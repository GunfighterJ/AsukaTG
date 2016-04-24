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

class SpurdoCommand extends BaseCommand
{
    protected $description = 'Spurdos whatever input you send.';
    protected $name = 'spurdo';

    const SPURDO_API = 'https://spurdo.pste.pw/api';

    public function handle($arguments)
    {
        $message = $this->getUpdate()->getMessage();
        // Detect a reply and spurdo it
        $replyToSpurdo = $message->getReplyToMessage();
        if (!$replyToSpurdo) {
            if ($arguments) {
                $toSpurdo = strtolower($arguments);
            } else {
                $this->reply('Please supply me with either a reply, or some text.');

                return;
            }
        } else {
            $messageType = $this->getTelegram()->detectMessageType($replyToSpurdo);
            if ($messageType != 'text') {
                $this->reply(sprintf('I cannot spurdo %s messages, please send me a text message.', $messageType));

                return;
            }

            $toSpurdo = strtolower($replyToSpurdo->getText());
        }

        $apiRequest = sprintf('%s?text=%s', self::SPURDO_API, urlencode(utf8_encode($toSpurdo)));
        $json = json_decode(Helpers::urlGetContents($apiRequest));

        if ($json->status === 0) {
            $response = $json->text;
        } else {
            $response = $json->error;
        }

        $params = ['disable_web_page_preview' => true];
        if ($replyToSpurdo) {
            $params['reply_to_message_id'] = $replyToSpurdo->getMessageId();
        }

        $this->reply($response, $params);
    }
}
