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

class DecideCommand extends BaseCommand
{
    protected $choiceDelimiters = [
        ' or ', '|', ',', '/', '\\',
    ];

    protected $description = 'Decides between a set of choices.';

    protected $name = 'decide';

    protected $singleChoiceResults = [
        'No.', 'Probably not.', 'Maybe.',
        'Probably.', 'Undecided, ask me again later.', 'Yes.',
    ];

    public function handle($arguments)
    {
        $badArgsResponse = implode(PHP_EOL, [
            'Please supply at least 1 choice.',
            'Example: /decide Eat cookies?',
            'Example: /decide Cookies | Cake',
            'Example: /decide Cookies or Cake',
            'Example: /decide Cookies, Cake, Pie',
        ]);

        if (empty($arguments)) {
            $this->reply($badArgsResponse);

            return;
        }

        // Look for any delimiter from $choiceDelimiters and use that as the delimiter for the rest of string,
        // kind of like sed.
        $choiceDelimiter = null;
        foreach ($this->choiceDelimiters as $delimiter) {
            if (str_contains($arguments, $delimiter)) {
                $choiceDelimiter = $delimiter;
                break;
            }
        }

        $singleChoiceResponse = $this->singleChoiceResults[array_rand($this->singleChoiceResults)];

        // No delimiters found in string, assume it's a single choice message.
        if (is_null($choiceDelimiter)) {
            $this->reply($singleChoiceResponse);

            return;
        }

        // Remove the delimiters from the string and then check if it's empty.
        // This should indicate whether or not the string is purely comprised of delimiters and nothing else.
        if (empty(trim(str_replace($choiceDelimiter, '', $arguments)))) {
            $this->reply($badArgsResponse);

            return;
        }

        // Run trim() on all choices and then remove any empty elements from the resulting array.
        // Handles input like "| choice1 || choice2" correctly.
        $choices = array_filter(array_map('trim', explode($choiceDelimiter, $arguments)));
        if (count($choices) < 2) {
            $this->reply($singleChoiceResponse);

            return;
        }

        if (count($choices) == 2) {
            array_push($choices, 'Neither.');
        }

        $this->reply($choices[array_rand($choices)], ['disable_web_page_preview' => true]);
    }
}
