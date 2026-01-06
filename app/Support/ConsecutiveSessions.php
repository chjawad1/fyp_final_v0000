<?php

namespace App\Support;

use App\Models\DefenceSession;
use Carbon\Carbon;

class ConsecutiveSessions
{
    /**
     * Count the length of the consecutive session run for a given evaluator
     * around the target time. It looks backward and forward from $at and
     * treats sessions as consecutive if the gap between adjacent sessions
     * is <= $breakMinutes.
     *
     * Returns the number including the target slot itself (i.e., prev + 1 + next).
     */
    public static function getRunLength(int $evaluatorUserId, Carbon $at, ?int $breakMinutes = null): int
    {
        $breakMinutes = $breakMinutes ?? (int) config('defence.consecutive_break_minutes', 30);

        $prevCount = self::countPrev($evaluatorUserId, $at, $breakMinutes);
        $nextCount = self::countNext($evaluatorUserId, $at, $breakMinutes);

        return $prevCount + 1 + $nextCount;
    }

    /**
     * Return true if adding a session at $at would exceed the configured consecutive limit.
     */
    public static function wouldExceedLimit(int $evaluatorUserId, Carbon $at, ?int $limit = null, ?int $breakMinutes = null): bool
    {
        $limit = $limit ?? (int) config('defence.consecutive_limit', 5);
        return self::getRunLength($evaluatorUserId, $at, $breakMinutes) > $limit;
    }

    protected static function countPrev(int $evaluatorUserId, Carbon $at, int $breakMinutes): int
    {
        // Fetch nearest previous sessions for this evaluator
        $prev = DefenceSession::select('scheduled_at')
            ->where('status', '!=', 'cancelled')
            ->whereHas('assignments', fn($q) => $q->where('user_id', $evaluatorUserId))
            ->where('scheduled_at', '<', $at)
            ->orderBy('scheduled_at', 'desc')
            ->limit(20) // should be plenty to find the break
            ->get()
            ->pluck('scheduled_at')
            ->map(fn($dt) => $dt instanceof Carbon ? $dt : Carbon::parse($dt));

        $count = 0;
        $cursor = $at->copy();

        foreach ($prev as $scheduled) {
            $gap = $scheduled->diffInMinutes($cursor);
            if ($gap <= $breakMinutes) {
                $count++;
                $cursor = $scheduled;
            } else {
                break;
            }
        }

        return $count;
    }

    protected static function countNext(int $evaluatorUserId, Carbon $at, int $breakMinutes): int
    {
        // Fetch nearest next sessions for this evaluator
        $next = DefenceSession::select('scheduled_at')
            ->where('status', '!=', 'cancelled')
            ->whereHas('assignments', fn($q) => $q->where('user_id', $evaluatorUserId))
            ->where('scheduled_at', '>', $at)
            ->orderBy('scheduled_at', 'asc')
            ->limit(20)
            ->get()
            ->pluck('scheduled_at')
            ->map(fn($dt) => $dt instanceof Carbon ? $dt : Carbon::parse($dt));

        $count = 0;
        $cursor = $at->copy();

        foreach ($next as $scheduled) {
            $gap = $scheduled->diffInMinutes($cursor);
            if ($gap <= $breakMinutes) {
                $count++;
                $cursor = $scheduled;
            } else {
                break;
            }
        }

        return $count;
    }
}