<?php

namespace Asuka\commands;

use FluentPDO;
use PDO;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Objects\User;

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

    protected function createOrUpdateUser(User $user)
    {
        $userId = $user->getId();
        $firstName = $user->getFirstName();
        $lastName = $user->getLastName() ? $user->getLastName() : null;
        $username = $user->getUsername() ? $user->getUsername() : null;

        $values = [
            'user_id' => $userId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'username' => $username
        ];

        $this->getDatabase()->insertInto('users')->values($values)->ignore()->execute();
        $this->getDatabase()->update('users')->set($values)->execute();
        return true;
    }

    /**
     * @return null|FluentPDO
     */
    public function getDatabase()
    {
        if (!$this->database) {
            if (!file_exists($this->databasePath)) {
                $this->reply('Bot database doesn\'t exist!');
                return null;
            }

            try {
                $this->database = new FluentPDO(new PDO('sqlite:' . $this->databasePath));
                $this->database->getPdo()->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            } catch (\PDOException $exception) {
                $this->reply($exception->getMessage());
                return null;
            }
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

    protected function getDBUser($userId)
    {
        $getUserStmnt = $this->getDatabase()->from('users')->where('user_id', $userId)->limit(1);
        $getUserStmnt->execute();

        if (!$getUserStmnt->execute()) {
            return null;
        }

        return $getUserStmnt->fetch();
    }
}
