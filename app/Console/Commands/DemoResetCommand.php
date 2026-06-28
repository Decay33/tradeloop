<?php

namespace App\Console\Commands;

use App\Services\DemoDataService;
use Illuminate\Console\Command;

class DemoResetCommand extends Command
{
    protected $signature = 'demo:reset';

    protected $description = 'Reset only the TradeLoop demo business and seed demo data.';

    public function handle(DemoDataService $demoData): int
    {
        $counts = $demoData->reset();

        $this->info('Demo data reset for Smith Home Services.');

        foreach ($counts as $label => $count) {
            $this->line($label.': '.$count);
        }

        return self::SUCCESS;
    }
}
