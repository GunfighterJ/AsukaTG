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

use Illuminate\Http\Request;

class BotController extends Controller
{
    function index($bot)
    {
        $telegram = app('telegram');
        if ($bot != $telegram->getBotConfig(config('telegram.default'))['token']) {
            return '';
        }

        $bot = $telegram->bot();
        $updates = $bot->commandsHandler(app()->environment() == 'production');
        return $updates;
    }

    function updateWebhook(Request $request, $action, $bot)
    {
        $telegram = app('telegram');
        if ($bot != $telegram->getBotConfig(config('telegram.default'))['token']) {
            return '';
        }

        $ownerId = $telegram->getBotConfig(config('telegram.default')['owner_id']);
        if ($ownerId) {
            sendMessage(sprintf('The IP %s just accessed %s', $request->getClientIp(), $request->getUri()), $ownerId);
        }

        if (app()->environment() != 'production') {
            return 'You must set APP_ENV to production before you can use webhooks.';
        }

        $bot = $telegram->bot();

        if ($action == 'set') {
            return $bot->setWebhook(['url' => url($bot)]);
        } elseif ($action == 'remove') {
            return $bot->removeWebhook();
        }
        return '';
    }
}