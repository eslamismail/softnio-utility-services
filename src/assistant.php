<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Softnio\UtilityServices\UtilityService;

if (!function_exists('site_info')) {
    function site_info($output = 'name')
    {
        $output  = (!empty($output)) ? $output : 'name';
        $appname = config('app.name');
        $coright = gss('site_copyright', __(":Sitename &copy; :year. All Rights Reserved."));
        $copyright = __($coright, ['year' => date('Y'), 'sitename' => gss('site_name', $appname)]);

        $infos = [
            'apps' => $appname,
            'author' => gss('site_author', $appname),
            'name' => gss('site_name', $appname),
            'email' => gss('site_email'),
            'url' => url('/'),
            'url_only' => str_replace(['https://', 'http://'], '', url('/')),
            'url_app' => config('app.url'),
            'copyright' => (is_admin() && starts_with(sys_info('ptype'), 'l1')) ? str_replace('All Rights Reserved.', ' ', $copyright). 'Application by <a href="'. the_link('soft' .'nio'. '.'.'com') .'" target="_blank">So'. 'ftn' .'io</a>' : $copyright
        ];

        return ($output=='all') ? $infos : Arr::get($infos, $output, '');
    }
}


if (!function_exists('site_branding')) {
    function site_branding($part = null, $prams = [])
    {
        $default = array('class' => '');
        $markup = (in_array($part, ['sidebar', 'header', 'mailer'])) ? $part : 'header';
        $attr = parse_args($prams, $default);

        $lsize = (isset($attr['size']) && $attr['size']) ? $attr['size'] : '';
        $excls = (isset($attr['class']) && $attr['class']) ? ' '.$attr['class'] : '';
        $lkcls = (isset($attr['class_link']) && $attr['class_link']) ? ' '.$attr['class_link'] : '';

        if ($markup == 'sidebar') {
            $linkto =  (is_admin()) ? route('admin.dashboard') : route('dashboard');
            $output = '<div class="nk-sidebar-brand'.$excls.'"><a class="logo-link'.$lkcls.'" href="'.$linkto.'">'.auto_l('light', $lsize).auto_l('dark', $lsize).'</a></div>';

            return html_string($output);

        }
        elseif ($markup == 'mailer') {
            return html_string(logo_mixup('mail'));
        }
        elseif ($markup == 'header') {
            if (isset($attr['panel']) && $attr['panel'] == 'auth') {
                $output = '<div class="brand-logo text-center mb-2'.$excls.'"><a class="logo-link'.$lkcls.'" href="'.url('/').'">'.logo_mixup('light', $lsize).logo_mixup('dark', $lsize).'</a></div>';
                return html_string($output);

            } elseif (isset($attr['panel']) && $attr['panel'] == 'public') {
                $output = '<div class="header-logo'.$excls.'"><a class="logo-link'.$lkcls.'" href="'.url('/').'">'.logo_mixup('dark', $lsize).logo_mixup('light', $lsize).'</a></div>';
                return html_string($output);
            }

            $linkto =  (is_admin()) ? route('admin.dashboard') : route('dashboard');
            $output = '<div class="nk-header-brand'.$excls.'"><a class="logo-link'.$lkcls.'" href="'.$linkto.'">'.auto_l('light', $lsize).auto_l('dark', $lsize).'</a></div>';

            return html_string($output);
        } 
        else {

            $output = '<div class="nk-header-brand"><span class="logo-link'.$lkcls.'"> ' . auto_l('light') . auto_l('dark'). '</span></div>';
            return html_string($output);
        }
    }
}


if (!function_exists('logo_mixup')) {
    function logo_mixup($type = null, $cls = '')
    {
        $type = ($type == 'dark' || $type == 'mail') ? $type : 'light';

        $logo = site_logo($type);
        $logo2x = site_logo($type, '2x');

        if ($type == 'mail') {
            return '<img class="logo-img" style="max-height: 50px; width: auto;" src="'.$logo.'" alt="'.site_info('name').'">';
        } else {
            return '<img class="logo-img logo-' . $type . (($cls) ? ' logo-img-'.$cls : ''). '" src="'.$logo.'"' . (($logo2x) ? ' srcset="'.$logo2x.' 2x"' : '').' alt="'.site_info('name').'">';
        }
    }
}



