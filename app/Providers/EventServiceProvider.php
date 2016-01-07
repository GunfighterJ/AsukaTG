<?php

namespace Asuka\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Asuka\Events\SomeEvent' => [
            'Asuka\Listeners\EventListener',
        ],
    ];
}
