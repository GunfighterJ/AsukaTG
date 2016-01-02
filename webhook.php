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

require 'vendor/autoload.php';

use Asuka\Commands;
use Telegram\Bot\Api;

$config = parse_ini_file('config.ini', true);
$telegramConfig = $config['telegram'];
$apiKey = $telegramConfig['api_key'];
$async = $telegramConfig['async_requests'];
$telegram = new Api($apiKey, $async);

$telegram->addCommands([
    Commands\HelpCommand::class,
    Commands\StartCommand::class,
    Commands\BotsCommand::class,
    Commands\RollCommand::class,
    Commands\CoinCommand::class,
    Commands\UptimeCommand::class,
    Commands\ImdbCommand::class,
    Commands\GoogleCommand::class,
    Commands\DecideCommand::class,
    Commands\EchoCommand::class
]);

$telegram->commandsHandler(true);
