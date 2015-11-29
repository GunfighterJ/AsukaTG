<?php

namespace Asuka\Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class HelpCommand extends Command
{
    protected $name = "help";
    protected $description = "Displays commands";

    public function handle($arguments)
    {
        $this->replyWithChatAction(Actions::TYPING);
        $commands = $this->getTelegram()->getCommands();

        $response = '';
        foreach ($commands as $name => $command) {
            $response .= sprintf('/%s - %s' . PHP_EOL, $name, $command->getDescription());
        }

        $this->replyWithMessage($response);
    }
}
