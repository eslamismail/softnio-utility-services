<?php

namespace Softnio\UtilityServices\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Services\HealthCheckService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Softnio\UtilityServices\UtilityService;

class UtilityServiceController extends Controller
{
    private $service;
    private $checkService;

    public function __construct(UtilityService $uservice, HealthCheckService $checkService)
    {
        $this->service = $uservice;
        $this->checkService = $checkService;
    }

    public function allowService()
    {
        if (!$this->checkService->isOk()) {
            return view('errors.health');
        }

        if (!hss('system'.'_'.'service')) {
            return view('Utility::product');
        }

        if ($this->service->validateService())  {
            return abort(404);
        }
        
        return view('Utility::product');
    }

    public function allowSetup()
    {
        $user = User::first();
        if (!empty($user)) {
            if(Auth::check()) Auth::logout();
            return redirect()->route('auth'.'.'.'login'.'.'.'form');
        }
        if (empty($user)) {
            session()->put('default'.'_'.'setup', 'finish');
            return (hss('system'.'_'.'service')) ? view('Utility::setup') : redirect()->route('app'.'.'.'service');
        }

        return abort(404);
    }

    public function validService(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'purchase_code' => 'required|min:32|max:40',
        ], [
            'name.required' => 'Envato Username is required for activation.',
            'email.required' => 'Your Email address is required for activation.',
            'email.email' => 'Please enter a valid email address.',
            'purchase_code.required' => 'Purchase Code is required for activation.',
            'purchase_code.*' => 'Please enter a valid purchase code.',
        ]);

        $fall = $request->hasCookie('appreg_fall');
        if($fall) {
            throw ValidationException::withMessages(['appreg' => config('session.waiting')]);
        }

        $gbl = [
            'domain' => get_path(),
            'product_number' => config('app.pid'),
            'product_key' => config('app.secret'),
            'appname' => config('app.name'),
            'appurl' => config('app.url'),
            'appver' => $this->service->getVer('fx'),
        ];

        $input = array_merge($request->except('_token'), $gbl);
        return $this->service->updateService($input);
    }
}