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

use FluentLiteral;

class QuoteCommand extends BaseCommand
{
    protected $description = 'Returns a random quote or adds a new quote if a message is supplied as a reply.';

    protected $name = 'q';

    public function handle($arguments)
    {
        $db = $this->getDatabase();
        $this->createOrUpdateUser($this->getUpdate()->getMessage()->getFrom());

        // Detect a reply and add it as a quote
        $quoteSource = $this->getUpdate()->getMessage()->getReplyToMessage();
        if ($quoteSource) {
            if ($this->getUpdate()->getMessage()->getChat()->getType() != 'group') {
                $this->reply('You can only add quotes in a group.');

                return;
            }

            if ($this->getTelegram()->getMe()->getId() == $quoteSource->getFrom()->getId()) {
                $this->reply('You cannot quote me >:)');

                return;
            }

            $messageType = $this->getTelegram()->detectMessageType($quoteSource);
            if ($messageType != 'text') {
                $this->reply(sprintf('I cannot quote %s messages, please send me a text message.', $messageType));

                return;
            }

            $quoteUser = $quoteSource->getFrom();
            $this->createOrUpdateUser($quoteUser);

            $values = [
                'content'           => $quoteSource->getText(),
                'chat_id'           => $quoteSource->getChat()->getId(),
                'message_id'        => $quoteSource->getMessageId(),
                'user_id'           => $quoteSource->getFrom()->getId(),
                'addedby_id'        => $this->getUpdate()->getMessage()->getFrom()->getId(),
                'message_timestamp' => $quoteSource->getDate()
            ];

            $result = $db->insertInto('quotes', $values)->execute();
            if ($result) {
                $this->reply(sprintf('Quote saved as #%s', $result), [
                    'reply_to_message_id' => $this->getUpdate()->getMessage()->getReplyToMessage()->getMessageId(),
                ]);
            } else {
                if ($db->getPdo()->errorCode() == 23000) {
                    $this->reply('I already have that quote.', [
                        'reply_to_message_id' => $this->getUpdate()->getMessage()->getReplyToMessage()->getMessageId(),
                    ]);

                    return;
                }
                $this->reply($db->getPdo()->errorInfo()[2]);
            }

            return;
        }

        if ($arguments) {
            $arguments = explode(' ', $arguments);
            $quoteId = intval(preg_replace('/[^0-9]/', '', $arguments[0]));

            if (!$quoteId) {
                $this->reply('Please supply a numeric quote ID.');

                return;
            }

            $getQuoteStmnt = $db->from('quotes')->select('*')->where('id', $quoteId)->limit(1);
        } else {
            // Random quote
            $getQuoteStmnt = $db->from('quotes')->select('*')->orderBy(new FluentLiteral('RANDOM()'))->limit(1);
        }

        if ($getQuoteStmnt->execute()) {
            $quote = $getQuoteStmnt->fetch();
            if ($quote) {
                $response = sprintf('%s' . PHP_EOL, $this->escapeMarkdown($quote->content));
                $user = $this->getUserById($quote->user_id);

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
            $this->reply($db->getPdo()->errorInfo()[2]);
        }
    }
}
