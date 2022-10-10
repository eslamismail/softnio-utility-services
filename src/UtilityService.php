<?php

namespace Softnio\UtilityServices;

use App\Models\Setting;
use App\Services\SettingsService;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\ValidationException;

class UtilityService
{
    protected $check;
    protected $ihost;
    protected $phash;

    public function __construct()
    {
        $this->ihost = request()->getHost();
        $this->phash = hash('joaat', get_path());
        $this->check = '/che'.'ck/en'.'va'.'to';
    }

    public function systemInfo($type = null) {
        $type = (!empty($type)) ? $type : 'name';
        
        if (in_array($type, ['code', 'lkey', 'secret', 'service'])) {
            return ($type=='service') ? gss('system'. '_' .'service') : Arr::get(gss('app' .'_'. 'acquire'), 'secret', false);
        }
        if (in_array($type, ['cipher', 'valid'])) {
            return Arr::get(gss('app'. '_' .'acquire'), 'cipher', false);
        }
        if (in_array($type, ['active', 'checked', 'state', 'sys'])) {
            return ($type=='checked') ? gss('payout' .'_'. 'check', false) : $this->validateService();
        }

        $merc = gss('site' .'_'. 'merchandise', []);
        $info = [
            'name' => config('app'.'.'.'system'),
            'vers' => $this->getVer('fx'),
            'version' => config('app' .'.'. 'version', '1.0'),
            'build' => (int) config('app'. '.' .'build'),
            'update' => (int) (config('app' .'.'. 'build') . str_replace('.', '', config('app' .'.'. 'version', '1.0'))),
            'install' => gss('installed'. '_' .'apps', false),
            'key' => config('app' .'.' .'secret', '7d5b16c6'),
            'item' => config('app'. '.'. 'pid', '23608043'),
            'author' => 'So'.'ft'.'ni'.'o',
            'system' => 'In'.'ve'.'sto'.'rm',
            'etype' => 'l'.substr(gss('system' .'_'. 'service'), 10, 1).'S'.substr(config('app'. '.' .'secret', '7d5b16c6'), 0, 3),
            'euser' => Arr::get($merc, 'name', false),
            'email' => Arr::get($merc, 'email', false),
            'ecode' => Arr::get($merc, 'purchase'. '_' .'code', false),
            'url' => config('app.url'),
        ];

        if ($type =='pcode' || $type =='purchase'  .'_'.  'code') $type = 'ecode';
        if ($type =='type' || $type =='pin' || $type == 'ptype') $type = 'etype';

        return ($type=='all') ? $info : Arr::get($info, $type, false);
    }

    public function getVer($type = null) {
        $evar = str_replace('.', '', env('APP_VERSION'));
        $mvar = str_replace('.', '', config('app'.'.'.'version'));

        if ($type=='fx'||$type=='all') {
            return $mvar . '.'.$evar;
        }

        return ($type=='ev') ? $evar : $mvar;
    }

    public function updateService($input)
    {
        $pque = (int) gss('app_queue', 0);
        $res = Http::post($this->httpApi('nw'), $this->postApi($input));

        if ($res->successful()) {
            if ($res->json('status') == "active") {
                try {
                    $input["secret"] = $res->json('code');
                    $input["cipher"] = $res->json('valid');
                    $input["update"] = $res->json('timestamp');
                    $input["app"] = site_info('name');
                    $input["key"] = substr($res->json('code'), 3, 6);

                    $ud = Arr::only($input, ['name', 'email', 'purchase'. '_'. 'code']);
                    $rd = Arr::only($input, ['app', 'secret', 'cipher', 'key', 'update']);
                    $app = array_merge($rd, $ud);

                    upss('app'. '_' .'acquire', json_encode($rd));
                    upss('site' .'_'. 'merchandise', json_encode($ud));
                    upss('system' .'_'. 'service', get_rand(10).strtoupper($rd['key']));

                    upss('payout'. '_' . 'check', $rd['update']);
                    upss('payout'. '_' . 'batch', $rd['secret']);

                    Cache::put(md5($this->ihost), $app, Carbon::now()->addMinutes(30));
                    Cookie::queue(Cookie::forget('appreg' .'_'. 'fall')); session()->forget('system'. '_' .'error');
                    upss('app' .'_'. 'queue', 0);

                    if (auth()->check()) {
                        $route = 'admin'. '.' .'dashboard'; 
                    } else {
                        $route = 'app' .'.'. 'service'. '.' .'setup';
                    }
                    $redirect = (has_route($route)) ? route($route) : route('auth' .'.'. 'login'.'.' .'form');

                    return response()->json(['msg' => $res->json('message'), 'redirect' => $redirect]);
                } catch (\Exception $e) {
                    throw ValidationException::withMessages(['failed' => 'Something is wrong, please try after sometimes.']);
                }
            } else {
                upss('app'. '_' .'queue', $pque + 1); upss('payout'. '_' .'batch', get_rand(28, false)); upss('app' . '_' . 'acquire', null);
                if($pque >= 3) { Cookie::queue(Cookie::make('appreg' .'_'. 'fall', 1, (($pque > 10) ? 30 : 4))); }
                throw ValidationException::withMessages(['failed' => $res->json('message')]);
            }
        } elseif($res->serverError()) {
            upss('payout' .'_'. 'check', (time() + 3600)); 
            throw ValidationException::withMessages(['server' => 'Please ensure you are connected to internet!']);
        } elseif($res->clientError()) {
            upss('payout'. '_' .'check', (time() + 3600)); 
            throw ValidationException::withMessages(['invalid' => 'Sorry, we are unable process your request!']);
        }
    }

