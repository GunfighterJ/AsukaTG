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

namespace Asuka\Http\Controllers;

use Asuka\Http\AsukaDB;
use Asuka\Http\Helpers;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BotController extends Controller
{
    function index(Request $request)
    {
        $telegram = app('telegram')->bot();
        $updates = $telegram->getWebhookUpdates();

        if (!$updates) {
            return response('No Updates');
        }

        if ($updates->getMessage()->getFrom()) {
            AsukaDB::createOrUpdateUser($updates->getMessage()->getFrom());
        }

        if ($updates->getMessage()->getChat()->getType() == 'group') {
            if ($updates->getMessage()->getGroupChatCreated() ||
                ($updates->getMessage()->getNewChatParticipant() && Helpers::userIsMe($updates->getMessage()->getNewChatParticipant())))
            {
                AsukaDB::createOrUpdateGroup($updates->getMessage()->getChat());
            }

            if ($updates->getMessage()->getNewChatTitle()) {
                AsukaDB::updateGroup($updates->getMessage()->getChat());
            }
        }

        $telegram->commandsHandler($request->getMethod() == Request::METHOD_POST);

        return $updates;
    }

    function updateWebhook($action, $botKey)
    {
        $telegram = app('telegram');

        $bot = $telegram->bot();

        if ($action == 'set') {
            return $bot->setWebhook(['url' => url($botKey)]);
        } elseif ($action == 'remove') {
            return $bot->removeWebhook();
        }

        throw new NotFoundHttpException;
    }
}