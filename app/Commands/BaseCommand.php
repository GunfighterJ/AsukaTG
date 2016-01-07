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