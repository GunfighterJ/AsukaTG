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

class MorseCommand extends BaseCommand
{
    const MORSE_REPLACEMENTS = [
        'a' => '.-',
        'b' => '-...',
        'c' => '-.-.',
        'd' => '-..',
        'e' => '.',
        'f' => '..-.',
        'g' => '--.',
        'h' => '....',
        'i' => '..',
        'j' => '.---',
        'k' => '-.-',
        'l' => '.-..',
        'm' => '--',
        'n' => '-.',
        'o' => '---',
        'p' => '.--.',
        'q' => '--.-',
        'r' => '.-.',
        's' => '...',
        't' => '-',
        'u' => '..-',
        'v' => '...-',
        'w' => '.--',
        'x' => '-..-',
        'y' => '-.--',
        'z' => '--..',
        '0' => '-----',
        '1' => '.----',
        '2' => '..---',
        '3' => '...--',
        '4' => '....-',
        '5' => '.....',
        '6' => '-....',
        '7' => '--...',
        '8' => '---..',
        '9' => '----.',
        '.' => '.-.-.-',
        ',' => '--..--',
        '?' => '..--..',
        ':' => '---...',
        "'" => '.----.',
        '"' => '.-..-.',
        '-' => '-....-',
        '/' => '-..-.',
        '(' => '-.--.',
        ')' => '-.--.-',
        ' ' => '/',
    ];

    protected $description = 'Translates text to morse code.';
    protected $name = 'morse';

    public function handle($arguments)
    {
        $message = $this->getUpdate()->getMessage();
        // Detect a reply and translate it
        $replyToMorse = $message->getReplyToMessage();
        if (!$replyToMorse) {
            if ($arguments) {
                $tmpMessage = strtolower($arguments);
            } else {
                $this->reply('Please supply me with either a reply, or some text.');

                return;
            }
        } else {
            if (!$message->isType('text')) {
                $this->reply(sprintf('I cannot translate %s messages, please send me a text message.', $messageType));

                return;
            }

            $tmpMessage = strtolower($replyToMorse->getText());
        }

        $response = '';
        foreach (str_split($tmpMessage) as $symbol) {
            if (array_key_exists($symbol, self::MORSE_REPLACEMENTS)) {
                $response .= sprintf(' %s ', self::MORSE_REPLACEMENTS[$symbol]);
            }
        }

        $params = ['disable_web_page_preview' => true];
        if ($replyToMorse) {
            $params['reply_to_message_id'] = $replyToMorse->getMessageId();
        }

        $this->reply($response, $params);
    }
}