    private function verifyToken()
    {
        $cd = 'code';
        $cs = $this->ciphers('secret');
        $pc = $this->ciphers('purchase' .'_'. $cd);
        $pb = gss('payout'. '_' .'batch');

        if ($pc && ($cs == $pb) && $this->service(true)) {
            try {
                $get = [
                    "domain" => get_path(),
                    "purchase". "_".$cd => $pc,
                    "activation" ."_".$cd => $pb,
                    "appname" => config('app' .'.'. 'name'),
                    "appurl" => config('app'. '.' .'url'),
                    "app".$cd => substr($this->service(), 10),
                    "appver" => $this->getVer('fx'),
                ];

                $res = Http::get($this->httpApi('or', config('app' .'.'. 'secret')), $get);

                if ($res->successful()) {
                    if ($res->json('status') == "active") {
                        $sservice = new SettingsService();
                        $sservice->generateSetting($res->json());
                        session()->forget('system' . '_' . 'error');
                        return true;
                    } else {
                        Cache::put(md5($this->ihost), $this->ciphers(), Carbon::now()->addMinutes(30)); str_sub_count();
                        return true;
                    }
                } else {
                    Cache::put(md5($this->ihost), $this->ciphers(), Carbon::now()->addMinutes(30)); str_sub_count();
                    return true;
                }
            } catch (\Exception $e) {
                Cache::put(md5($this->ihost), $this->ciphers(), Carbon::now()->addMinutes(30)); str_sub_count();
                return true;
            }
        } else {
            upss('payout'. '_' .'check', (time() + 1800)); session()->put('system'. '_' .'error', true);
            if (gss('system' .'_'. 'service') && (strlen($this->service()) == 13 || strlen($this->service()) == 12)) $this->checkSystem();
            return false;
        }
    }

    public function validateService($force = false)
    {
        $cipher = get_etoken('cipher', true); $tc = get_etoken('update', true); $ts = gss('payout'. '_' .'check');
        $token  = (str_contains($cipher, $this->phash) && str_con(get_path()));
        
        if ($force) {
            return $this->verifyToken();
        } elseif (min($tc, $ts) <= time()){
            return $this->verifyToken();
        } elseif (!$token) {
            return $this->verifyToken();
        }

        return true;
    }

    private function checkSystem() 
    {
        $acq = gss('app'.'_'.'acquire', []);
        $dcq = [
            'app' => site_info('name'),
            'key' => get_rand(6, false),
            'cipher' => get_rand(48, false),
            'secret' => get_rand(28, false),
            'update' => (time() + 3600)
        ];

        if (!empty($acq) && is_array($acq) && count($acq) > 0) {
            $dcq = array_merge($acq, $dcq);
        }

        upss('payout'.'_'.'batch', $dcq['secret']); upss('app'.'_'.'acquire', $dcq);
        Cache::put(get_m5host(), array_merge(gss('site'.'_'.'merchandise', []), $dcq), Carbon::now()->addMinutes(30));
    }

    private function httpApi($what = null, $endslug = null) 
    {
        $setting = new SettingsService();
        $endwith = ($what=='nw') ? 'license' : config('app'.'.'.'secret');
        $endpoint = (!empty($endslug)) ? $endslug : $endwith;
        return $setting->api . $this->check .'/'. $endpoint;
    }

    private function postApi($input) 
    {
        return Arr::only($input, ['name', 'email', 'domain', 'purchase' . '_' . 'code', 'product' .'_'. 'number', 'product'. '_' .'key', 'appname', 'appurl', 'appver']);
    }

    private function secrets() 
    {
        $key = gss('payout'.'_'.'batch', random_hash('M3X'));
        return $this->ciphers('secret', $key) ? $this->ciphers('secret') : false;
    }

    private function service($len = false) 
    {
        $sys = gss('system' . '_' . 'service');
        return ($len) ? ((strlen($sys) < 12) ? false : true) : $sys;
    }

    private function ciphers($token = null, $compare = false) 
    {
        $cipher = get_sys_cipher();
        $token = ($token) ? $token : 'all';

        if (!empty($cipher)) {
            if (is_array($cipher) && $compare) {
                return in_array($compare, $cipher) ? true : false;
            }

            if ($token == 'all') {
                return ($cipher) ? $cipher : false;
            }

            return (isset($cipher[$token]) && $cipher[$token]) ? $cipher[$token] : false;
        }

        return false;
    }
}