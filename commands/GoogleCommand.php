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

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class GoogleCommand extends Command
{
    protected $name = "g";
    protected $description = "Returns the first Google result for search terms.";

    public function handle($arguments)
    {
        if (empty($arguments)) {
            $this->replyWithMessage('Search terms cannot be empty!');
            return;
        }

        $this->replyWithChatAction(Actions::TYPING);

        $query = urlencode($arguments);
        $url = "http://ajax.googleapis.com/ajax/services/search/web?v=1.0&q=".$query;
        $body = file_get_contents($url);
        $json = json_decode($body);

        $response = $json->responseData->results[0]->unescapedUrl;

        $this->replyWithMessage($response, false, $this->getUpdate()->getMessage()->getMessageId(), null);
    }
}
