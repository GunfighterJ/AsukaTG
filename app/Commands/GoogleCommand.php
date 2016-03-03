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

use Asuka\Http\Helpers;
use Telegram\Bot\Actions;

class GoogleCommand extends BaseCommand
{
    protected $description = 'Returns the first Google result for a set of search terms.';
    protected $name = 'g';

    public function handle($arguments)
    {
        if (empty($arguments)) {
            $badArgsResponse = implode(
                PHP_EOL,
                [
                    'Please supply some search terms.',
                    'Example: /g What happens if you Google Google?',
                ]
            );
            $this->reply($badArgsResponse);

            return;
        }

        $this->replyWithChatAction(['action' => Actions::TYPING]);

        $query = trim(rawurlencode($arguments));
        $url = sprintf('http://ajax.googleapis.com/ajax/services/search/web?v=1.0&q=%s', $query);
        $body = Helpers::urlGetContents($url);
        $json = json_decode($body);

        if (!$json) {
            $this->reply('No results found!');
            return;
        }

        if (!$json->responseData || !count($json->responseData->results)) {
            if ($json->responseDetails) {
                $this->reply($json->responseDetails);
                return;
            }
            $this->reply('No results found!');
            return;
        }

        $cursor = $json->responseData->cursor;
        $response = sprintf(
            'About %s results (%.2f seconds)' . PHP_EOL . PHP_EOL,
            $cursor->resultCount,
            $cursor->searchResultTime
        );

        $results = collect($json->responseData->results)->slice(0, 5);
        foreach ($results->all() as $result) {
            $response .= sprintf('<b>%s.</b> <a href="%s">%s</a>' . PHP_EOL,
                $results->search($result) + 1,
                Helpers::escapeMarkdown($result->url),
                Helpers::escapeMarkdown($result->titleNoFormatting));
        }

        $this->reply($response, ['parse_mode' => 'HTML', 'disable_web_page_preview' => true]);
    }
}
