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
        if ($this->getUpdate()->getMessage()->getChat()->getType() != 'group') {
            $this->reply('You can only use this command in a group.');

            return;
        }

        // Detect a reply and add it as a quote
        $quoteSource = $this->getUpdate()->getMessage()->getReplyToMessage();
        if ($quoteSource) {
            if ($this->getTelegram()->getMe()->getId() == $quoteSource->getFrom()->getId()) {
                $this->reply('You cannot quote me >:)');

                return;
            }

            if ($this->getUpdate()->getMessage()->getFrom()->getId() == $quoteSource->getFrom()->getId()) {
                $this->reply('Why would you quote yourself? What are you, some kind of loner?');

                return;
            }

            $messageType = $this->getTelegram()->detectMessageType($quoteSource);
            if ($messageType != 'text') {
                $this->reply(sprintf('I cannot quote %s messages, please send me a text message.', $messageType));

                return;
            }

            $result = AsukaDB::createQuote($this->getUpdate()->getMessage());
            if ($result) {
                $this->reply(sprintf('Quote saved as #%s', $result), [
                    'reply_to_message_id' => $quoteSource->getMessageId(),
                ]);
            }

            return;
        }

        $quote = null;
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

        if ($quote) {
            $response = sprintf('%s' . PHP_EOL, Helpers::escapeMarkdown($quote->content));
            $user = AsukaDB::getUser($quote->user_id);

            $citation = $user->first_name;
            if ($user->last_name) {
                $citation .= sprintf(' %s', $user->last_name);
            }

            if ($user->username) {
                $citation .= sprintf(' (%s)', $user->username);
            }

            $response .= sprintf('-- %s, %s (#%s)', Helpers::escapeMarkdown($citation), date('D, jS M Y H:i:s T', $quote->message_timestamp), $quote->id);

            $this->reply($response, [
                'disable_web_page_preview' => true,
            ]);
        } else {
            $this->reply('No quote found!');
        }
    }
}
