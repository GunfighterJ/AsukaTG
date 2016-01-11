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

use Asuka\Http\AsukaDB;
use Asuka\Http\Helpers;

class IgnoreCommand extends BaseCommand
{
    protected $description = 'Ignores a user (admin only).';
    protected $name = 'ignore';

    public function handle($arguments)
    {
        $message = $this->getUpdate()->getMessage();
        if (!Helpers::userIsOwner($message->getFrom())) {
            $this->reply('You do not have permission to use this command.');
            return;
        }

        if (!$message->getReplyToMessage()) {
            $this->reply('Please send me a reply from the user you wish to ignore.');
            return;
        }

        $userToIgnore = $message->getReplyToMessage()->getFrom();

        if (Helpers::userIsMe($userToIgnore) || Helpers::userIsOwner($userToIgnore)) {
            $this->reply('You cannot ignore that user.');
            return;
        }

        AsukaDB::updateUserIgnore($userToIgnore);
        $this->reply(sprintf('Ignored %s'),
            $userToIgnore->getUsername() ? $userToIgnore->getUsername() : $userToIgnore->getFirstName());
    }
}

class UnignoreCommand extends BaseCommand
{
    protected $description = 'Unignores a user (admin only).';
    protected $name = 'unignore';

    public function handle($arguments)
    {
        $message = $this->getUpdate()->getMessage();
        if (!Helpers::userIsOwner($message->getFrom())) {
            $this->reply('You do not have permission to use this command.');
            return;
        }

        if (!$message->getReplyToMessage()) {
            $this->reply('Please send me a reply from the user you wish to unignore.');
            return;
        }

        $userToUnignore = $message->getReplyToMessage()->getFrom();

        if (Helpers::userIsMe($userToUnignore) || Helpers::userIsOwner($userToUnignore)) {
            $this->reply('You cannot ignore that user.');
            return;
        }

        AsukaDB::updateUserIgnore($userToUnignore, false);
        $this->reply(sprintf('Unignored %s'),
            $userToUnignore->getUsername() ? $userToUnignore->getUsername() : $userToUnignore->getFirstName());
    }
}
