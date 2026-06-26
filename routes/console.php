<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('wabot:send-scheduled')->everyMinute();
Schedule::command('wabot:recurring')->everyMinute();
Schedule::command('wabot:retry-campaigns')->everyThirtyMinutes();
Schedule::command('wabot:cleanup-sessions --days=30')->dailyAt('03:00');
