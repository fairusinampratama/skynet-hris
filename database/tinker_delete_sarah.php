<?php

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

$nameSearch = 'Sarah';
$today = Carbon::now()->toDateString();

$user = User::where('name', 'like', "%$nameSearch%")->first();

if (!$user) {
    echo "USER NOT FOUND: Could not find user with name like '$nameSearch'\n";
    exit;
}

echo "FOUND USER: {$user->name} (ID: {$user->id}, Email: {$user->email})\n";

$attendances = Attendance::where('user_id', $user->id)
    ->whereDate('date', $today)
    ->get();

if ($attendances->isEmpty()) {
    echo "NO ATTENDANCE RECORDS: No attendance found for today ({$today}).\n";
    exit;
}

$count = $attendances->count();
echo "DELETING {$count} RECORD(S) for today ({$today})...\n";

foreach ($attendances as $attendance) {
    try {
        $dateStr = $attendance->date->format('Y-m-d');
        $attendance->delete();
        echo " - Deleted record for {$dateStr} (ID: {$attendance->id})\n";
    } catch (\Exception $e) {
        echo " - FAILED to delete record ID {$attendance->id}: {$e->getMessage()}\n";
    }
}

echo "DONE: Sarah's attendance for today has been cleared.\n";
