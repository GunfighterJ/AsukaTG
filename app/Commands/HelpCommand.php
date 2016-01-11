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

class HelpCommand extends BaseCommand
{
    protected $description = 'Displays a list of bot commands.';
    protected $name = 'help';

    public function handle($arguments)
    {
        // Prevent the bot SDK from running this of it's own accord
        $message = $this->getUpdate()->getMessage()->getText();
        if (!starts_with($message, ['/help', '/start'])) {
            return;
        }

        $response = '';
        $commands = $this->getTelegram()->getCommands();
        foreach ($commands as $name => $command) {
            $response .= sprintf('/%s - %s' . PHP_EOL, $name, $command->getDescription());
        }

        $response .= PHP_EOL . 'My source code can be found at https://github.com/TheReverend403/AsukaTG';
        $this->reply($response);
    }
}

class StartCommand extends BaseCommand
{
    protected $description = 'Displays a list of bot commands.';
    protected $name = 'start';

    public function handle($arguments)
    {
        $this->triggerCommand('help');
    }
}
