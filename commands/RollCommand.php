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

class RollCommand extends Command
{
    protected $name = "roll";
    protected $description = "Roll some dice.";

    public function handle($arguments)
    {
        // A default response for when the user is an idiot.
        $response = 'Please specify the amount and type of dice to roll.' . PHP_EOL;
        $response .= 'Example: /roll 3d6';

        if (empty($arguments)) {
            $this->reply($response);

            return;
        }

        $diceParam = explode('d', strtolower($arguments));
        if (count($diceParam) != 2) {
            $this->reply($response);

            return;
        }

        $diceCount = intval($diceParam[0]);
        $diceType = intval($diceParam[1]);

        if (!$diceCount || !$diceType) {
            $this->reply($response);

            return;
        }

        if ($diceCount < 1 || $diceCount > 128) {
            $this->reply("Amount of dice must be between 1 and 128 (inclusive).");

            return;
        }

        if ($diceType < 1 || $diceType > 120) {
            $response = 'Die type must be between 1 and 120 (inclusive).';
            $response .= "https://en.wikipedia.org/wiki/Dice#Standard_variations";
            $this->reply($response);

            return;
        }

        $response = '';
        for ($i = 0; $i < $diceCount; $i++) {
            $response .= sprintf('%s, ', mt_rand(1, $diceType));
        }

        $this->reply(rtrim($response, ', '));
    }

    private function reply($response)
    {
        $this->replyWithMessage($response, true, $this->getUpdate()->getMessage()->getMessageId(), null);
    }
}
