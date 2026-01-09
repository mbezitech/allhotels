<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;

class ExpirePendingBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:expire-pending {--minutes=10 : Minutes after which pending bookings should expire}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically expire pending bookings that have been pending for more than the specified minutes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $minutes = (int) $this->option('minutes');
        
        $this->info("Checking for pending bookings older than {$minutes} minutes...");
        
        $expiredCount = Booking::expireStalePending($minutes);
        
        if ($expiredCount > 0) {
            $this->info("Successfully expired {$expiredCount} booking(s).");
        } else {
            $this->info("No bookings to expire.");
        }
        
        return Command::SUCCESS;
    }
}
