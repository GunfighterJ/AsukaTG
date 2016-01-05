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
    protected $description = 'Returns a random quote or adds a new quote if a message is supplied as a reply.';

    protected $name = 'q';

    public function handle($arguments)
    {
        $dataPath      = realpath(__DIR__) . '/../data/';
        $quoteDatabase = $dataPath . 'asuka.db';

        if (!file_exists($quoteDatabase)) {
            $this->reply("Quote database doesn't exist.");

            return;
        }

        $db = new PDO('sqlite:' . $quoteDatabase);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

        // Detect a reply and add it as a quote
        $quoteSource = $this->getUpdate()->getMessage()->getReplyToMessage();
        if ($quoteSource) {
            $quoteUser = implode(' ', [$quoteSource->getFrom()->getFirstName(), $quoteSource->getFrom()->getLastName()]);
            if (!empty($quoteSource->getFrom()->getUsername())) {
                $quoteUser .= sprintf(' (@%s)', $quoteSource->getFrom()->getUsername());
            }

            $db = new PDO('sqlite:' . $quoteDatabase);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $sth = $db->prepare('INSERT INTO quotes (citation, content) VALUES (:citation, :content)');
            $sth->bindValue(':citation', $quoteUser, PDO::PARAM_STR);
            $sth->bindValue(':content', $quoteSource->getText(), PDO::PARAM_STR);

            if ($sth->execute()) {
                $this->replyToAdd(sprintf('Quote saved as #%s', $db->lastInsertId()));
            } elseif ($db->errorInfo()) {
                $this->reply(implode(PHP_EOL, $db->errorInfo()));
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

            $sth = $db->prepare('SELECT * FROM quotes WHERE id = :id LIMIT 1');
            $sth->bindValue(':id', $quoteId, PDO::PARAM_INT);
        } else {
            // Random quote
            $sth = $db->prepare('SELECT * FROM quotes ORDER BY RANDOM() LIMIT 1');
        }

        if ($sth->execute()) {
            $quote = $sth->fetch(PDO::FETCH_OBJ);
            if (isset($quote->id)) {
                $response = sprintf('Quote #%d added at %s' . PHP_EOL . PHP_EOL, $quote->id, date('r', strtotime($quote->created_at)));

                $quote->quote = str_replace('*', '\*', $quote->content);
                $quote->quote = str_replace('_', '\_', $quote->content);
                $response .= sprintf('*%s*' . PHP_EOL, $quote->content);
                $response .= sprintf('_-- %s_', $quote->citation);
                $response .= sprintf(PHP_EOL . PHP_EOL . 'Source: %s', $quote->source);

                $this->reply($response);
            } else {
                $this->reply('No such quote!');
            }
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
