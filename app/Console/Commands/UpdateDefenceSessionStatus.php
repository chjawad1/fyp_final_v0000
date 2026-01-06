<?php

namespace App\Console\Commands;

use App\Models\DefenceSession;
use Illuminate\Console\Command;

class UpdateDefenceSessionStatus extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'defence:update-status';

    /**
     * The console command description.
     */
    protected $description = 'Update defence session status to completed for past scheduled sessions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Find all scheduled sessions where the scheduled time has passed
        $pastSessions = DefenceSession::where('status', 'scheduled')
            ->where('scheduled_at', '<', now())
            ->get();

        if ($pastSessions->isEmpty()) {
            $this->info('No past scheduled sessions found.');
            return 0;
        }

        $updatedCount = 0;

        foreach ($pastSessions as $session) {
            $session->update(['status' => 'completed']);
            $updatedCount++;
            
            $this->line("Updated session ID {$session->id} - Project:  " . ($session->project ? $session->project->title : 'N/A'));
        }

        $this->info("Successfully updated {$updatedCount} defence session(s) to 'completed' status.");
        
        return 0;
    }
}