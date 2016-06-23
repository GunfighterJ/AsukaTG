<?php

namespace Asuka\Commands;

use Asuka\Http\Helpers;
use DOMDocument;

class WtcCommand extends BaseCommand
{
    protected $description = 'Gets a random commit message from whatthecommit.com.';
    protected $name = 'wtc';

    public function handle($arguments)
    {
        $url = 'http://whatthecommit.com';
        $html = Helpers::urlGetContents($url);

        $dom = new DOMDocument();
        if ($dom->loadHTML($html)) {
            $commitMessage = $dom->getElementById('content')->getElementsByTagName('p')->item(0)->nodeValue;
        } else {
            $this->reply('Failed to load content from whatthecommit.com', ['disable_web_page_preview' => true]);
            return;
        }

        $this->reply($commitMessage, ['disable_web_page_preview' => true]);
    }
}