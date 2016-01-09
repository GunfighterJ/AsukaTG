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

class RollCommand extends BaseCommand
{
    const DICE_MAX_AMOUNT = 128;
    const DIE_MAX_FACES = 120;
    const DIE_MIN_FACES = 4;

    protected $description = 'Rolls some dice.';
    protected $name = 'roll';

    public function handle($arguments)
    {
        // A default response for when the user is an idiot.
        $badArgsResponse = implode(PHP_EOL, [
            'Please specify the amount and type of dice to roll.',
            'Command must be formatted as /roll <1-128>d<1-120>',
            'Example: /roll 3d6',
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
            $this->reply(sprintf('Amount of dice must be between 1 and %d (inclusive).', self::DICE_MAX_AMOUNT));

            return;
        }

        if ($diceType < self::DIE_MIN_FACES || $diceType > self::DIE_MAX_FACES) {
            $this->reply(sprintf('Die type must be between %d and %d (inclusive).' . PHP_EOL, self::DIE_MIN_FACES, self::DIE_MAX_FACES));

            return;
        }

        if ($diceCount == 1) {
            $this->reply(Helpers::getRandomInt(1, $diceType));
            return;
        }

        // Create an array of $diceCount $diceType-sided dice.
        $diceArray = [];
        while (count($diceArray) < $diceCount) {
            array_push($diceArray, Helpers::getRandomInt(1, $diceType));
        }

        $response = sprintf('[%s] = %d', implode(', ', $diceArray), array_sum($diceArray));
        $this->reply($response);
    }
}
