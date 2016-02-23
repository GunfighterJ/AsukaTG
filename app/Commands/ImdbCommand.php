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
    const OMDB_API_ENDPOINT = 'https://omdbapi.com';
    protected $description = 'Returns the first IMDb result for a set of search terms.';
    protected $name = 'imdb';

    public function handle($arguments)
    {
        if (empty($arguments)) {
            $badArgsResponse = implode(
                PHP_EOL,
                [
                    'Please supply some search terms.',
                    'Example: /imdb Hitchhikers Guide to the Galaxy',
                ]
            );
            $this->reply($badArgsResponse);

            return;
        }

        $this->replyWithChatAction(['action' => Actions::TYPING]);

        $queryArgs = [
            'plot' => 'full',
            't'    => trim($arguments),
            'type' => 'movie',
            'r'    => 'json'
        ];

        $json = Helpers::urlGetContents(sprintf('%s/?%s', self::OMDB_API_ENDPOINT, http_build_query($queryArgs)));
        $results = json_decode($json, true);

        if (!$results) {
            $this->reply('No results found!');

            return;
        }

        // Exact title match failed, fall back to search
        if (array_key_exists('Error', $results)) {
            $queryArgs['s'] = $queryArgs['t'];
            unset($queryArgs['t']);
            $json = Helpers::urlGetContents(sprintf('%s/?%s', self::OMDB_API_ENDPOINT, http_build_query($queryArgs)));
            $results = json_decode($json, true);

            if (!$results) {
                $this->reply('No results found!');

                return;
            }

            if (array_key_exists('Error', $results)) {
                $this->reply($results['Error']);

                return;
            }

            unset($queryArgs['s']);
            $queryArgs['i'] = $results['Search'][0]['imdbID'];
            $queryArgs['plot'] = 'full';
            $json = Helpers::urlGetContents(sprintf('%s/?%s', self::OMDB_API_ENDPOINT, http_build_query($queryArgs)));
            $results = json_decode($json, true);
        }

        $imdbInfo = [
            'URL' => 'http://www.imdb.com/title/' . $results['imdbID'],
            'Title' => $results['Title'],
            'Year' => $results['Year'],
            'Genre' => $results['Genre'],
            'IMDb Score' => $results['imdbRating'],
            'Runtime' => $results['Runtime'],
            'Rating' => $results['Rated'],
            'Actors' => $results['Actors'],
            'Director' => $results['Director']
        ];

        $response = '';
        foreach ($imdbInfo as $item => $value) {
            $response .= sprintf('<b>%s:</b> %s' . PHP_EOL, $item, Helpers::escapeMarkdown($value));
        }

        $response .= PHP_EOL . trim(Helpers::escapeMarkdown($results['Plot']));

        $this->reply($response, ['disable_web_page_preview' => true, 'parse_mode' => 'HTML']);
    }
}
