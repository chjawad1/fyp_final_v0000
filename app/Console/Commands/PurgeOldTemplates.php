<?php

namespace App\Console\Commands;

use App\Models\DocumentTemplate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PurgeOldTemplates extends Command
{
    protected $signature = 'templates:purge {--days=30} {--dry-run}';
    protected $description = 'Permanently delete soft-deleted templates older than N days and remove their files';

    public function handle(): int
    {
        $days   = (int) $this->option('days');
        $dry    = (bool) $this->option('dry-run');
        $cutoff = now()->subDays($days);

        $this->info("Looking for templates soft-deleted before {$cutoff->toDateTimeString()} (days={$days})" . ($dry ? ' [DRY RUN]' : ''));

        $total = 0;
        $filesDeleted = 0;

        DocumentTemplate::onlyTrashed()
            ->where('deleted_at', '<', $cutoff)
            ->orderBy('id')
            ->chunk(100, function ($batch) use (&$total, &$filesDeleted, $dry) {
                foreach ($batch as $t) {
                    $total++;

                    if ($dry) {
                        $this->line("Would purge: #{$t->id} {$t->name}");
                        continue;
                    }

                    if ($t->file_path) {
                        try {
                            if (Storage::disk('public')->delete($t->file_path)) {
                                $filesDeleted++;
                            }
                        } catch (\Throwable $e) {
                            Log::warning('Failed to delete template file during purge', [
                                'id' => $t->id,
                                'file' => $t->file_path,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }

                    try {
                        $t->forceDelete();
                    } catch (\Throwable $e) {
                        Log::error('Failed to force delete template during purge', [
                            'id' => $t->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

        if ($dry) {
            $this->info("Dry run complete. Candidates: {$total}");
        } else {
            $this->info("Purge complete. Records purged: {$total}. Files deleted: {$filesDeleted}.");
        }

        return Command::SUCCESS;
    }
}