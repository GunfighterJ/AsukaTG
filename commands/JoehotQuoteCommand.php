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

class JoehotQuoteCommand extends Command
{
    protected $name = "jq";
    protected $description = "Returns a random joehot classic.";

    public function handle($arguments)
    {
        $dataPath = realpath(__DIR__) . '/../data/';
        $quoteDatabase = $dataPath . 'joehot.db';

        if (!file_exists($quoteDatabase)) {
            $this->reply("Quote database doesn't exist.");

            return;
        }

        $db = new PDO('sqlite:' . $quoteDatabase);
        // Random quote
        $result = $db->query('SELECT citation, source, quote FROM quotes WHERE id >= random() % (SELECT max(id) FROM quotes) LIMIT 1');
        $quote = $result->fetch(PDO::FETCH_ASSOC);

        $response = sprintf('*%s*' . PHP_EOL, $quote['quote']);
        $response .= sprintf('_-- %s_', $quote['citation']);

        if ($quote['source']) {
            $response .= sprintf(PHP_EOL . PHP_EOL . 'Source: %s', $quote['source']);
        }

        $this->reply($response);
    }

    private function reply($response)
    {
        $this->replyWithMessage([
            'text'                     => $response,
            'disable_web_page_preview' => true,
            'parse_mode'               => 'Markdown',
            'reply_to_message_id'      => $this->getUpdate()->getMessage()->getMessageId()
        ]);
    }
}
