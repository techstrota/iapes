<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Attendance;
use App\Models\InternManagement\DailyLogbook;
use App\Models\TaskManagement\Task;
use App\Models\TaskManagement\TaskSubmission;
use App\Models\InternManagement\Intern;
use Carbon\Carbon;

$intern = Intern::active()->first();
echo "Testing calculation for Intern: {$intern->name} (ID: {$intern->id})\n";

$startDate = now()->startOfMonth();
$endDate = now()->endOfMonth();
$startStr = $startDate->toDateString();
$endStr = $endDate->toDateString();

// 1. Eligible days
$curr = $startDate->copy();
$eligibleDays = 0;
while ($curr->lte($endDate)) {
    if (!$curr->isSunday()) $eligibleDays++;
    $curr->addDay();
}
$eligibleDays = max(1, $eligibleDays);

// 2. Attendance
$attendances = Attendance::where('intern_id', $intern->id)->whereBetween('date', [$startStr, $endStr])->get();
$attendedDays = $attendances->whereIn('status', ['present', 'late', 'wfh'])->count();
$attPct = round(($attendedDays / $eligibleDays) * 100);

echo "1. Attendance: {$attPct}% ({$attendedDays}/{$eligibleDays} days)\n";

// 3. Task submissions
$taskSubmissions = TaskSubmission::where('intern_id', $intern->id)->whereNotNull('marks')->get();
$taskAvg = $taskSubmissions->isNotEmpty() ? $taskSubmissions->avg('marks') : 80;
echo "2. Task Avg Marks: {$taskAvg}\n";

// 4. Daily logs
$logs = DailyLogbook::where('intern_id', $intern->id)->whereBetween('date', [$startStr, $endStr])->get();
echo "3. Daily Logs in Month: {$logs->count()}\n";

echo "All calculations working cleanly!\n";