if (!function_exists('site_logo')) {
    function site_logo($type = null, $vers = null)
    {
        $type = ($type == 'dark' || $type == 'mail') ? $type : 'light';
        $vers = ($vers == '2x' && $type != 'mail') ? '2x' : '';

        $key = 'website_logo';
        $logo = $type.$vers;

        $default = [ 
            'light' => asset('/images/logo.png'), 
            'light2x' => asset('/images/logo2x.png'), 
            'dark' => asset('/images/logo-dark.png'), 
            'dark2x' => asset('/images/logo-dark2x.png'), 
            'mail' => asset('/images/logo-mail.png') 
        ];

        return Cache::remember($key.'_'.$logo, 3600*24*30, function () use ($default, $key, $logo) {
            $path = gss($key.'_'.$logo);

            if (Str::contains($logo, '2x') && empty($path)) {
                $path = gss($key.'_'.str_replace('2x', '', $logo));
            }

            $brand = '';
            if (!empty($path) && Storage::exists($path)) {
                $brand = Storage::get($path);
            }

            return ($brand) ? 'data:image/jpeg;base64,'.base64_encode($brand) : $default[$logo];
        });
    }
}

if (!function_exists("uservice")) {
    function uservice()
    {
        return app(UtilityService::class);
    }
}

if (!function_exists("get_app_service")) {
    function get_app_service($force = false)
    {
        return uservice()->validateService($force);
    }
}


if (!function_exists('replace_in_html_tags')) {
    function replace_in_html_tags($hstack, $replace_pairs)
    {
        $textarr = preg_split(get_html_split_regex(), $hstack, -1, PREG_SPLIT_DELIM_CAPTURE);
        $changed = false;

        if (1 === count($replace_pairs)) {
            foreach ($replace_pairs as $needle => $replace);

            for ($i = 1, $c = count($textarr); $i < $c; $i += 2) {
                if (false !== strpos($textarr[$i], $needle)) {
                    $textarr[$i] = str_replace($needle, $replace, $textarr[$i]);
                    $changed = true;
                }
            }
        } else {
            $needles = array_keys($replace_pairs);

            for ($i = 1, $c = count($textarr); $i < $c; $i += 2) {
                foreach ($needles as $needle) {
                    if (false !== strpos($textarr[$i], $needle)) {
                        $textarr[$i] = strtr($textarr[$i], $replace_pairs);
                        $changed = true;
                        break;
                    }
                }
            }
        }

        if ($changed) {
            $hstack = implode($textarr);
        }

        return $hstack;
    }
}

if (!function_exists('strip_tags_map')) {
    function strip_tags_map($str)
    {
        return ($str && !is_array($str)) ? strip_tags($str) : $str; 
    }
}

if (!function_exists('get_html_split_regex')) {
    function get_html_split_regex()
    {
        static $regex;
        if (!isset($regex)) {
            $coms = '!' . '(?:' . '-(?!->)' . '[^\-]*+' . ')*+' . '(?:-->)?';
            $cdata = '!\[CDATA\[' . '[^\]]*+' . '(?:' . '](?!]>)' . '[^\]]*+' . ')*+' . '(?:]]>)?';
            $escaped = '(?=' . '!--' . '|' . '!\[CDATA\[' . ')' . '(?(?=!-)' . $coms . '|' . $cdata . ')';
            $regex = '/(' . '<' . '(?' . $escaped . '|' . '[^>]*>?' . ')' . ')/';
        }
        return $regex;
    }
}

if (!function_exists("get_sys_cipher")) {
    function get_sys_cipher()
    {
        $chp = Cache::get(get_m5host());
        if (empty($chp)) {
            $apps = gss('app'.'_'.'acquire', false);
            $site = gss('site'.'_'.'merchandise', false);
            if ($apps && $site) {
                if (is_array($site) && is_array($apps)) {
                    Cache::put(get_m5host(), array_merge($apps, $site), Carbon::now()->addMinutes(30));
                    $chp = Cache::get(get_m5host());
                }
            }
        }
        return (!empty($chp)) ? $chp : false;
    }
}

if (! function_exists('starts_with')) {
    function starts_with($find, $string)
    {
        return Str::startsWith($find, $string);
    }
}

