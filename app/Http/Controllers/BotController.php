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
use Exception;
use IPRIT\BotanSDK\Botan;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BotController extends Controller
{
    function index()
    {
        return response()->json(app('telegram')->bot()->getMe());
    }

    function webhook()
    {
        $telegram = app('telegram')->bot();
        $message = $telegram->getWebhookUpdates()->getMessage();

        if (!$message || !$message->getFrom()) {
            return response('OK');
        }

        AsukaDB::createOrUpdateUser($message->getFrom());

        if (Helpers::isGroup($message->getChat())) {
            // Store this group if it's a new group or the title was updated
            if (($message->getGroupChatCreated() || $message->getSupergroupChatCreated()) || ($message->getNewChatParticipant())) {
                AsukaDB::createOrUpdateGroup($message->getChat());
            }

            if ($message->getNewChatTitle()) {
                AsukaDB::updateGroup($message->getChat());
            }

            // Check if this group is authorised to use the bot
            if (!Helpers::groupIsAuthorized($message->getChat())) {
                return response('OK');
            }
        }

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

    public function showQuotes()
    {
        $quotes = app('db')->connection()->table('quotes')->paginate(30);
        return view('quotes')->with('quotes', $quotes)->with('botName', app('telegram')->bot()->getMe()->name);
    }
}
