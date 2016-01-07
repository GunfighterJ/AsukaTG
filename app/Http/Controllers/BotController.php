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
    function index(Request $request)
    {
        $telegram = app('telegram')->bot();
        $updates = $telegram->commandsHandler($request->getMethod() == Request::METHOD_POST);
        return $updates;
    }

    function updateWebhook(Request $request, $action, $botKey)
    {
        $telegram = app('telegram');

        $ownerId = $telegram->getBotConfig(config('telegram.default'))['owner_id'];
        if ($ownerId) {
            sendMessage(sprintf('The IP %s just accessed %s', $request->getClientIp(), $request->url()), $ownerId);
        }

        $bot = $telegram->bot();

        if ($action == 'set') {
            return $bot->setWebhook(['url' => url($botKey)]);
        } elseif ($action == 'remove') {
            return $bot->removeWebhook();
        }
        return '';
    }
}