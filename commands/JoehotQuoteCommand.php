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

use Telegram\Bot\Commands\Command;

class JoehotQuoteCommand extends Command
{
    protected $name = "jq";
    protected $description = "Returns a random joehot classic.";

    const QUOTE_DB_SOURCE_URL = 'https://git.yawk.at/?p=jhq-server.git;a=blob_plain;f=joehot.qt.txt;h=92a89c73f1aa7cf2120524111ef1e10262b70026;hb=HEAD';

    public function handle($arguments)
    {
        $dataPath = realpath(__DIR__) . '/../data/';
        $quoteDatabase = $dataPath . 'joehot.qt.txt';

        if (!file_exists($quoteDatabase)) {
            mkdir($dataPath);
            file_put_contents($quoteDatabase, file_get_contents(self::QUOTE_DB_SOURCE_URL));
        }

        $lines = file($quoteDatabase);
        $quote = $lines[array_rand($lines)];

        $quoteParts = [
            'citation' => 'joehot200',
            'text'     => null,
            'source'   => null,
        ];

        // Parse the quote DB according to the rules defined at https://git.yawk.at/?p=jhq-server.git;a=blob;f=README.md;h=a0894ebf6cd5bd94488bb61c0bf3d5ec54821e61;hb=HEAD
        $matches = [];
        if (preg_match('/^(.*)::/', $quote, $matches)) {
            $quoteParts['citation'] = trim(rtrim($matches[0], '::'));
            $quote = preg_replace('/^.*::/', '', $quote);
        }

        if (preg_match('/#(.*)$/', $quote, $matches)) {
            $quoteParts['source'] = trim(ltrim($matches[0], '#'));
            $quote = preg_replace('/#.*$/', '', $quote);
        }

        $quoteParts['text'] = trim($quote);

        $response = sprintf('*%s*' . PHP_EOL, $quoteParts['text']);
        $response .= sprintf('_-- %s_', $quoteParts['citation']);

        // Check for source and ensure it isn't just '^+'
        if ($quoteParts['source'] && !empty(trim(str_replace('^', '', $quoteParts['source'])))) {
            $response .= sprintf(PHP_EOL . PHP_EOL . 'Source: %s', $quoteParts['source']);
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
