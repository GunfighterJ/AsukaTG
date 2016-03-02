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

use Asuka\Http\AsukaDB;
use Asuka\Http\Helpers;

class QuoteCommand extends BaseCommand
{
    protected $description = 'Returns a random quote or adds a new quote if a message is supplied as a reply.';
    protected $name = 'q';

    public function handle($arguments)
    {
        $message = $this->getUpdate()->getMessage();
        // Detect a reply and add it as a quote
        $quoteSource = $message->getReplyToMessage();
        if ($quoteSource) {
            if (!Helpers::isGroup($message->getChat())) {
                $this->reply('You can only add quotes in a group.');

                return;
            }

            if (Helpers::userIsMe($quoteSource->getFrom())) {
                $this->reply('You cannot quote me >:)');

                return;
            }

            if (Helpers::isCommand($quoteSource)) {
                $this->reply('Don\'t be silly, why would you quote commands?');

                return;
            }

            if (Helpers::usersAreSame($message->getFrom(), $quoteSource->getFrom())) {
                $this->reply('Why would you quote yourself? What are you, some kind of loner?');

                return;
            }

            $messageType = $this->getTelegram()->detectMessageType($quoteSource);
            if ($messageType != 'text') {
                $this->reply(sprintf('I cannot quote %s messages, please send me a text message.', $messageType));

                return;
            }

            $result = AsukaDB::createQuote($message);
            if ($result) {
                $this->reply(
                    sprintf('Quote saved as #%d', $result),
                    ['reply_to_message_id' => $quoteSource->getMessageId()]
                );
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

            $quote = AsukaDB::getQuote($quoteId);
        } else {
            // Random quote
            $quote = AsukaDB::getQuote();
        }

        if (!$quote) {
            $this->reply('No quote found!');

            return;
        }

        $response = sprintf('<b>%s</b>' . PHP_EOL, Helpers::escapeMarkdown($quote->content));
        $quotee = AsukaDB::getUser($quote->user_id);
        $quoter = AsukaDB::getUser($quote->added_by_id);

        $citation = $quotee->first_name;
        if ($quotee->last_name) {
            $citation .= sprintf(' %s', $quotee->last_name);
        }

        if ($quotee->username) {
            $citation .= sprintf(' (%s)', $quotee->username);
        }

        $response .= sprintf(
            '-- <i>%s, %s (#%d)</i>' . PHP_EOL,
            Helpers::escapeMarkdown($citation),
            date('D, jS M Y H:i:s T', $quote->message_timestamp),
            $quote->id
        );

        $addedBy = $quoter->first_name;
        if ($quoter->last_name) {
            $addedBy .= sprintf(' %s', $quoter->last_name);
        }

        if ($quoter->username) {
            $addedBy .= sprintf(' (%s)', $quoter->username);
        }

        $response .= sprintf(PHP_EOL . 'Added by %s' . PHP_EOL, Helpers::escapeMarkdown($addedBy));

        if ($quote->comment) {
            $response .= sprintf('Comment: %s', Helpers::escapeMarkdown($quote->comment));
        }

        $this->reply($response, ['disable_web_page_preview' => true, 'parse_mode' => 'HTML']);
    }
}
