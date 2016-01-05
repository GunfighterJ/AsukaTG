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

        if (!$this->createOrUpdateUser($this->getUpdate()->getMessage()->getFrom())) {
            return;
        };

        // Detect a reply and add it as a quote
        $quoteSource = $this->getUpdate()->getMessage()->getReplyToMessage();
        if ($quoteSource) {
            if ($this->getUpdate()->getMessage()->getChat()->getType() != 'group') {
                $this->reply('You can only add quotes in a group.');

                return;
            }

            $this->getTelegram()->setAsyncRequest(false);
            if ($this->getTelegram()->getMe()->getId() == $quoteSource->getFrom()->getId()) {
                $this->reply('You cannot quote me >:)');

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

            $createQuoteStmnt = $db->prepare('INSERT INTO quotes (content, chat_id, message_id, user_id, addedby_id, message_timestamp) VALUES (:content, :chat_id, :message_id, :user_id, :addedby_id, :message_timestamp)');
            $createQuoteStmnt->bindValue(':content', $quoteSource->getText(), PDO::PARAM_STR);
            $createQuoteStmnt->bindValue(':chat_id', $quoteSource->getChat()->getId(), PDO::PARAM_INT);
            $createQuoteStmnt->bindValue(':message_id', $quoteSource->getMessageId(), PDO::PARAM_INT);
            $createQuoteStmnt->bindValue(':user_id', $quoteSource->getFrom()->getId(), PDO::PARAM_INT);
            $createQuoteStmnt->bindValue(':addedby_id', $this->getUpdate()->getMessage()->getFrom()->getId(), PDO::PARAM_INT);
            $createQuoteStmnt->bindValue(':message_timestamp', $quoteSource->getDate(), PDO::PARAM_INT);

            if ($createQuoteStmnt->execute()) {
                $this->reply(sprintf('Quote saved as #%s', $db->lastInsertId()), [
                    'reply_to_message_id' => $this->getUpdate()->getMessage()->getReplyToMessage()->getMessageId(),
                ]);
            } else {
                if ($createQuoteStmnt->errorCode() == 23000) {
                    $this->reply('I already have that quote.', [
                        'reply_to_message_id' => $this->getUpdate()->getMessage()->getReplyToMessage()->getMessageId(),
                    ]);

                    return;
                }
                $this->reply($createQuoteStmnt->errorInfo()[2]);
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

            $getQuoteStmnt = $db->prepare('SELECT * FROM quotes WHERE id = :id LIMIT 1');
            $getQuoteStmnt->bindValue(':id', $quoteId, PDO::PARAM_INT);
        } else {
            // Random quote
            $getQuoteStmnt = $db->prepare('SELECT * FROM quotes ORDER BY RANDOM() LIMIT 1');
        }

        if ($getQuoteStmnt->execute()) {
            $quote = $getQuoteStmnt->fetch(PDO::FETCH_OBJ);
            if (isset($quote->id)) {
                $response = sprintf('%s' . PHP_EOL, $this->escapeMarkdown($quote->content));

                $user = $this->getDBUser($quote->user_id);

                $citation = $user->first_name;
                if ($user->last_name) {
                    $citation .= sprintf(' %s', $user->last_name);
                }

                if ($user->username) {
                    $citation .= sprintf(' (%s)', $user->username);
                }

                $response .= sprintf('-- %s, %s (#%s)', $this->escapeMarkdown($citation), date('D, jS M Y H:i:s T', $quote->message_timestamp), $quote->id);

                $this->reply($response, [
                    'disable_web_page_preview' => true,
                ]);
            } else {
                $this->reply('No such quote!');
            }
        } else {
            $this->reply($getQuoteStmnt->errorInfo()[2]);
        }
    }

    private function escapeMarkdown($string)
    {
        return $string;
        //return preg_replace('/([*_])/i', '\\\\$1', $string);
    }
}
