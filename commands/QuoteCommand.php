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

        // Detect a reply and add it as a quote
        $quoteSource = $this->getUpdate()->getMessage()->getReplyToMessage();
        if ($quoteSource) {
            if ($this->getUpdate()->getMessage()->getChat()->getType() != 'group') {
                $this->reply('You can only add quotes in a group.');
                return;
            }

            $messageType = $this->getTelegram()->detectMessageType($quoteSource);
            if ('text' != $messageType) {
                $this->reply(sprintf('I cannot quote %s messages, please send me a text message.', $messageType));

                return;
            }

            $quoteUser = $quoteSource->getFrom();
            if (!$this->createOrUpdateUser($quoteUser)) {
                return;
            };

            $createQuoteStmt = $db->prepare('INSERT INTO quotes (content, chat_id, message_id, user_id, addedby_id, message_timestamp) VALUES (:content, :chat_id, :message_id, :user_id, :addedby_id, :message_timestamp)');
            $createQuoteStmt->bindValue(':content', $quoteSource->getText(), PDO::PARAM_STR);
            $createQuoteStmt->bindValue(':chat_id', $quoteSource->getChat()->getId(), PDO::PARAM_INT);
            $createQuoteStmt->bindValue(':message_id', $quoteSource->getMessageId(), PDO::PARAM_INT);
            $createQuoteStmt->bindValue(':user_id', $quoteSource->getFrom()->getId(), PDO::PARAM_INT);
            $createQuoteStmt->bindValue(':addedby_id', $this->getUpdate()->getMessage()->getFrom()->getId(), PDO::PARAM_INT);
            $createQuoteStmt->bindValue(':message_timestamp', $quoteSource->getDate(), PDO::PARAM_INT);

            if ($createQuoteStmt->execute()) {
                $this->reply(sprintf('Quote saved as #%s', $db->lastInsertId()), [
                    'reply_to_message_id' => $this->getUpdate()->getMessage()->getReplyToMessage()->getMessageId(),
                ]);
            } else {
                if ($createQuoteStmt->errorCode() == 23000) {
                    $this->reply('I already have that quote.', [
                        'reply_to_message_id' => $this->getUpdate()->getMessage()->getReplyToMessage()->getMessageId(),
                    ]);

                    return;
                }
                $this->reply($createQuoteStmt->errorInfo()[2]);
            }

            return;
        }

        if ($arguments) {
            $arguments = explode(' ', $arguments);
            $quoteId   = intval(trim(trim($arguments[0], '#')));

            if (!$quoteId) {
                $this->reply('Please supply a numeric quote ID.');

                return;
            }

            $getQuoteStmt = $db->prepare('SELECT * FROM quotes WHERE id = :id LIMIT 1');
            $getQuoteStmt->bindValue(':id', $quoteId, PDO::PARAM_INT);
        } else {
            // Random quote
            $getQuoteStmt = $db->prepare('SELECT * FROM quotes ORDER BY RANDOM() LIMIT 1');
        }

        if ($getQuoteStmt->execute()) {
            $quote = $getQuoteStmt->fetch(PDO::FETCH_OBJ);
            if (isset($quote->id)) {
                $response = sprintf('Quote #%d added at %s' . PHP_EOL . PHP_EOL, $quote->id, date('r', strtotime($quote->added_timestamp)));
                $response .= sprintf('%s' . PHP_EOL, $this->escapeMarkdown($quote->content));

                $user = $this->getUser($quote->user_id);

                $citation = $user->first_name;
                if ($user->last_name) {
                    $citation .= sprintf(' %s', $user->last_name);
                }

                if ($user->username) {
                    $citation .= sprintf(' (@%s)', $user->username);
                }

                $response .= sprintf('-- %s, %s', $this->escapeMarkdown($citation), date('r', $quote->message_timestamp));

                $this->reply($response, [
                    'disable_web_page_preview' => true,
                ]);
            } else {
                $this->reply('No such quote!');
            }
        } else {
            $this->reply($getQuoteStmt->errorInfo()[2]);
        }
    }

    private function escapeMarkdown($string)
    {
        return $string;
        //return preg_replace('/([*_])/i', '\\\\$1', $string);
    }
}
