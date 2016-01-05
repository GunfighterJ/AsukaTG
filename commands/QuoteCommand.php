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
        $dataPath = realpath(__DIR__) . '/../data/';
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
            if ($quoteSource->getFrom()->getId() === $this->getTelegram()->getMe()->getId()) {
                $this->reply('Don\'t be silly, why would you quote a bot?');
                return;
            }
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
                $this->reply(sprintf('Quote saved as #%s', $db->lastInsertId()), [
                    'reply_to_message_id' => $this->getUpdate()->getMessage()->getReplyToMessage()->getMessageId(),
                ]);
            } elseif ($db->errorInfo()) {
                $this->reply(implode(PHP_EOL, $db->errorInfo()));
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
                $response = sprintf('Quote #%d added at %s' . PHP_EOL . PHP_EOL, $quote->id, date('r', strtotime($quote->created)));
                $response .= sprintf('*%s*' . PHP_EOL, $this->escapeMarkdown($quote->content));
                $response .= sprintf('_-- %s_', $this->escapeMarkdown($quote->citation));
                $response .= sprintf(PHP_EOL . PHP_EOL . 'Source: %s', $quote->source);

                $this->reply($response, [
                    'parse_mode' => 'Markdown',
                    'disable_web_page_preview' => true
                ]);
            } else {
                $this->reply('No such quote!');
            }
        } elseif ($db->errorInfo()) {
            $this->reply(implode(PHP_EOL, $db->errorInfo()));
        }
    }

    private function escapeMarkdown($string)
    {
        return preg_replace('/([*_])/i', '\\\\$1', $string);
    }
}