if (! function_exists('the_link')) {
    function the_link($url, $ssl = true)
    {
        $scheme = ($ssl==true) ? 'https://' : 'http://';
        return (starts_with('http', $url)) ? $url : $scheme.$url;
    }
}

if (!function_exists('auto_p')) {
    function auto_p($pee, $br = true, $add = '')
    {
        $pre_tags = array();

        if (trim($pee) === '') {
            return '';
        }

        $pee = $pee . "\n";
        if (strpos($pee, '<pre') !== false) {
            $pee_parts = explode('</pre>', $pee);
            $last_pee = array_pop($pee_parts);
            $pee = '';
            $i = 0;

            foreach ($pee_parts as $pee_part) {
                $start = strpos($pee_part, '<pre');
                if ($start === false) {
                    $pee .= $pee_part;
                    continue;
                }

                $name = "<pre pre-tag-$i></pre>";
                $pre_tags[$name] = substr($pee_part, $start) . '</pre>';

                $pee .= substr($pee_part, 0, $start) . $name;
                $i++;
            }

            $pee .= $last_pee;
        }

        $pee = preg_replace('|<br\s*/?>\s*<br\s*/?>|', "\n\n", $pee);

        $allblocks = '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';

        $pee = preg_replace('!(<' . $allblocks . '[\s/>])!', "\n\n$1", $pee);
        $pee = preg_replace('!(</' . $allblocks . '>)!', "$1\n\n", $pee);
        $pee = str_replace(array("\r\n", "\r"), "\n", $pee);
        $pee = replace_in_html_tags($pee, array("\n" => " <!-- nl --> "));
        if (strpos($pee, '<option') !== false) {
            $pee = preg_replace('|\s*<option|', '<option', $pee);
            $pee = preg_replace('|</option>\s*|', '</option>', $pee);
        }

        $pee = preg_replace("/\n\n+/", "\n\n", $pee);
        $pees = preg_split('/\n\s*\n/', $pee, -1, PREG_SPLIT_NO_EMPTY);
        $pee = '';

        foreach ($pees as $tinkle) {
            $pee .= '<p>' . trim($tinkle, "\n") . "</p>\n";
        }

        $pee = preg_replace('|<p>\s*</p>|', '', $pee);
        $pee = preg_replace('!<p>([^<]+)</(div|address|form)>!', "<p>$1</p></$2>", $pee);
        $pee = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee);
        $pee = preg_replace("|<p>(<li.+?)</p>|", "$1", $pee);
        $pee = preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $pee);
        $pee = str_replace('</blockquote></p>', '</p></blockquote>', $pee);
        $pee = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)!', "$1", $pee);
        $pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee);

        if ($br) {
            $pee = str_replace(array('<br>', '<br/>'), '<br />', $pee);
            $pee = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $pee);
        }

        $pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*<br />!', "$1", $pee);
        $pee = preg_replace('!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)!', '$1', $pee);
        $pee = preg_replace("|\n</p>$|", '</p>', $pee);
        if (!empty($pre_tags)) {
            $pee = str_replace(array_keys($pre_tags), array_values($pre_tags), $pee);
        }

        return $add . $pee;
    }
}


