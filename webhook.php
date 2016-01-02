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
$async = $config->telegram->async_requests;

$telegram = new Api($apiKey, $async);

if (php_sapi_name() == 'cli') {
    $flags = [
        'set'  => [
            '-s', '--set'
        ],
        'del'  => [
            '-d', '--delete', '--del'
        ],
        'help' => [
            '-h', '--help'
        ],
    ];

    function printHelp() {
        $response = sprintf('Usage: php %s [options]' . PHP_EOL, $argv[0]);
        $response .= implode(PHP_EOL, [
            implode(', ', $this->flags['set']) . ' - Set the webhook URL.',
            implode(', ', $this->flags['del']) . ' - Remove the webhook URL.',
            implode(', ', $this->flags['help']) . ' - Show this help message.',
        ]);
        echo $response;

        return;
    }

    if ($argc == 1 || in_array($argv[1], $flags['help'])) {
        printHelp();
    }

    foreach ($flags as $flag) {
        if (!in_array($argv[1], $flag)) {
            printHelp();
        }
    }

    if (in_array($argv[1], $flags['set'])) {
        $webhookUrl = $config->telegram->webhook_url;
        $telegram->setWebhook(['url' => $webhookUrl]);
        echo sprintf("Webhook set to %s" . PHP_EOL, $webhookUrl);
    }

    if (in_array($argv[1], $flags['del'])) {
        $telegram->removeWebhook();
        echo "Webhook removed.";
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
    Commands\EchoCommand::class
]);

$telegram->commandsHandler(true);
