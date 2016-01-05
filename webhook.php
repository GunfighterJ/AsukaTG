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

function printHelp()
{
    global $flags;
    global $argv;

    $response = sprintf('Usage: php %s [option]' . PHP_EOL, $argv[0]);
    $response .= PHP_EOL;

    $response .= 'Options: ' . PHP_EOL;

    foreach ($flags as $flagArray) {
        $response .= sprintf('%s : %s' . PHP_EOL, implode(', ', $flagArray['flags']), $flagArray['description']);
    }
    echo $response . PHP_EOL;

    exit;
}

// If the config isn't set up, just let Telegram think we're all good so it doesn't keep retrying updates.
if (!file_exists('config.ini')) {
    http_response_code(200);
    echo sprintf('Config not found, please copy config.ini.dist to config.ini in %s', realpath(__DIR__));

    return;
}

// Convert config.ini to an object
$config = parse_ini_file('config.ini', true);
$config = json_encode($config);
$config = json_decode($config);

$apiKey = $config->telegram->api_key;
$async  = $config->telegram->async_requests;

$telegram = new Api($apiKey, $async);

if (php_sapi_name() == 'cli') {
    $flags = [
        'set'  => [
            'flags'       => [
                '-s', '--set',
            ],
            'description' => 'Set the webhook URL.',
        ],
        'del'  => [
            'flags'       => [
                '-d', '--delete', '--del',
            ],
            'description' => 'Remove the webhook URL.',
        ],
        'help' => [
            'flags'       => [
                '-h', '--help',
            ],
            'description' => 'Show this help message.',
        ],
    ];

    if (1 == $argc || in_array($argv[1], $flags['help'])) {
        printHelp();
    }

    $validArg = false;
    foreach ($flags as $flagArray) {
        if (in_array($argv[1], $flagArray['flags'])) {
            $validArg = true;
        }
    }

    if (!$validArg) {
        printHelp();
    }

    if (in_array($argv[1], $flags['set'])) {
        $webhookUrl = $config->telegram->webhook_url;
        $telegram->setWebhook(['url' => $webhookUrl]);
        echo sprintf('Webhook set to %s' . PHP_EOL, $webhookUrl);
    }

    if (in_array($argv[1], $flags['del'])) {
        $telegram->removeWebhook();
        echo 'Webhook removed.' . PHP_EOL;
    }

    return;
}

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
    Commands\EchoCommand::class,
    Commands\QuoteCommand::class,
    Commands\AddQuoteCommand::class,
]);

$telegram->commandsHandler(true);
