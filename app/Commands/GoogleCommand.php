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
            $badArgsResponse = implode(PHP_EOL, [
                'Please supply some search terms.',
                'Example: /g What happens if you Google Google?',
            ]);
            $this->reply($badArgsResponse);

            return;
        }

        $this->replyWithChatAction(['action' => Actions::TYPING]);

        $query = trim(rawurlencode($arguments));
        $url = sprintf('http://ajax.googleapis.com/ajax/services/search/web?v=1.0&q=%s', $query);
        $body = Helpers::curl_get_contents($url);
        $json = json_decode($body);

        $response = sprintf('About %s results (%.2f seconds)' . PHP_EOL, $json->responseData->cursor->resultCount, $json->responseData->cursor->searchResultTime);
        $response .= $json->responseData->results[0]->unescapedUrl;

        if (!$response) {
            $this->reply('No results found!');

            return;
        }

        $this->reply($response);
    }
}
