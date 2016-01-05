<?php


namespace Asuka\commands;

use PDO;
use Telegram\Bot\Commands\Command;

class BaseCommand extends Command
{
    private $database;

    public function __construct()
    {
        $this->dataPath = realpath(__DIR__) . '/../data';
        $this->databasePath = $this->dataPath . '/asuka.db';
    }

    /**
     * {@inheritdoc}
     */
    public function handle($arguments)
    {
        parent::handle($arguments);
    }

    /**
     * @return null|PDO
     */
    public function getDatabase()
    {
        if (!$this->database) {
            if (!file_exists($this->databasePath)) {
                $this->reply('Bot database doesn\'t exist!');

                return null;
            }

            try {
                $this->database = new PDO('sqlite:' . $this->databasePath);
            } catch (\PDOException $exception) {
                $this->reply($exception->getMessage());

                return null;
            }

            $this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        }

        return $this->database;
    }

    /**
     * @param $response String to send as a reply
     * @param array $params Extra parameters to sendMessage
     */
    protected function reply($response, $params = [])
    {
        $params = array_merge([
            'text'                => $response,
            'reply_to_message_id' => $this->getUpdate()->getMessage()->getMessageId(),
        ], $params);

        $this->replyWithMessage($params);
    }
}