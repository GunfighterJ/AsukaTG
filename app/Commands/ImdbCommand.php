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

class ImdbCommand extends BaseCommand
{
    const OMDB_API_ENDPOINT = 'http://www.omdbapi.com';

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

        $query = trim(rawurlencode($arguments));
        $json = Helpers::curlGetContents(sprintf('%s/?t=%s&r=json&type=movie', self::OMDB_API_ENDPOINT, $query));
        $results = json_decode($json, true);

        if (!$results) {
            $this->reply('No results found!');

            return;
        }

        // Exact title match failed, fall back to search
        if (array_key_exists('Error', $results)) {
            $json = Helpers::curlGetContents(sprintf('%s/?s=%s&r=json&type=movie', self::OMDB_API_ENDPOINT, $query));
            $results = json_decode($json, true);

            if (!$results) {
                $this->reply('No results found!');

                return;
            }

            if (array_key_exists('Error', $results)) {
                $this->reply($results['Error']);

                return;
            }

            $json = Helpers::curlGetContents(sprintf('%s/?i=%s&r=json&type=movie&plot=full', self::OMDB_API_ENDPOINT, $results['Search'][0]['imdbID']));
            $results = json_decode($json, true);
        }

        $response = implode(PHP_EOL, [
            sprintf('URL: http://www.imdb.com/title/%s', $results['imdbID']),
            sprintf('Title: %s', $results['Title']),
            sprintf('Year: %d', $results['Year']),
            sprintf('Genre: %s', $results['Genre']),
            sprintf('IMDb Score: %.1f/10', $results['imdbRating']),
            sprintf('Runtime: %s', $results['Runtime']),
            sprintf('Rating: %s', $results['Rated']),
            sprintf('Stars: %s', $results['Actors']),
            sprintf('Director: %s', $results['Director']),
            PHP_EOL . trim($results['Plot']),
        ]);

        $this->reply($response, ['disable_web_page_preview' => true]);
    }
}
