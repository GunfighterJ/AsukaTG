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

use Telegram\Bot\Commands\Command;
use Uptime\System;

class UptimeCommand extends Command
{
    protected $name = "uptime";
    protected $description = "Display the current system uptime.";

    public function handle($arguments)
    {
        $system = new System();
        $uptime = $system->getUptime();
        $this->reply(sprintf('%s days, %s hours, %s minutes, %s seconds',
            $uptime->d, $uptime->h, $uptime->m, $uptime->s));
    }

    private function reply($response)
    {
        $this->replyWithMessage($response, true, $this->getUpdate()->getMessage()->getMessageId(), null);
    }
}
