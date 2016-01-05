<?php

namespace Asuka\commands;

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
        if (!$this->createOrUpdateUser($this->getUpdate()->getMessage()->getFrom())) {
            return;
        };
        parent::handle($arguments);
    }

    protected function createOrUpdateUser(User $user)
    {
        $createUserStmnt = $this->getDatabase()->prepare('INSERT OR IGNORE INTO users (user_id, first_name, last_name, username) VALUES (:user_id, :first_name, :last_name, :username)');
        $createUserStmnt->bindValue(':user_id', $user->getId(), PDO::PARAM_INT);
        $createUserStmnt->bindValue(':first_name', $user->getFirstName(), PDO::PARAM_STR);
        $createUserStmnt->bindValue(':last_name', $user->getLastName() ? $user->getLastName() : null, PDO::PARAM_STR);
        $createUserStmnt->bindValue(':username', $user->getUsername() ? $user->getUsername() : null, PDO::PARAM_STR);
        return $createUserStmnt->execute();
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

            $this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
        $getUserStmnt = $this->getDatabase()->prepare('SELECT * FROM users WHERE user_id = :user_id LIMIT 1');
        $getUserStmnt->bindValue('user_id', $userId);
        $getUserStmnt->execute();
        $user = $getUserStmnt->fetch(PDO::FETCH_OBJ);

        if (!$userId) {
            return null;
        }

        return $user;
    }
}
