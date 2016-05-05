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
        if (!config('asuka.keys.google.api_key') || !config('asuka.keys.google.custom_search_engine_id')) {
            $this->reply('Missing custom search credentials!');
            return;
        }
        
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

        $query = trim($arguments);
        $queryParams = [
            'alt' => 'json',
            'cx' => config('asuka.keys.google.custom_search_engine_id'),
            'key' => config('asuka.keys.google.api_key'),
            'q' => $query
        ];
        $url = sprintf('https://www.googleapis.com/customsearch/v1?%s', http_build_query($queryParams));
        $body = Helpers::urlGetContents($url);
        $json = json_decode($body);

        if (!$json) {
            $this->reply('No results found!');
            return;
        }

        if (!$json->items || !count($json->items)) {
            if ($json->error) {
                $this->reply($json->error->message, ['disable_web_page_preview' => true]);
                return;
            }
            $this->reply('No results found!');
            return;
        }

        $searchInformation = $json->searchInformation;
        $response = sprintf(
            'About %s results (%.2f seconds)' . PHP_EOL,
            $searchInformation->totalResults,
            $searchInformation->searchTime
        );

        $results = collect($json->items)->slice(0, 5);
        foreach ($results->all() as $result) {
            $response .= sprintf(
                '<b>%s.</b> <a href="%s">%s</a>' . PHP_EOL,
                $results->search($result) + 1,
                Helpers::escapeMarkdown($result->link),
                Helpers::escapeMarkdown(html_entity_decode($result->title, ENT_QUOTES | ENT_HTML401))
            );
        }

        $this->reply($response, ['parse_mode' => 'HTML', 'disable_web_page_preview' => true]);
    }
}
