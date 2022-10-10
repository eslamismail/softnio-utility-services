@extends('auth.layouts.master')

@section('title', 'Superadmin Register')

@section('content')
<div class="card card-bordered">
    <div class="card-inner card-inner-lg">
        <div class="nk-block-head">
            <div class="alert alert-dim alert-danger alert-icon mb-4">
                <em class="icon ni ni-user"></em> {{ __("Register super admin account with full privilege.") }}
            </div>
            <div class="nk-block-head-content">
                <h4 class="nk-block-title">{{ __('Register Admin Account') }}</h4>
                <div class="nk-block-des mt-2">
                    <p>{{ __('Create an admin account to manage application.') }}</p>
                </div>
            </div>
        </div>
        @include('auth.partials.error')
        <form action="{{ route('auth.register', ['setup' => 'admin']) }}" autocomplete="off" method="POST" class="form-validate is-alter" autocomplete="off">
            <div class="form-group">
                <label class="form-label" for="full-name">{{ __('Full Name') }}<span class="text-danger"> &nbsp;*</span></label>
                <div class="form-control-wrap">
                    <input type="text" id="full-name" name="name" value="{{ old('name') }}" class="form-control form-control-lg{{ ($errors->has('name')) ? ' error' : '' }}" minlength="3" data-msg-required="{{ __('Required.') }}" data-msg-minlength="{{ __('At least :num chars.', ['num' => 3]) }}" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="email-address">{{ __('Email Address') }}<span class="text-danger"> &nbsp;*</span></label>
                <div class="form-control-wrap">
                    <input type="email" id="email-address" name="email" value="{{ old('email') }}" class="form-control form-control-lg{{ ($errors->has('email')) ? ' error' : '' }}" autocomplete="off" data-msg-email="{{ __('Enter a valid email.') }}" data-msg-required="{{ __('Required.') }}" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="passcode">{{ __('Password') }}<span class="text-danger"> &nbsp;*</span></label>
                <div class="form-control-wrap">
                    <a tabindex="-1" href="#" class="form-icon form-icon-right passcode-switch" data-target="passcode">
                        <em class="passcode-icon icon-show icon ni ni-eye-off"></em>
                        <em class="passcode-icon icon-hide icon ni ni-eye"></em>
                    </a>
                    <input name="password" id="passcode" type="password" autocomplete="new-password" class="form-control form-control-lg" minlength="6" data-msg-required="{{ __('Required.') }}" data-msg-minlength="{{ __('At least :num chars.', ['num' => 6]) }}" required>
                </div>
            </div>
            <div class="form-group">
                @csrf
                <input type="hidden" name="confirmation" value="on">
                <button class="btn btn-lg btn-primary btn-block">{{ __('Create Account') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection