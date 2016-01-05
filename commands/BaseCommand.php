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
        parent::handle($arguments);
    }

    protected function createOrUpdateUser(User $user)
    {
        $createUserStmnt = $this->getDatabase()->prepare('INSERT OR IGNORE INTO users (user_id, first_name, last_name, username) VALUES (:user_id, :first_name, :last_name, :username)');
        $updateUserStmnt = $this->getDatabase()->prepare('UPDATE users SET first_name=:first_name, last_name=:last_name, username=:username WHERE user_id=:user_id');

        $createUserStmnt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $createUserStmnt->bindParam(':first_name', $firstName, PDO::PARAM_STR);
        $createUserStmnt->bindParam(':last_name', $lastName, PDO::PARAM_STR);
        $createUserStmnt->bindParam(':username', $username, PDO::PARAM_STR);

        $updateUserStmnt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $updateUserStmnt->bindParam(':first_name', $firstName, PDO::PARAM_STR);
        $updateUserStmnt->bindParam(':last_name', $lastName, PDO::PARAM_STR);
        $updateUserStmnt->bindParam(':username', $username, PDO::PARAM_STR);

        $userId = $user->getId();
        $firstName = $user->getFirstName();

        if ($user->getLastName()) {
            $lastName = $user->getLastName();
        }

        if ($user->getUsername()) {
            $username = $user->getUsername();
        }

        if (!$createUserStmnt->execute()) {
            $this->reply($createUserStmnt->errorInfo()[2]);
            return false;
        }

        if (!$updateUserStmnt->execute()) {
            $this->reply($updateUserStmnt->errorInfo()[2]);
            return false;
        }
        return true;
    }

    /**
     * @return null|PDO
     */
    public function getDatabase()
    {
        if (!$this->database) {
            if (!file_exists($this->databasePath)) {
                $this->reply('Bot database doesn\'t exist!');
                error_log('Bot database does not exist');
                return null;
            }

            try {
                $this->database = new PDO('sqlite:' . $this->databasePath);
            } catch (\PDOException $exception) {
                $this->reply($exception->getMessage());
                error_log($exception);
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
