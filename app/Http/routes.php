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

$app->get('quotes',
    ['as' => 'quotes', 'uses' => 'BotController@showQuotes']);

$app->get('/{botKey}',
    ['as' => 'bot', 'middleware' => 'bot', 'uses' => 'BotController@index']);

$app->post('/{botKey}/webhook',
    ['as' => 'bot.webhook', 'middleware' => 'bot', 'uses' => 'BotController@webhook']);

$app->get('/{botKey}/webhook/{action}',
    ['as' => 'bot.webhook.action', 'middleware' => 'bot', 'uses' => 'BotController@updateWebhook']);