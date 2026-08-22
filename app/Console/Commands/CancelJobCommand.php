<?php

namespace App\Console\Commands;

use App\Traits\CancellableJob;
use Illuminate\Console\Command;

class CancelJobCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:cancel {key : Job ID, Job class name (e.g. CheckOverdueTasksJob), or custom key (e.g. task_1)} {--clear : Clear a previously set cancel signal}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a cancellation signal to stop a specific job or job class';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $key = $this->argument('key');
        $clear = $this->option('clear');

        if ($clear) {
            CancellableJob::clearCancelSignal($key);
            $this->info("Successfully cleared cancel signal for key: [{$key}]");
        } else {
            CancellableJob::cancel($key);
            $this->warn("Successfully sent cancel signal for key: [{$key}]");
            $this->line("Any running or upcoming job checking for [{$key}] will exit gracefully.");
        }

        return Command::SUCCESS;
    }
}
