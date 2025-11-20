<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class RateLimitMiddleware
{
    public function handle($request, Closure $next)
    {
        $keyAttempts3min = "rate_limit_3min";
        $keyAttempts1hour = "rate_limit_1hour";
        $keyBlockCount = "rate_limit_block_count";

        $attempts3min = session($keyAttempts3min, 0);
        $attempts1hour = session($keyAttempts1hour, 0);
        $blockCount = session($keyBlockCount, 0);
        $now = now();

        // Limite de 3 minutes (10 tentatives)
        if ($attempts3min >= 10) {
            if ($blockCount < 2) {
                $blockUntil = session($keyAttempts3min.'_block_until', $now->addMinutes(5));
                session()->put($keyBlockCount, $blockCount + 1);
                return response()->json(['message' => 'Vous avez été temporairement bloqué pour 5 minutes'], 429);
            } else {
                $blockUntil = session($keyAttempts3min.'_block_until', $now->addHour());
                return response()->json(['message' => 'Trop de tentatives. Vous êtes bloqué pour une heure.'], 429);
            }
        }

        // Limite de 1 heure (100 tentatives)
        if ($attempts1hour >= 100) {
            $blockUntil = session($keyAttempts1hour.'_block_until', $now->addHour());
            return response()->json(['message' => 'Trop de tentatives. Vous êtes bloqué pour une heure.'], 429);
        }

        // Incrément des tentatives
        session()->put($keyAttempts3min, $attempts3min + 1);
        session()->put($keyAttempts1hour, $attempts1hour + 1);
        session()->put($keyAttempts3min.'_expires', $now->addMinutes(3));
        session()->put($keyAttempts1hour.'_expires', $now->addHour());

        return $next($request);
    }

}
