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

    if (!$output) {
        $update = app('telegram')->bot()->getWebhookUpdates();
        sendMessage(curl_error($ch), $update->getMessage()->getChat()->getId(), $update->getMessage()->getMessageId());
        app()->abort(200);
        return null;
    }

    curl_close($ch);

    return $output;
}

function sendMessage($response, $chatId, $replyTo = null, $params = [])
{
    if ($replyTo) {
        $params['reply_to_message_id'] = $replyTo;
    }

    $params['chat_id'] = $chatId;
    $params['text'] = $response;

    app('telegram')->bot()->sendMessage($params);
}

function escapeMarkdown($string)
{
    return $string;
    //return preg_replace('/([*_])/i', '\\\\$1', $string);
}