<?php
namespace Asuka\Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class EchoCommand extends Command
{
	protected $name = "echo";
	protected $description = "returns whatever input you send";

    public function handle($arguments)
    {
        if (empty($arguments)) {
            $this->replyWithMessage('Arguments cannot be empty!');
            return;
        }
        $this->replyWithMessage($arguments);
    }
}