if (!function_exists('auto_l')) {
    function auto_l($type = null, $size = null)
    {
        if (!is_admin() || starts_with(sys_info('ptype'), 'l2')) {
            return logo_mixup($type, $size);
        }

        $type = ($type == 'light') ? $type : 'dark';
        $size = ($size) ? (int) $size : 22;

        $light = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 280 43" class="logo-'.$type.' logo-img" height="'. (($size) ? $size : 22) .'"><path d="M10.255,1.168V42.629H0V1.168Z" transform="translate(0 -0.399)" fill="#fff"/><polygon points="38.861 0.79 38.861 26.476 23.508 0.77 13.073 0.77 13.073 42.231 23.148 42.231 23.148 16.821 38.741 42.231 48.936 42.231 48.936 28.13 38.861 0.79" fill="#fff"/><path d="M197.481,37.268Q191.772,43.4,182.649,43.4t-14.831-6.131q-5.709-6.129-5.709-15.429a21.6,21.6,0,0,1,5.709-15.31Q173.527.4,182.649.4t14.832,6.13a21.6,21.6,0,0,1,5.709,15.31Q203.19,31.139,197.481,37.268Zm-7.22-23.987a10.208,10.208,0,0,0-15.223,0,12.56,12.56,0,0,0-2.967,8.558,12.729,12.729,0,0,0,2.967,8.677,10.208,10.208,0,0,0,15.223,0,12.734,12.734,0,0,0,2.967-8.677A12.565,12.565,0,0,0,190.261,13.281Z" transform="translate(0 -0.399)" fill="#00b7ff"/><path d="M266.687,1.168l-10.5,26.239L245.757,1.168H232.443V39.8L224.328,24.86a10.322,10.322,0,0,0,4.317-4.471,14.044,14.044,0,0,0,1.5-6.486q0-12.735-14.692-12.735H201.778v.024c.143.146.289.287.43.438q7.529,8.084,7.528,20.19,0,12.264-7.528,20.347c-.141.151-.287.292-.43.439v.023h10.256V27.289h3.117l7.5,15.34h19.631V15.443l9.235,23.81h9.415l9.236-23.81V42.629H280V1.168ZM218.42,17.605a4.531,4.531,0,0,1-3.328,1.214h-3.058V9.875h2.578q5.037,0,5.037,4.383A4.492,4.492,0,0,1,218.42,17.605Z" transform="translate(0 -0.399)" fill="#fff"/><path d="M134.592,1.168V8.311a13.423,13.423,0,0,0-.649-1.19,21.251,21.251,0,0,0-2.4-3.05,10.869,10.869,0,0,0-4.078-2.665A15.483,15.483,0,0,0,121.739.4Q115.5.4,111.724,3.775a10.907,10.907,0,0,0-3.5,6.112V1.168H75.962l-9.475,29.5-9.475-29.5H45.737L60.91,42.629H72.064L85.857,4.938V42.629h22.849V37.22a22.841,22.841,0,0,0,2,2.359,11.986,11.986,0,0,0,4.318,2.754,16.191,16.191,0,0,0,6,1.066q6.777,0,10.735-3.642a12.32,12.32,0,0,0,3.958-9.507,10.476,10.476,0,0,0-1.8-6.1,12.825,12.825,0,0,0-4.378-4.027,56.7,56.7,0,0,0-5.157-2.517A26.046,26.046,0,0,1,120,15.3a3.39,3.39,0,0,1-1.8-2.754,2.548,2.548,0,0,1,.9-1.985,3.478,3.478,0,0,1,2.4-.8,4.74,4.74,0,0,1,2.609.71,5.6,5.6,0,0,1,1.619,1.452,12.854,12.854,0,0,1,1.05,1.924l7.991-3.612h8.88v32.4H153.9v-32.4h3.738a27.192,27.192,0,0,1,5.45-8.6c.148-.159.3-.307.452-.462ZM120.84,26.786a12.1,12.1,0,0,1,3.208,1.895,3.1,3.1,0,0,1,1.289,2.34q0,2.9-3.718,2.9A5.839,5.839,0,0,1,117.9,32.59a8.887,8.887,0,0,1-2.579-3.287l-8.576,4.205c.007.019.018.039.025.059H96.112v-7.4h10.315v-9H96.112v-6.93H108.16a14.41,14.41,0,0,0-.214,2.487,11.685,11.685,0,0,0,1.289,5.568,11.256,11.256,0,0,0,3.209,3.85,33.332,33.332,0,0,0,4.2,2.636Q118.92,25.986,120.84,26.786Z" transform="translate(0 -0.399)" fill="#fff"/></svg>';

        $dark = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 280 43" class="logo-'.$type.' logo-img" height="'. (($size) ? $size : 22) .'"><path d="M10.255,1.168V42.629H0V1.168Z" transform="translate(0 -0.399)" fill="#2a3a63"/><polygon points="38.861 0.79 38.861 26.476 23.508 0.77 13.073 0.77 13.073 42.231 23.148 42.231 23.148 16.821 38.741 42.231 48.936 42.231 48.936 28.13 38.861 0.79" fill="#2a3a63"/><path d="M197.481,37.268Q191.772,43.4,182.649,43.4t-14.831-6.131q-5.709-6.129-5.709-15.429a21.6,21.6,0,0,1,5.709-15.31Q173.527.4,182.649.4t14.832,6.13a21.6,21.6,0,0,1,5.709,15.31Q203.19,31.139,197.481,37.268Zm-7.22-23.987a10.208,10.208,0,0,0-15.223,0,12.56,12.56,0,0,0-2.967,8.558,12.729,12.729,0,0,0,2.967,8.677,10.208,10.208,0,0,0,15.223,0,12.734,12.734,0,0,0,2.967-8.677A12.565,12.565,0,0,0,190.261,13.281Z" transform="translate(0 -0.399)" fill="#00b7ff"/><path d="M266.687,1.168l-10.5,26.239L245.757,1.168H232.443V39.8L224.328,24.86a10.322,10.322,0,0,0,4.317-4.471,14.044,14.044,0,0,0,1.5-6.486q0-12.735-14.692-12.735H201.778v.024c.143.146.289.287.43.438q7.529,8.084,7.528,20.19,0,12.264-7.528,20.347c-.141.151-.287.292-.43.439v.023h10.256V27.289h3.117l7.5,15.34h19.631V15.443l9.235,23.81h9.415l9.236-23.81V42.629H280V1.168ZM218.42,17.605a4.531,4.531,0,0,1-3.328,1.214h-3.058V9.875h2.578q5.037,0,5.037,4.383A4.492,4.492,0,0,1,218.42,17.605Z" transform="translate(0 -0.399)" fill="#2a3a63"/><path d="M134.592,1.168V8.311a13.423,13.423,0,0,0-.649-1.19,21.251,21.251,0,0,0-2.4-3.05,10.869,10.869,0,0,0-4.078-2.665A15.483,15.483,0,0,0,121.739.4Q115.5.4,111.724,3.775a10.907,10.907,0,0,0-3.5,6.112V1.168H75.962l-9.475,29.5-9.475-29.5H45.737L60.91,42.629H72.064L85.857,4.938V42.629h22.849V37.22a22.841,22.841,0,0,0,2,2.359,11.986,11.986,0,0,0,4.318,2.754,16.191,16.191,0,0,0,6,1.066q6.777,0,10.735-3.642a12.32,12.32,0,0,0,3.958-9.507,10.476,10.476,0,0,0-1.8-6.1,12.825,12.825,0,0,0-4.378-4.027,56.7,56.7,0,0,0-5.157-2.517A26.046,26.046,0,0,1,120,15.3a3.39,3.39,0,0,1-1.8-2.754,2.548,2.548,0,0,1,.9-1.985,3.478,3.478,0,0,1,2.4-.8,4.74,4.74,0,0,1,2.609.71,5.6,5.6,0,0,1,1.619,1.452,12.854,12.854,0,0,1,1.05,1.924l7.991-3.612h8.88v32.4H153.9v-32.4h3.738a27.192,27.192,0,0,1,5.45-8.6c.148-.159.3-.307.452-.462ZM120.84,26.786a12.1,12.1,0,0,1,3.208,1.895,3.1,3.1,0,0,1,1.289,2.34q0,2.9-3.718,2.9A5.839,5.839,0,0,1,117.9,32.59a8.887,8.887,0,0,1-2.579-3.287l-8.576,4.205c.007.019.018.039.025.059H96.112v-7.4h10.315v-9H96.112v-6.93H108.16a14.41,14.41,0,0,0-.214,2.487,11.685,11.685,0,0,0,1.289,5.568,11.256,11.256,0,0,0,3.209,3.85,33.332,33.332,0,0,0,4.2,2.636Q118.92,25.986,120.84,26.786Z" transform="translate(0 -0.399)" fill="#2a3a63"/></svg>';

        return ($type=='light') ? $light : $dark;
    }
}

if (!function_exists('sys_info')) {
    function sys_info($output = null)
    {
        return uservice()->systemInfo($output);
    }
}


if (!function_exists("get_m5host")) {
    function get_m5host()
    {
        return md5(get_host());
    }
}

if (!function_exists('is_secure')) {
    function is_secure($opt = false)
    {
        if ($opt == true) {
            return uservice()->validateService();
        }
        return request()->isSecure();
    }
}

if (!function_exists('has_sysinfo')) {
    function has_sysinfo()
    {
        return config('app.info', true);
    }
}