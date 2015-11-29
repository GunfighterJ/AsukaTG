<?php
require 'vendor/autoload.php';
require 'config.php';

use Telegram\Bot\Api;
use Asuka\Commands;

$telegram = new Api($api_key);

if (array_key_exists('setwebhook', $_GET)) {
    $response = $telegram->setWebhook($webhook_url);
    echo $response;
    return;
}

$telegram->addCommand(Asuka\Commands\HelpCommand::class);
$telegram->addCommand(Asuka\Commands\EchoCommand::class);
$telegram->commandsHandler(true);
