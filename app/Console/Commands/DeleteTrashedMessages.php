<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Message;
use Carbon\Carbon;

class DeleteTrashedMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messages:delete-trashed';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deleted messages that have been trashed for more than 7 days.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Calculate the date from 7 days ago
        $date = Carbon::now()->subDays(7);

        // Delete all soft-deleted messages older than 7 days
        $deletedCount = Message::onlyTrashed()
            ->where('deleted_at', '<', $date)
            ->forceDelete();

        $this->info("Successfully deleted {$deletedCount} old messages.");
    }
}
