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

use Telegram\Bot\Commands\Command;

class DecideCommand extends Command
{
    const RESULT_NO = 0;
    const RESULT_MAYBE = 1;
    const RESULT_YES = 2;

    protected $name = "decide";
    protected $description = "Decides between a set of choices.";

    protected $resultMap = [
        self::RESULT_NO    => 'No.',
        self::RESULT_MAYBE => 'Maybe.',
        self::RESULT_YES   => 'Yes.'
    ];

    public function handle($arguments)
    {
        $badArgsResponse = 'Please supply at least 1 choice.' . PHP_EOL;
        $badArgsResponse .= 'Example: /decide Eat cookies?' . PHP_EOL;
        $badArgsResponse .= 'Example: /decide Cookies | Cake' . PHP_EOL;
        $badArgsResponse .= 'Example: /decide Cookies or Cake' . PHP_EOL;
        $badArgsResponse .= 'Example: /decide Cookies, Cake, Pie';

        if (empty($arguments)) {
            $this->reply($badArgsResponse);

            return;
        }

        $delim = null;
        if (str_contains($arguments, ' or ')) {
            $delim = ' or ';
        } elseif (str_contains($arguments, '|')) {
            $delim = '|';
        } elseif (str_contains($arguments, ',')) {
            $delim = ',';
        }

        $result = mt_rand(self::RESULT_NO, self::RESULT_YES);
        $yesNoResponse = $this->resultMap[$result];

        if (is_null($delim)) {
            $this->reply($yesNoResponse);

            return;
        }

        if (empty(trim(str_replace($delim, '', $arguments)))) {
            $this->reply($badArgsResponse);

            return;
        }

        $choices = array_map('trim', explode($delim, $arguments));
        if (count(array_filter($choices)) < 2) {
            $this->reply($yesNoResponse);

            return;
        }

        $this->reply($choices[array_rand($choices)]);
    }

    private function reply($response)
    {
        $this->replyWithMessage($response, true, $this->getUpdate()->getMessage()->getMessageId(), null);
    }
}
