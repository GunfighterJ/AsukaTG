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

class QuoteCommand extends Command
{
    protected $name = "q";
    protected $description = "Returns a random quote.";

    public function handle($arguments)
    {
        $dataPath = realpath(__DIR__) . '/../data/';
        $quoteDatabase = $dataPath . 'joehot.db';

        if (!file_exists($quoteDatabase)) {
            $this->reply("Quote database doesn't exist.");

            return;
        }

        $db = new PDO('sqlite:' . $quoteDatabase);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

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
                $response = sprintf('Quote #%d:' . PHP_EOL, $quote->id);
                $response .= sprintf('*%s*' . PHP_EOL, $quote->quote);
                $response .= sprintf('_-- %s_', $quote->citation);

                if (isset($quote->source)) {
                    $response .= sprintf(PHP_EOL . PHP_EOL . 'Source: %s', $quote->source);
                }

                $this->reply($response);
            } else {
                $this->reply('No such quote!');
            }
        }
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
