<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Message;
use Carbon\Carbon;

class CleanupMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messages:cleanup';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup messages after 5 minute of being read';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $softDeletedCount = Message::where('read_at', '<', Carbon::now()->subMinutes(1))
               ->whereNull('deleted_at')
               ->delete();
               
        $this->info("Successfully soft deleted {$softDeletedCount} read messages.");
    }
}
