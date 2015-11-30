<?php
namespace Asuka\Commands;

use Telegram\Bot\Commands\Command;

class GoogleCommand extends Command
{
    protected $name = "g";
    protected $description = "Returns Google results for search terms";

    public function handle($arguments)
    {
        if (empty($arguments)) {
            $this->replyWithMessage('Search terms cannot be empty!');
            return;
        }

        $query = urlencode($arguments);
        $url = "http://ajax.googleapis.com/ajax/services/search/web?v=1.0&q=".$query;
        $body = file_get_contents($url);
        $json = json_decode($body);

        $response = $json->responseData->results[0]->unescapedUrl;

        $this->replyWithMessage($response, false, $this->getUpdate()->getMessage()->getMessageId(), null);
    }
}
