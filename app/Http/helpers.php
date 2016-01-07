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

namespace Asuka\Http;

use Telegram\Bot\Objects\Chat;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\User;

class Helpers
{
    /**
     * @param $url
     * @param bool $dieOnError
     * @return mixed
     */
    public static function curl_get_contents($url, $dieOnError = true)
    {
        $curlOpts = [
            CURLOPT_URL            => $url,
            CURLOPT_HEADER         => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'AsukaTG (https://github.com/TheReverend403/AsukaTG)',
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_FAILONERROR    => true,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $curlOpts);
        $output = curl_exec($ch);

        if (curl_errno($ch)) {
            $message = app('telegram')->bot()->getWebhookUpdates()->getMessage();
            self::sendMessage(curl_error($ch), $message->getChat()->getId(), $message->getMessageId());
            if ($dieOnError) {
                curl_close($ch);
                app()->abort(200);
            }
        }

        curl_close($ch);

        return $output;
    }

    public static function sendMessage($response, $chatId, $params = [])
    {
        $params['chat_id'] = $chatId;
        $params['text'] = $response;

        app('telegram')->bot()->sendMessage($params);
    }

    public static function escapeMarkdown($string)
    {
        return $string;
        //return preg_replace('/([*_])/i', '\\\\$1', $string);
    }
}

class AsukaDB
{
    public static function createOrUpdateUser(User $user)
    {
        $db = app('db')->connection();
        $values = [
            'id'         => $user->getId(),
            'first_name' => $user->getFirstName(),
            'last_name'  => $user->getLastName() ? $user->getLastName() : null,
            'username'   => $user->getUsername() ? $user->getUsername() : null,
        ];

        if (!count($db->table('users')->where('id', $user->getId())->limit(1)->get(['id']))) {
            $db->table('users')->insert($values);
        } else {
            unset($values['id']);
            $db->table('users')->where('id', $user->getId())->update($values);
        }
    }

    public static function createQuote(Message $message)
    {
        $db = app('db')->connection();
        $groupId = $message->getChat()->getId();
        $messageId = $message->getReplyToMessage()->getMessageId();

        $values = [
            'added_by_id'       => $message->getFrom()->getId(),
            'user_id'           => $message->getReplyToMessage()->getFrom()->getId(),
            'group_id'          => $groupId,
            'message_id'        => $messageId,
            'message_timestamp' => $message->getDate(),
            'content'           => $message->getText(),
        ];

        $quoteId = $db->table('quotes')->insertGetId($values);
        if ($quoteId) {
            return $quoteId;
        } else {
            Helpers::sendMessage('I already have that quote.', $messageId, ['reply_to_message_id' => $messageId]);

            return false;
        }
    }

    public static function getQuote(int $id)
    {
        $db = app('db')->connection();

        return $db->table('quotes')->where('id', $id)->limit(1)->get();
    }

    public static function createOrUpdateGroup(Chat $group)
    {
        $db = app('db')->connection();
        $values = [
            'id'    => $group->getId(),
            'title' => $group->getTitle(),
        ];

        if (!count($db->table('groups')->where('id', $group->getId())->limit(1)->get(['id']))) {
            $db->table('groups')->insert($values);
        } else {
            unset($values['id']);
            $db->table('groups')->where('id', $group->getId())->update($values);
        }
    }
}
