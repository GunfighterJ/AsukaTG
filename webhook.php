<?php
require 'vendor/autoload.php';
require 'config.php';

use Telegram\Bot\Api;
use Asuka\Commands;

$async = true;
$telegram = new Api($api_key, $async);

if (array_key_exists('setwebhook', $_GET)) {
    $response = $telegram->setWebhook($webhook_url);
    echo $response->getBody();
    return;
}

$telegram->addCommand(Asuka\Commands\HelpCommand::class);
$telegram->addCommand(Asuka\Commands\EchoCommand::class);
$telegram->addCommand(Asuka\Commands\GoogleCommand::class);
$telegram->commandsHandler(true);
