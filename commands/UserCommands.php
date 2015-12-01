<?php

namespace Asuka\Commands;

use Telegram\Bot\Commands\Command;
use Asuka\Models\Asuka;

class DesktopCommand extends Command
{
    protected $name = "dtop";
    protected $description = "Sets/queries user desktop information.";

    function __construct(Asuka $bot)
    {
        $this->bot = $bot;
    }

    public function handle($arguments)
    {
        $user = $this->getUpdate()->getMessage()->getFrom();
        $this->bot->getDatabase()->insert("users", [
            "id" => $user->getId(),
            "first_name" => $user->getFirstName(),
            "last_name" => $user->getLastName(),
            "username" => $user->getUsername()
        ]);
        $this->reply('Not implemented yet!');
    }

    private function reply($response)
    {
        $this->replyWithMessage($response, true, $this->getUpdate()->getMessage()->getMessageId(), null);
    }
}