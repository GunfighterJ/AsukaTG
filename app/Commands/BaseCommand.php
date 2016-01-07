<?php


namespace App\Commands;


use Telegram\Bot\Commands\Command;

class BaseCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    public function handle($arguments)
    {
        parent::handle($arguments);
    }

    protected function reply($response, $params = []) {
        reply($response, $params, $this->getUpdate()->getMessage()->getChat()->getId(), $this->getUpdate()->getMessage()->getMessageId());
    }
}