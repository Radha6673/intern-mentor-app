<?php
namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class performanceReport extends Command
{

    protected $signature = 'app:performance-report {--intern== : Specific Intern User ID}';

    protected $description = 'Generates performance report for interns including task statistics and completion rate.';

    public function handle(): int
    {


        $internId = $this->option('intern');

        $query = User::where('role', 'intern')->with('myTasks');

        if ($internId !== null && $internId !== '') {
            $internId = ltrim($internId, '=');
            $query->where('id', $internId);
        }

        $interns = $query->get();

        if ($interns->isEmpty()) {
            $this->error('No intern(s) found!');
            return Command::FAILURE;
        }

        $tableHeaders = ['Intern ID', 'Name', 'Email', 'Total Tasks', 'Approved', 'Submitted', 'Pending', 'Completion Rate'];
        $tableRows = [];

        foreach ($interns as $intern) {
            $totalTasks = $intern->myTasks->count();
            $approvedTasks = $intern->myTasks->where('status', 'approved')->count();
            $submittedTasks = $intern->myTasks->where('status', 'submitted')->count();
            $pendingTasks = $intern->myTasks->whereIn('status', ['pending', 'in_progress'])->count();

            $completionRate = $totalTasks > 0
                ? round(($approvedTasks / $totalTasks) * 100, 1) . '%'
                : '0%';

            $tableRows[] = [
                $intern->id,
                $intern->name,
                $intern->email,
                $totalTasks,
                $approvedTasks,
                $submittedTasks,
                $pendingTasks,
                $completionRate,
            ];
        }

        $this->table($tableHeaders, $tableRows);

        $this->newline();
        $this->info('Report generated successfully at: ' . now()->toDateTimeString());

        return Command::SUCCESS;
    }
}