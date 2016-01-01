<?php
/*
 * This file is part of AsukaTG.
 *
 * AsukaTG is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * AsukaTG is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AsukaTG.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Asuka\Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class HelpCommand extends Command
{
    protected $name = "?";
    protected $description = "Displays a list of bot commands.";

    public function handle($arguments)
    {
        $this->replyWithChatAction(Actions::TYPING);
        $commands = $this->getTelegram()->getCommands();

        $response = '';
        foreach ($commands as $name => $command) {
            $response .= sprintf('/%s - %s' . PHP_EOL, $name, $command->getDescription());
        }

        $response .= PHP_EOL . 'My source code can be found at https://github.com/TheReverend403/AsukaTG';
        $this->reply($response);
    }

    private function reply($response)
    {
        $this->replyWithMessage([
            'text' => $response,
            'disable_web_page_preview' => true,
            'reply_to_message_id' => $this->getUpdate()->getMessage()->getMessageId()
        ]);
    }
}
