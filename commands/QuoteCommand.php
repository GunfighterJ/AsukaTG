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

namespace Asuka\Commands;

use PDO;
use Telegram\Bot\Objects\User;

class QuoteCommand extends BaseCommand
{
    protected $description = 'Returns a random quote or adds a new quote if a message is supplied as a reply.';

    protected $name = 'q';

    public function handle($arguments)
    {
        $db = $this->getDatabase();
        if (!$db) {
            return;
        }

        $getUserSth = $db->prepare('SELECT * FROM users WHERE user_id = :user_id LIMIT 1');

        // Detect a reply and add it as a quote
        $quoteSource = $this->getUpdate()->getMessage()->getReplyToMessage();
        if ($quoteSource) {
            if ($this->getUpdate()->getMessage()->getChat()->getType() != 'group') {
                $this->reply('You can only add quotes in a group.');
                return;
            }

            $messageType = $this->getTelegram()->detectMessageType($quoteSource);
            if ($messageType != 'text') {
                $this->reply(sprintf('I cannot quote %s messages, please send me a text message.', $messageType));

                return;
            }

            $quoteUser = $quoteSource->getFrom();
            $eventUsers = [$quoteUser, $this->getUpdate()->getMessage()->getFrom()];

            $createUserSth = $db->prepare('INSERT INTO users (user_id, first_name, last_name, username) VALUES (:user_id, :first_name, :last_name, :username)');

            foreach ($eventUsers as $user) {
                $getUserSth->bindValue(':user_id', $user->getId(), PDO::PARAM_INT);
                if ($getUserSth->execute()) {
                    $dbUser = $getUserSth->fetch(PDO::FETCH_OBJ);
                    if (!isset($dbUser->id)) {
                        $createUserSth->bindValue(':user_id', $user->getId(), PDO::PARAM_INT);
                        $createUserSth->bindValue(':first_name', $user->getFirstName(), PDO::PARAM_STR);
                        $createUserSth->bindValue(':last_name', $user->getFirstName() ? $user->getFirstName() : null, PDO::PARAM_STR);
                        $createUserSth->bindValue(':username', $user->getUsername() ? $user->getUsername() : null, PDO::PARAM_STR);

                        if (!$createUserSth->execute()) {
                            $this->reply($createUserSth->errorInfo()[2]);
                            return;
                        }
                    }
                } else {
                    $this->reply($getUserSth->errorInfo()[2]);
                    return;
                }
            }

            $sth = $db->prepare('INSERT INTO quotes (content, chat_id, message_id, user_id, addedby_id, message_timestamp) VALUES (:content, :chat_id, :message_id, :user_id, :addedby_id, :message_timestamp)');
            $sth->bindValue(':content', $quoteSource->getText(), PDO::PARAM_STR);
            $sth->bindValue(':chat_id', $quoteSource->getChat()->getId(), PDO::PARAM_INT);
            $sth->bindValue(':message_id', $quoteSource->getMessageId(), PDO::PARAM_INT);
            $sth->bindValue(':user_id', $quoteSource->getFrom()->getId(), PDO::PARAM_INT);
            $sth->bindValue(':addedby_id', $this->getUpdate()->getMessage()->getFrom()->getId(), PDO::PARAM_INT);
            $sth->bindValue(':message_timestamp', $quoteSource->getDate(), PDO::PARAM_INT);

            if ($sth->execute()) {
                $this->reply(sprintf('Quote saved as #%s', $db->lastInsertId()), [
                    'reply_to_message_id' => $this->getUpdate()->getMessage()->getReplyToMessage()->getMessageId()
                ]);
            } else {
                if ($sth->errorCode() == 23000) {
                    $this->reply('I already have that quote.', [
                        'reply_to_message_id' => $this->getUpdate()->getMessage()->getReplyToMessage()->getMessageId()
                    ]);

                    return;
                }
                $this->reply($sth->errorInfo()[2]);
            }

            return;
        }

        if ($arguments) {
            $arguments = explode(' ', $arguments);
            $quoteId = intval(trim(trim($arguments[0], '#')));

            if (!$quoteId) {
                $this->reply('Please supply a numeric quote ID.');

                return;
            }

            $sth = $db->prepare('SELECT * FROM quotes WHERE id = :id LIMIT 1');
            $sth->bindValue(':id', $quoteId, PDO::PARAM_INT);
        } else {
            // Random quote
            $sth = $db->prepare('SELECT * FROM quotes ORDER BY RANDOM() LIMIT 1');
        }

        if ($sth->execute()) {
            $quote = $sth->fetch(PDO::FETCH_OBJ);
            if (isset($quote->id)) {
                $response = sprintf('Quote #%d added at %s' . PHP_EOL . PHP_EOL, $quote->id, date('r', strtotime($quote->added_timestamp)));
                $response .= sprintf('%s' . PHP_EOL, $this->escapeMarkdown($quote->content));

                $getUserSth->bindValue('user_id', $quote->user_id);
                $user = $getUserSth->fetch(PDO::FETCH_OBJ);

                $citation = $user->first_name;
                if ($user->last_name) {
                    $citation .= sprintf(' %s', $user->last_name);
                }

                if ($user->username) {
                    $citation .= sprintf(' (@%s)', $user->username);
                }

                $response .= sprintf('-- %s, %s', $this->escapeMarkdown($citation), date('r', $quote->message_timestamp));

                $this->reply($response, [
                    'disable_web_page_preview' => true
                ]);
            } else {
                $this->reply('No such quote!');
            }
        } else {
            $this->reply($sth->errorInfo()[2]);
        }
    }

    private function escapeMarkdown($string)
    {
        return $string;
        //return preg_replace('/([*_])/i', '\\\\$1', $string);
    }
}
