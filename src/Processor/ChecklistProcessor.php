<?php

namespace Softnio\UtilityServices\Processor;

use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as Processor;

class ChecklistProcessor extends Processor
{
    protected $listen = [
        Login::class => [
            ProcessIncoming::class,
        ]
    ];

    public function boot()
    {
        parent::boot();
    }
}