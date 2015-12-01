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

use Telegram\Bot\Api;
use Asuka\Commands;

$config = parse_ini_file('config.ini', true);
$telegram_config = $config['telegram'];

$api_key = $telegram_config['api_key'];
$webhook_url = $telegram_config['webhook_url'];
$async = $telegram_config['async_requests'];;

$telegram = new Api($api_key, $async);

if (array_key_exists('setwebhook', $_GET)) {
    $response = $telegram->setWebhook($webhook_url);
    echo $response;
    return;
}

$telegram->addCommands([
    Asuka\Commands\HelpCommand::class,
    Asuka\Commands\EchoCommand::class,
    Asuka\Commands\GoogleCommand::class,
    Asuka\Commands\ImdbCommand::class,
    Asuka\Commands\BotsCommand::class,
    Asuka\Commands\StartCommand::class,
]);
$telegram->commandsHandler(true);
