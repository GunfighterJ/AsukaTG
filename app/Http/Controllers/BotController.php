<?php
/*
 * This file is part of AsukaTG.
 *
 * AsukaTG is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * AsukaTG is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AsukaTG.  If not, see <http://www.gnu.org/licenses/>.
 */

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