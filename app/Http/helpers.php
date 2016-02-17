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

use Exception;
use GuzzleHttp\Exception\RequestException;
use Telegram\Bot\Objects\Chat;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\User;
use GuzzleHttp\Client;

class Helpers
{
    /**
     * Checks to see if a given user is actually the bot.
     *
     * @param User $user The user we're comparing.
     * @return bool
     */
    public static function userIsMe(User $user)
    {
        return $user->getId() == app('telegram')->bot()->getMe()->getId();
    }

    /**
     * Checks to see if two users are actually the same user.
     *
     * @param User $user1
     * @param User $user2
     * @return bool
     */
    public static function usersAreSame(User $user1, User $user2)
    {
        return $user1->getId() == $user2->getId();
    }

    /**
     * Checks whether or not a user is the bot's owner.
     *
     * @param User $user
     * @return bool
     */
    public static function userIsOwner(User $user)
    {
        return $user->getId() == app('telegram')->getBotConfig(config('telegram.default'))['owner_id'];
    }

    /**
     * Similar to file_get_contents() but only works on URLs and uses cURL.
     *
     * @param $url
     * @param bool $dieOnError Exit the whole script if an error is thrown.
     * @return mixed
     */
    public static function urlGetContents($url, $dieOnError = true)
    {
        $client = new Client();

        try {
            $response = $client->get($url);
        } catch (RequestException $ex) {
            if ($ex->hasResponse()) {
                $message = app('telegram')->bot()->getWebhookUpdates()->getMessage();
                self::sendMessage($ex->getResponse(), $message->getChat()->getId(), $message->getMessageId());
            }

            if ($dieOnError) {
                app()->abort(200);
            }

            return null;
        }

        return $response->getBody();
    }

    /**
     * Sends a message to a Telegram Chat.
     *
     * @param $message
     * @param $chatId
     * @param array $params Extra Telegram Bot API parameters to send with this message.
     */
    public static function sendMessage($message, $chatId, $params = [])
    {
        $params['chat_id'] = $chatId;
        $params['text'] = str_limit($message, 4096);

        app('telegram')->bot()->sendMessage($params);
    }

    /**
     * Escapes Markdown special characters in a string with backslashes.
     *
     * @param $string
     * @return mixed
     */
    public static function escapeMarkdown($string)
    {
        return $string;
        //return preg_replace('/([*_])/i', '\\\\$1', $string);
    }

    /**
     * Tries to use random_int() (random_compat or PHP 7) to get a random integer and falls back to mt_rand() on failure.
     * Not cryptographically secure due to the fallback.
     *
     * @param $min
     * @param $max
     * @return int random integer between $min and $max
     */
    public static function getRandomInt($min, $max)
    {
        try {
            // Uses random_compat for PHP < 7
            return random_int($min, $max);
        } catch (Exception $ex) {
            $message = app('telegram')->bot()->getWebhookUpdates()->getMessage();

            $error = 'Error occurred in random_int(), falling back to mt_rand()' . PHP_EOL;
            $error .= $ex->getMessage();

            self::sendMessage($error, $message->getChat()->getId(), $message->getMessageId());

            return mt_rand($min, $max);
        }
    }
}

class AsukaDB
{
    /**
     * Creates a new quote from a Message containing another Message as a reply.
     *
     * @param Message $message The Message to make a quote from. Assumed to contain a valid reply.
     * @return int|null Qoute ID on success, null on failure.
     */
    public static function createQuote(Message $message)
    {
        $db = app('db')->connection()->table('quotes');
        $quoteSource = $message->getReplyToMessage();
        $messageId = $message->getReplyToMessage()->getMessageId();
        $groupId = $quoteSource->getChat()->getId();
        $comment = ltrim($message->getText(), '/q ');

        self::createOrUpdateUser($quoteSource->getFrom());

        $values = [
            'added_by_id'       => $message->getFrom()->getId(),
            'user_id'           => $quoteSource->getFrom()->getId(),
            'group_id'          => $groupId,
            'message_id'        => $messageId,
            'message_timestamp' => $quoteSource->getDate(),
            'content'           => $quoteSource->getText(),
            'comment'           => empty($comment) ? null : $comment,
        ];

        $existing = $db->where('message_id', $messageId)->where('group_id', $groupId)->limit(1)->value('id');
        if (!$existing) {
            return $db->insertGetId($values);
        } else {
            Helpers::sendMessage(sprintf('I already have that quote saved as #%s.', $existing), $groupId, [
                'reply_to_message_id' => $message->getMessageId()
            ]);

            return null;
        }
    }

    /**
     * Adds a new {@User} to the database, or updates an existing one if it already exists.
     *
     * @param User $user The user to add to the database.
     * @param array $params
     */
    public static function createOrUpdateUser(User $user, $params = [])
    {
        $db = app('db')->connection()->table('users');
        $values = [
            'id'         => $user->getId(),
            'first_name' => $user->getFirstName(),
            'last_name'  => $user->getLastName() ? $user->getLastName() : null,
            'username'   => $user->getUsername() ? $user->getUsername() : null,
        ];

        $values = array_merge($params, $values);
        if (!$db->where('id', $user->getId())->limit(1)->value('id')) {
            $db->insert($values);
        } else {
            unset($values['id']);
            $db->where('id', $user->getId())->update($values);
        }
    }

    /**
     * Adds a new {@User} to the database, or updates an existing one if it already exists.
     *
     * @param User $user The user to ignore or unignore to the database.
     * @param bool $ignored
     */
    public static function updateUserIgnore(User $user, $ignored = true)
    {
        self::createOrUpdateUser($user, ['ignored' => $ignored]);
    }

    /**
     * Fetches quote data for a quote with an ID matching $id, or a random quote if $id is not specified.
     * Optionally specify a group ID to only get quotes originating from that group.
     *
     * @param null $id
     * @param int|null $groupId
     * @return mixed|static Returns a quote object on success.
     */
    public static function getQuote($id = null, $groupId = null)
    {
        $db = app('db')->connection()->table('quotes')->limit(1);

        if (!$id) {
            $db = $db->orderByRaw('RAND()');
        } else {
            $db = $db->where('id', $id);
        }

        if ($groupId) {
            $db = $db->where('group_id', $groupId);
        }

        return $db->first();
    }

    /**
     * Fetches user data for a user with an ID matching $id
     *
     * @param null $id
     * @return mixed|static Returns a user object on success.
     */
    public static function getUser($id)
    {
        $db = app('db')->connection()->table('users');

        return $db->where('id', $id)->limit(1)->first();
    }

    /**
     * Adds a new group to the database, or updates an existing one if it already exists.
     *
     * @param Chat $group Group to add to the database.
     */
    public static function createOrUpdateGroup(Chat $group)
    {
        $db = app('db')->connection()->table('groups');
        $values = [
            'id'    => $group->getId(),
            'title' => $group->getTitle(),
        ];

        if (!$db->where('id', $group->getId())->limit(1)->value('id')) {
            $db->insert($values);
        } else {
            self::updateGroup($group);
        }
    }

    /**
     * Updates an existing group with new data such as group titles.
     *
     * @param Chat $group Group to update.
     */
    public static function updateGroup(Chat $group)
    {
        $db = app('db')->connection()->table('groups');
        $values = [
            'title' => $group->getTitle(),
        ];

        $db->where('id', $group->getId())->update($values);
    }
}
