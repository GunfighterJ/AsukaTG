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

use Imdb\TitleSearch;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class ImdbCommand extends Command
{
    protected $name = "imdb";
    protected $description = "Returns the first IMDb result for a set of search terms.";

    public function handle($arguments)
    {
        if (empty($arguments)) {
            $badArgsResponse = implode(PHP_EOL, [
                'Please supply some search terms.',
                'Example: /imdb Hitchhikers Guide to the Galaxy'
            ]);
            $this->reply($badArgsResponse);

            return;
        }

        $this->replyWithChatAction(['action' => Actions::TYPING]);

        $search = new TitleSearch();
        $results = $search->search($arguments, [TitleSearch::MOVIE, TitleSearch::TV_SERIES]);

        $result = $results[0];
        if (is_null($result)) {
            $this->reply('No results found!');

            return;
        }

        $response = implode(PHP_EOL, [
            sprintf("URL: %s", $result->main_url()),
            sprintf("Title: %s", $result->title()),
            sprintf("Year: %s", $result->year()),
            sprintf("Rating: %s/10", $result->rating()),
            sprintf("Top 5 Cast: %s", implode(', ', array_slice($result->cast(), 0, 5))),
            PHP_EOL . strip_tags(trim($result->plotoutline(true)))
        ]);

        $this->reply($response);
    }

    private function reply($response)
    {
        $this->replyWithMessage([
            'text'                     => $response,
            'disable_web_page_preview' => true,
            'reply_to_message_id'      => $this->getUpdate()->getMessage()->getMessageId()
        ]);
    }
}
