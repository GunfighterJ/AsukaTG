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

use Uptime\System;

class UptimeCommand extends BaseCommand
{
    protected $description = 'Displays the current system uptime.';
    protected $name = 'uptime';

    public function handle($arguments)
    {
        $system = new System();
        $uptime = $system->getUptime();

        $response = implode(', ', [
            sprintf(ngettext('%d day', '%d days', $uptime->d), $uptime->d),
            sprintf(ngettext('%d hour', '%d hours', $uptime->h), $uptime->h),
            sprintf(ngettext('%d minute', '%d minutes', $uptime->i), $uptime->i),
            sprintf(ngettext('%d second', '%d seconds', $uptime->s), $uptime->s),
        ]);

        $this->reply($response, ['disable_web_page_preview' => true]);
    }
}
