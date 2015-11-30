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
require 'config.php';

use Telegram\Bot\Api;
use Asuka\Commands;

$async = true;
$telegram = new Api($api_key, $async);

if (array_key_exists('setwebhook', $_GET)) {
    $response = $telegram->setWebhook($webhook_url);
    echo $response;
    return;
}

$telegram->addCommand(Asuka\Commands\HelpCommand::class);
$telegram->addCommand(Asuka\Commands\EchoCommand::class);
$telegram->addCommand(Asuka\Commands\GoogleCommand::class);
$telegram->commandsHandler(true);
