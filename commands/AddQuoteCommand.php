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
use Telegram\Bot\Commands\Command;

class AddQuoteCommand extends Command
{
    protected $description = 'Add a quote to the database';

    protected $name = 'aq';

    public function handle($arguments)
    {
        $dataPath      = realpath(__DIR__) . '/../data/';
        $quoteDatabase = $dataPath . 'quotes.db';

        if (!file_exists($quoteDatabase)) {
            $this->reply("Quote database doesn't exist.");

            return;
        }

        $quoteSource = $this->getUpdate()->getMessage()->getReplyToMessage();
        if (!$quoteSource) {
            $this->reply('Please give me a message to quote by replying to the message with the reply "/aq"');
            return;
        }

        $quoteUser = $quoteSource->getFrom()->getUsername();
        if (empty($quoteUser)) {
            $quoteUser = implode(' ', [$quoteSource->getFrom()->getFirstName(), $quoteSource->getFrom()->getLastName()]);
        }

        $db = new PDO('sqlite:' . $quoteDatabase);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sth = $db->prepare('INSERT INTO quotes (citation, source, quote) VALUES (:citation, :source, :quote)');
        $sth->bindValue(':citation', $quoteUser, PDO::PARAM_STR);
        $sth->bindValue(':source', 'Telegram', PDO::PARAM_STR);
        $sth->bindValue(':quote', $quoteSource->getText(), PDO::PARAM_STR);

        if ($sth->execute()) {
            $this->replyToAdd(sprintf('Quote saved as #%s!', $db->lastInsertId()));
        } elseif ($db->errorInfo()) {
            $this->reply(implode(PHP_EOL, $db->errorInfo()));
        }
    }

    private function reply($response)
    {
        $this->replyWithMessage([
            'text'                     => $response,
            'disable_web_page_preview' => true,
            'parse_mode'               => 'Markdown',
            'reply_to_message_id'      => $this->getUpdate()->getMessage()->getMessageId(),
        ]);
    }

    private function replyToAdd($response)
    {
        $this->replyWithMessage([
            'text'                     => $response,
            'disable_web_page_preview' => true,
            'reply_to_message_id'      => $this->getUpdate()->getMessage()->getReplyToMessage()->getMessageId(),
        ]);
    }
}
