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

class SpurdoCommand extends BaseCommand
{
    const SPURDO_REPLACEMENTS = [
        'wh'   => 'w',
        'th'   => 'd',
        'af'   => 'ab',
        'ap'   => 'ab',
        'ca'   => 'ga',
        'ck'   => 'gg',
        'co'   => 'go',
        'ev'   => 'eb',
        'ex'   => 'egz',
        'et'   => 'ed',
        'iv'   => 'ib',
        'it'   => 'id',
        'ke'   => 'ge',
        'nt'   => 'nd',
        'op'   => 'ob',
        'ot'   => 'od',
        'po'   => 'bo',
        'pe'   => 'be',
        'pi'   => 'bi',
        'up'   => 'ub',
        'va'   => 'ba',
        'cr'   => 'gr',
        'kn'   => 'gn',
        'lt'   => 'ld',
        'mm'   => 'm',
        'pr'   => 'br',
        'ts'   => 'dz',
        'tr'   => 'dr',
        'bs'   => 'bz',
        'ds'   => 'dz',
        'es'   => 'es',
        'fs'   => 'fz',
        'gs'   => 'gz',
        ' is'  => ' iz',
        'ls'   => 'lz',
        'ms'   => 'mz',
        'ns'   => 'nz',
        'rs'   => 'rz',
        'ss'   => 'sz',
        'us'   => 'uz',
        'ws'   => 'wz',
        'ys'   => 'yz',
        'alk'  => 'olk',
        'ing'  => 'ign',
        'ic'   => 'ig',
        'ng'   => 'nk',
        'kek'  => 'geg',
        'epic' => 'ebin',
        'some' => 'sum',
        'meme' => 'maymay',
    ];

    const EBIN_FACES = [':D', ':DD', ':DDD', ':-D', 'D', 'XXD', 'XDD', 'XXDD', 'xD', 'xDD', ':dd'];

    protected $description = 'Spurdos whatever input you send.';
    protected $name = 'spurdo';

    public function handle($arguments)
    {
        $message = $this->getUpdate()->getMessage();
        // Detect a reply and spurdo it
        $replyToSpurdo = $message->getReplyToMessage();
        if (!$replyToSpurdo) {
            if ($arguments) {
                $response = strtolower($arguments);
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

            $response = strtolower($replyToSpurdo->getText());
        }

        foreach (self::SPURDO_REPLACEMENTS as $match => $replacement) {
            $response = str_replace($match, $replacement, $response);
        }

        while (preg_match('/\.|,(?=\s|$|\.)/m', $response)) {
            $response = preg_replace('/\.|,(?=\s|$|\.)/m', sprintf(' %s', self::EBIN_FACES[array_rand(self::EBIN_FACES)]), $response, 1);
        }

        foreach (self::EBIN_FACES as $ebinFace) {
            if (str_contains($response, $ebinFace)) {
                $response .= sprintf(' %s', self::EBIN_FACES[array_rand(self::EBIN_FACES)]);
                break;
            }
        }

        $params = ['disable_web_page_preview' => true];
        if ($replyToSpurdo) {
            $params['reply_to_message_id'] = $replyToSpurdo->getMessageId();
        }
        
        $this->reply($response, $params);
    }
}
