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
    const DICE_MAX_AMOUNT = 128;
    const DICE_MAX_FACES = 120;
    protected $name = "roll";
    protected $description = "Rolls some dice.";

    public function handle($arguments)
    {
        // A default response for when the user is an idiot.
        $badArgsResponse = implode(PHP_EOL, [
            'Please specify the amount and type of dice to roll.',
            'Command must be formatted as /roll <1-128>d<1-120>',
            'Example: /roll 3d6'
        ]);

        if (empty($arguments)) {
            $this->reply($badArgsResponse);

            return;
        }

        // {{{ Parse XdY where X is the amount of dice and Y is the type.
        $diceParam = explode('d', strtolower($arguments));
        if (count($diceParam) != 2) {
            $this->reply($badArgsResponse);

            return;
        }

        $diceCount = intval($diceParam[0]);
        $diceType = intval($diceParam[1]);
        // }}}

        if ($diceCount < 1 || $diceCount > self::DICE_MAX_AMOUNT) {
            $this->reply(sprintf("Amount of dice must be between 1 and %s (inclusive).", self::DICE_MAX_AMOUNT));

            return;
        }

        if ($diceType < 4 || $diceType > self::DICE_MAX_FACES) {
            $this->reply(sprintf('Die type must be between 4 and %s (inclusive).' . PHP_EOL, self::DICE_MAX_FACES));

            return;
        }

        // Create an array of $diceCount $diceType-sided dice.
        $diceArray = [];
        while (count($diceArray) < $diceCount) {
            array_push($diceArray, mt_rand(1, $diceType));
        }

        $response = sprintf('[%s] = %d', implode(', ', $diceArray), array_sum($diceArray));
        $this->reply($response);
    }

    private function reply($response)
    {
        $this->replyWithMessage($response, true, $this->getUpdate()->getMessage()->getMessageId(), null);
    }
}
