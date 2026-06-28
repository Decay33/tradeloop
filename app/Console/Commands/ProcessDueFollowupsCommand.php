<?php

namespace App\Console\Commands;

use App\Models\FollowupMessage;
use App\Services\DemoMessageSender;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

class ProcessDueFollowupsCommand extends Command
{
    protected $signature = 'followups:process-due';

    protected $description = 'Process due scheduled follow-up messages using simulated demo sending.';

    public function handle(DemoMessageSender $sender): int
    {
        $messages = FollowupMessage::query()
            ->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->get();

        $processed = 0;

        foreach ($messages as $message) {
            try {
                $sender->send($message);
                $processed++;
            } catch (ValidationException $exception) {
                $this->warn('Skipped follow-up '.$message->id.': '.$exception->getMessage());
            }
        }

        $this->info("Processed {$processed} due follow-up messages.");

        return self::SUCCESS;
    }
}
