<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsEvaluator
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !self::isEvaluator($user)) {
            abort(403, 'This area is only accessible to evaluators.');
        }

        return $next($request);
    }

    protected static function isEvaluator($user): bool
    {
        // If you use spatie/laravel-permission
        if (method_exists($user, 'hasRole')) {
            try {
                if ($user->hasRole('evaluator')) {
                    return true;
                }
            } catch (\Throwable $e) {
                // fall through to other checks
            }
        }

        // If you store a simple role column
        if (property_exists($user, 'role') && ($user->role ?? null) === 'evaluator') {
            return true;
        }

        // If you have an Evaluator model relation like $user->evaluator()
        if (method_exists($user, 'evaluator')) {
            return (bool) $user->evaluator()->exists();
        }

        // If you flag evaluators with a boolean like $user->is_evaluator
        if (property_exists($user, 'is_evaluator') && (bool) ($user->is_evaluator ?? false) === true) {
            return true;
        }

        return false;
    }
}