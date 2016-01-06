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

    /**
     * @param $url
     * @return mixed
     */
    function curl_get_contents($url)
    {
        $curlOpts = [
            CURLOPT_URL            => $url,
            CURLOPT_HEADER         => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'AsukaTG (https://github.com/TheReverend403/AsukaTG)',
            CURLOPT_MAXREDIRS      => 5,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $curlOpts);
        $output = curl_exec($ch);

        if (!$output && curl_errno($ch)) {
            $this->reply(curl_error($ch));
            die();
        }
        
        curl_close($ch);

        return $output;
    }

    protected function createOrUpdateUser(User $user)
    {
        $userId = $user->getId();
        $firstName = $user->getFirstName();
        $lastName = $user->getLastName() ? $user->getLastName() : null;
        $username = $user->getUsername() ? $user->getUsername() : null;

        $values = [
            'user_id'    => $userId,
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'username'   => $username
        ];

        // SQLite doesn't support ON DUPLICATE KEY UPDATE
        if ($this->getDatabase()->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME) == 'sqlite') {
            // Disable error reporting for insertInto because FluentPDO's ignore() causes a syntax error
            $this->getDatabase()->getPdo()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            if (!$this->getDatabase()->insertInto('users', $values)->execute()) {
                $this->getDatabase()->getPdo()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                $this->getDatabase()->update('users')->set($values)->where('user_id', $userId)->execute();
            }
        } else {
            $this->getDatabase()->insertInto('users', $values)->onDuplicateKeyUpdate($values);
        }
    }

    /**
     * @return FluentPDO
     */
    public function getDatabase()
    {
        if (!$this->database) {
            if (!file_exists($this->databasePath)) {
                $this->reply('Bot database doesn\'t exist!');
                die();
            }

            try {
                $this->database = new FluentPDO(new PDO('sqlite:' . $this->databasePath));
                $this->database->getPdo()->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
                $this->getDatabase()->getPdo()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            } catch (\PDOException $exception) {
                $this->reply($exception->getMessage());
                die();
            }
        }

        return $this->database;
    }

    /**
     * @param $response
     * @param array $extraParams
     */
    protected function reply($response, $extraParams = [])
    {
        $extraParams = array_merge([
            'text'                => $response,
            'reply_to_message_id' => $this->getUpdate()->getMessage()->getMessageId(),
        ], $extraParams);

        $this->replyWithMessage($extraParams);
    }

    /**
     * @param $userId
     * @return mixed
     */
    protected function getUserById($userId)
    {
        $getUserStmnt = $this->getDatabase()->from('users')->where('user_id', $userId)->limit(1);
        if (!$getUserStmnt->execute()) {
            $this->reply($this->getDatabase()->getPdo()->errorInfo()[2]);
            die();
        }

        return $getUserStmnt->fetch();
    }


    protected function escapeMarkdown($string)
    {
        return $string;
        //return preg_replace('/([*_])/i', '\\\\$1', $string);
    }
}
