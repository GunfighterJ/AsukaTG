<?php

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

function reply($response, $params = [], $chatId, $replyTo = null)
{
    if ($replyTo) {
        $params['reply_to_message_id'] = $replyTo;
    }

    $params = array_merge([
        'text' => $response,
        'chat_id' => $chatId
    ], $params);

    app('telegram')->bot()->sendMessage($params);
}

function escapeMarkdown($string)
{
    return $string;
    //return preg_replace('/([*_])/i', '\\\\$1', $string);
}