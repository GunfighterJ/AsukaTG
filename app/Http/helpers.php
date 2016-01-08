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
     * Checks to see if a given user is actually the bot.
     * @param User $user The user we're comparing.
     * @return bool
     */
    public static function userIsMe(User $user)
    {
        return $user->getId() == app('telegram')->bot()->getMe()->getId();
    }

    /**
     * Checks to see if two users are actually the same user.
     * @param User $user1
     * @param User $user2
     * @return bool
     */
    public static function usersAreSame(User $user1, User $user2)
    {
        return $user1->getId() == $user2->getId();
    }

    /**
     * Similar to file_get_contents() but only works on URLs and uses cURL.
     * @param $url
     * @param bool $dieOnError Exit the whole script if curl throws an error.
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

    /**
     * Sends a message to a Telegram Chat.
     * @param $message
     * @param $chatId
     * @param array $params Extra Telegram Bot API parameters to send with this message.
     */
    public static function sendMessage($message, $chatId, $params = [])
    {
        $params['chat_id'] = $chatId;
        $params['text'] = $message;

        app('telegram')->bot()->sendMessage($params);
    }

    /**
     * Escapes Markdown special characters in a string with backslashes.
     * @param $string
     * @return mixed
     */
    public static function escapeMarkdown($string)
    {
        return $string;
        //return preg_replace('/([*_])/i', '\\\\$1', $string);
    }
}

class AsukaDB
{
    /**
     * Creates a new quote from a Message containing another Message as a reply.
     * @param Message $message The Message to make a quote from. Assumed to contain a valid reply.
     * @return int|null Qoute ID on success, null on failure.
     */
    public static function createQuote(Message $message)
    {
        $db = app('db')->connection();
        $quoteSource = $message->getReplyToMessage();
        $messageId = $message->getReplyToMessage()->getMessageId();
        $groupId = $quoteSource->getChat()->getId();

        self::createOrUpdateUser($quoteSource->getFrom());

        $values = [
            'added_by_id'       => $message->getFrom()->getId(),
            'user_id'           => $quoteSource->getFrom()->getId(),
            'group_id'          => $groupId,
            'message_id'        => $messageId,
            'message_timestamp' => $quoteSource->getDate(),
            'content'           => $quoteSource->getText()
        ];

        $existing = $db->table('quotes')->where('message_id', $messageId)->where('group_id', $groupId)->limit(1)->value('id');
        if (!$existing) {
            $quoteId = $db->table('quotes')->insertGetId($values);

            return $quoteId;
        } else {
            Helpers::sendMessage(sprintf('I already have that quote saved as #%s.', $existing), $groupId, ['reply_to_message_id' => $message->getMessageId()]);

            return null;
        }
    }

    /**
     * Adds a new {@User} to the database, or updates an existing one if it already exists.
     * @param User $user The user to add to the database.
     */
    public static function createOrUpdateUser(User $user)
    {
        $db = app('db')->connection();
        $values = [
            'id'         => $user->getId(),
            'first_name' => $user->getFirstName(),
            'last_name'  => $user->getLastName() ? $user->getLastName() : null,
            'username'   => $user->getUsername() ? $user->getUsername() : null,
        ];

        if (!$db->table('users')->where('id', $user->getId())->limit(1)->value('id')) {
            $db->table('users')->insert($values);
        } else {
            unset($values['id']);
            $db->table('users')->where('id', $user->getId())->update($values);
        }
    }

    /**
     * Fetches quote data for a quote with an ID matching $id, or a random quote if $id is not specified
     * @param null $id
     * @return mixed|static Returns a quote object on success.
     */
    public static function getQuote($id = null)
    {
        $db = app('db')->connection();

        if (!$id) {
            return $db->table('quotes')->limit(1)->orderByRaw('RAND()')->first();
        }

        return $db->table('quotes')->where('id', $id)->limit(1)->first();
    }

    /**
     * Fetches user data for a user with an ID matching $id
     * @param null $id
     * @return mixed|static Returns a user object on success.
     */
    public static function getUser($id)
    {
        $db = app('db')->connection();

        return $db->table('users')->where('id', $id)->limit(1)->first();
    }

    /**
     * Adds a new group to the database, or updates an existing one if it already exists.
     * @param Chat $group Group to add to the database.
     */
    public static function createOrUpdateGroup(Chat $group)
    {
        $db = app('db')->connection();
        $values = [
            'id'    => $group->getId(),
            'title' => $group->getTitle(),
        ];

        if (!$db->table('groups')->where('id', $group->getId())->limit(1)->value('id')) {
            $db->table('groups')->insert($values);
        } else {
            self::updateGroup($group);
        }
    }

    /**
     * Updates an existing group with new data such as group titles.
     * @param Chat $group Group to update.
     */
    public static function updateGroup(Chat $group)
    {
        $db = app('db')->connection();
        $values = [
            'title' => $group->getTitle(),
        ];

        $db->table('groups')->where('id', $group->getId())->update($values);
    }
}
