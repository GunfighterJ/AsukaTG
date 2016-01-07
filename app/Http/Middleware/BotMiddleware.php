<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BotMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $botKey = null;
        if (isset($request->route()[2]['botKey'])) {
            $botKey = $request->route()[2]['botKey'];
        }

        if (!$botKey) {
            throw new NotFoundHttpException;
        }

        $telegram = app('telegram');
        if ($botKey != $telegram->getBotConfig(config('telegram.default'))['token']) {
            throw new NotFoundHttpException;
        }
        return $next($request);
    }
}
