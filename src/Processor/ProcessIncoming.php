<?php

namespace Softnio\UtilityServices\Processor;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ProcessIncoming
{
    public function __construct()
    {

    }

    public function handle($in)
    {
        if (is_admin()) {
            $app = hss('app'.'_'.'acquire'); $site = hss('site'.'_'.'merchandise');
            if (!(!empty($app) && is_array($app)) || !(!empty($site) && is_array($site))) {
                if (Cache::has(get_dkey('host', true))) { Cache::forget(get_dkey('host', true)); }
                if (Cache::has(get_dkey('path', true))) { Cache::forget(get_dkey('path', true)); }
            }
        } 
        if (!get_app_service()) {
            if (data_get(Auth::user(), 'role') == "user") {
                Auth::logout();
                session()->put('unknown'.'_'.'error', __('Sorry, due to technical issues we unable to proceed. Please try again after sometimes or contact us.'));
            }
            if (in_array(data_get(Auth::user(), 'role'), ['admin', 'super-admin', 'superadmin'])) {
                session()->put('system'.'_'.'error', true);
            }
        }
    }
}