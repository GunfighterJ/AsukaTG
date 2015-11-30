<?php
namespace Asuka\Commands;

use Telegram\Bot\Commands\Command;

class EchoCommand extends Command
{
	protected $name = "echo";
	protected $description = "Returns whatever input you send";

    public function handle($arguments)
    {
        if (empty($arguments)) {
            $this->replyWithMessage('Arguments cannot be empty!');
            return;
        }
        $this->replyWithMessage($arguments);
    }
}
