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

namespace App\Commands;

use Telegram\Bot\Actions;

class ImdbCommand extends BaseCommand
{
    protected $description = 'Returns the first IMDb result for a set of search terms.';

    protected $name = 'imdb';

    public function handle($arguments)
    {
        if (empty($arguments)) {
            $badArgsResponse = implode(PHP_EOL, [
                'Please supply some search terms.',
                'Example: /imdb Hitchhikers Guide to the Galaxy',
            ]);
            $this->reply($badArgsResponse);

            return;
        }

        $this->replyWithChatAction(['action' => Actions::TYPING]);

        $query = trim(urlencode($arguments));
        $searchJson = curl_get_contents(sprintf('http://www.omdbapi.com/?s=%s&r=json&type=movie', $query));
        $searchResults = json_decode($searchJson, true);

        if (is_null($searchResults)) {
            $this->reply('No results found!');

            return;
        }

        if ($searchResults['Error']) {
            $this->reply($searchResults['Error']);

            return;
        }

        $json = curl_get_contents(sprintf('http://www.omdbapi.com/?i=%s&r=json&type=movie&plot=full', $searchResults['Search'][0]['imdbID']));
        $result = json_decode($json, true);

        $response = implode(PHP_EOL, [
            sprintf('URL: http://www.imdb.com/title/%s', $result['imdbID']),
            sprintf('Title: %s', $result['Title']),
            sprintf('Year: %s', $result['Year']),
            sprintf('Genre: %s', $result['Genre']),
            sprintf('IMDb Score: %s/10', $result['imdbRating']),
            sprintf('Runtime: %s', $result['Runtime']),
            sprintf('Rating: %s', $result['Rated']),
            sprintf('Stars: %s', $result['Actors']),
            sprintf('Director: %s', $result['Director']),
            PHP_EOL . trim($result['Plot']),
        ]);

        $this->reply($response, ['disable_web_page_preview' => true]);
    }
}
