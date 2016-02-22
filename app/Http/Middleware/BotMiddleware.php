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

namespace Asuka\Http\Middleware;

use Asuka\Http\Helpers;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BotMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $botKey = null;
        if (isset($request->route()[2]['botKey'])) {
            $botKey = $request->route()[2]['botKey'];
        }

        $telegram = app('telegram');
        if (!$botKey || $botKey != $telegram->getBotConfig(config('telegram.default'))['token']) {
            throw new NotFoundHttpException;
        }

        if ($request->getMethod() != Request::METHOD_POST) {
            $ownerId = $telegram->getBotConfig(config('telegram.default'))['owner_id'];
            if ($ownerId) {
                Helpers::sendMessage(
                    sprintf(
                        'The IP %s just accessed %s',
                        $request->getClientIp(), $request->url()
                    ), $ownerId
                );
            }
        }

        return $next($request);
    }
}
