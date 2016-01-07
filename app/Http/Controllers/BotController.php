<?php

namespace App\Http\Controllers;

class BotController extends Controller
{
    function index($bot)
    {
        if ($bot != config(sprintf('telegram.bots.%s.token', config('telegram.default')))) {
            return response('No such bot.', 404);
        }

        $telegram = app('telegram')->bot();
        $updates = $telegram->commandsHandler(app()->environment() == 'production');
        return $updates;
    }

    function updateWebhook($action, $bot)
    {
        if ($bot != config(sprintf('telegram.bots.%s.token', config('telegram.default')))) {
            return response($bot, 404);
        }

        if (app()->environment() != 'production') {
            return 'You must set APP_ENV to production before you can use webhooks.';
        }

        $telegram = app('telegram')->bot();

        if ($action == 'set') {
            return $telegram->setWebhook(['url' => url($bot)]);
        } elseif ($action == 'remove') {
            return $telegram->removeWebhook();
        }
    }
}