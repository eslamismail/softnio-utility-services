<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Softnio\UtilityServices\UtilityService;

if (!function_exists('site_info')) {
    function site_info($output = 'name')
    {
        $output = (!empty($output)) ? $output : 'name';
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
            'copyright' => (is_admin() && starts_with(sys_info('ptype'), 'l1')) ? str_replace('All Rights Reserved.', ' ', $copyright) . 'Application by <a href="' . the_link('soft' . 'nio' . '.' . 'com') . '" target="_blank">So' . 'ftn' . 'io</a>' : $copyright,
        ];

        return ($output == 'all') ? $infos : Arr::get($infos, $output, '');
    }
}

if (!function_exists('site_branding')) {
    function site_branding($part = null, $prams = [])
    {
        $default = array('class' => '');
        $markup = (in_array($part, ['sidebar', 'header', 'mailer'])) ? $part : 'header';
        $attr = parse_args($prams, $default);

        $lsize = (isset($attr['size']) && $attr['size']) ? $attr['size'] : '';
        $excls = (isset($attr['class']) && $attr['class']) ? ' ' . $attr['class'] : '';
        $lkcls = (isset($attr['class_link']) && $attr['class_link']) ? ' ' . $attr['class_link'] : '';

        if ($markup == 'sidebar') {
            $linkto = (is_admin()) ? route('admin.dashboard') : route('dashboard');
            $output = '<div class="nk-sidebar-brand' . $excls . '"><a class="logo-link' . $lkcls . '" href="' . $linkto . '">' . auto_l('light', $lsize) . auto_l('dark', $lsize) . '</a></div>';
            return html_string($output);

        } elseif ($markup == 'mailer') {
            return html_string(logo_mixup('mail'));
        } elseif ($markup == 'header') {
            if (isset($attr['panel']) && $attr['panel'] == 'auth') {
                $output = '<div class="brand-logo text-center mb-2' . $excls . '"><a class="logo-link' . $lkcls . '" href="' . url('/') . '">' . logo_mixup('light', $lsize) . logo_mixup('dark', $lsize) . '</a></div>';
                return html_string($output);

            } elseif (isset($attr['panel']) && $attr['panel'] == 'public') {
                $output = '<div class="header-logo' . $excls . '"><a class="logo-link' . $lkcls . '" href="' . url('/') . '">' . logo_mixup('dark', $lsize) . logo_mixup('light', $lsize) . '</a></div>';
                return html_string($output);
            }

            $linkto = (is_admin()) ? route('admin.dashboard') : route('dashboard');
            $output = '<div class="nk-header-brand' . $excls . '"><a class="logo-link' . $lkcls . '" href="' . $linkto . '">' . auto_l('light', $lsize) . auto_l('dark', $lsize) . '</a></div>';

            return html_string($output);
        } else {

            $output = '<div class="nk-header-brand"><span class="logo-link' . $lkcls . '"> ' . auto_l('light') . auto_l('dark') . '</span></div>';
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
            return '<img class="logo-img" style="max-height: 50px; width: auto;" src="' . $logo . '" alt="' . site_info('name') . '">';
        } else {
            return '<img class="logo-img logo-' . $type . (($cls) ? ' logo-img-' . $cls : '') . '" src="' . $logo . '"' . (($logo2x) ? ' srcset="' . $logo2x . ' 2x"' : '') . ' alt="' . site_info('name') . '">';
        }
    }
}

if (!function_exists('site_logo')) {
    function site_logo($type = null, $vers = null)
    {
        $type = ($type == 'dark' || $type == 'mail') ? $type : 'light';
        $vers = ($vers == '2x' && $type != 'mail') ? '2x' : '';

        $key = 'website_logo';
        $logo = $type . $vers;

        $default = [
            'light' => asset('/images/logo.png'),
            'light2x' => asset('/images/logo2x.png'),
            'dark' => asset('/images/logo-dark.png'),
            'dark2x' => asset('/images/logo-dark2x.png'),
            'mail' => asset('/images/logo-mail.png'),
        ];

        return Cache::remember($key . '_' . $logo, 3600 * 24 * 30, function () use ($default, $key, $logo) {
            $path = gss($key . '_' . $logo);

            if (Str::contains($logo, '2x') && empty($path)) {
                $path = gss($key . '_' . str_replace('2x', '', $logo));
            }

            $brand = '';
            if (!empty($path) && Storage::exists($path)) {
                $brand = Storage::get($path);
            }

            return ($brand) ? 'data:image/jpeg;base64,' . base64_encode($brand) : $default[$logo];
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
            $apps = gss('app' . '_' . 'acquire', false);
            $site = gss('site' . '_' . 'merchandise', false);
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

if (!function_exists('starts_with')) {
    function starts_with($find, $string)
    {
        return Str::startsWith($find, $string);
    }
}

if (!function_exists('the_link')) {
    function the_link($url, $ssl = true)
    {
        $scheme = ($ssl == true) ? 'https://' : 'http://';
        return (starts_with('http', $url)) ? $url : $scheme . $url;
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

        $pee = preg_replace('|<br\s*/?>\s*<br\s* /?>|', "\n\n", $pee);

    $allblocks =
    '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';

    $pee = preg_replace('!(<' . $allblocks . '[\s/>])!' , "\n\n$1" , $pee); $pee=preg_replace('!(</' . $allblocks
        . '>)!' , "$1\n\n" , $pee); $pee=str_replace(array("\r\n", "\r" ), "\n" , $pee); $pee=replace_in_html_tags($pee,
        array("\n"=> "
        <!-- nl --> ", ));
        if (strpos($pee, '<option') !==false) {$pee=preg_replace('|\s*<option|', '<option' , $pee);
            $pee=preg_replace('|</option>\s*|', '</option>', $pee);
            }

            $pee = preg_replace("/\n\n+/", "\n\n", $pee);
            $pees = preg_split('/\n\s*\n/', $pee, -1, PREG_SPLIT_NO_EMPTY);
            $pee = '';

            foreach ($pees as $tinkle) {
            $pee .= '<p>' . trim($tinkle, "\n") . "</p>\n";
            }

            $pee = preg_replace('|<p>\s*</p>|', '', $pee);
            $pee = preg_replace('!<p>([^<]+)< /(div|address|form)>!', "<p>$1</p>
                    </$2>", $pee);
                    $pee = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)\s*</p>!' , "$1" , $pee);
                            $pee=preg_replace("|<p>(<li.+?)< /p>|", "$1", $pee);
                            $pee = preg_replace('|<p>
                                <blockquote([^>]*)>|i', "<blockquote$1>
                                        <p>", $pee);
                                            $pee = str_replace('</blockquote>
                                        </p>', '
                            </p>
                            </blockquote>', $pee);
                            $pee = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)!' , "$1" , $pee);
                                    $pee=preg_replace('!(</?' . $allblocks . '[^>]*>)\s*</p>!' , "$1" , $pee);if ($br)
                                    {$pee=str_replace(array('<br>', '<br />'), '<br />', $pee);
                                $pee = preg_replace('|(?
                                <!<br />)\s*\n|', "<br />\n", $pee);
                                }

                                $pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*<br />!' , "$1" , $pee);
                                    $pee=preg_replace('!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)!',
                                '$1', $pee);
                                $pee = preg_replace("|\n
                            </p>$|", '</p>', $pee);
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
                    return logo_mixup($type, $size);

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