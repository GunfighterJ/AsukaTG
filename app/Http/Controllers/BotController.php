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
use Illuminate\Support\Facades\Log;
use Monolog\Logger;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BotController extends Controller
{
    function index()
    {
        return app('telegram')->bot()->getMe()->toJson();
    }

    function webhook()
    {
        $telegram = app('telegram')->bot();
        $message = $telegram->getWebhookUpdates()->getMessage();


        if (in_array($message->getChat()->getType(), ['group', 'supergroup'])) {
            // Check if this group is authorised to use the bot
            if (count(config('asuka.groups.groups_list'))) {
                if (config('asuka.groups.groups_mode') === 'whitelist'
                    && !in_array($message->getChat()->getId(), config('asuka.groups.groups_list'))) {
                    return response('OK');
                    // blacklist
                } elseif (config('asuka.groups.groups_mode') === 'blacklist'
                    && in_array($message->getChat()->getId(), config('asuka.groups.groups_list'))) {
                    return response('OK');
                }
            }

            // Store this group if it's a new group or the title was updated
            if ($message->getGroupChatCreated() ||
                ($message->getNewChatParticipant() && Helpers::userIsMe($message->getNewChatParticipant()))) {
                AsukaDB::createOrUpdateGroup($message->getChat());
            }

            if ($message->getNewChatTitle()) {
                AsukaDB::updateGroup($message->getChat());
            }
        }

        if (!$message->getFrom()) {
            return response('OK');
        }

        AsukaDB::createOrUpdateUser($message->getFrom());

        if (AsukaDB::getUser($message->getFrom()->getId())->ignored) {
            return response('OK');
        }

        $telegram->commandsHandler(true);

        return response('OK');
    }

    function updateWebhook($action, $botKey)
    {
        $telegram = app('telegram');

        $bot = $telegram->bot();

        if ($action === 'set') {
            return $bot->setWebhook(['url' => route('bot.webhook', ['botKey' => $botKey])]);
        } elseif ($action === 'remove') {
            return $bot->removeWebhook();
        }

        throw new NotFoundHttpException;
    }
}