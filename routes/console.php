<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('wabot:send-scheduled')->everyMinute();
Schedule::command('wabot:recurring')->everyMinute();
Schedule::command('wabot:retry-campaigns')->everyThirtyMinutes();
Schedule::command('wabot:cleanup-sessions --days=30')->dailyAt('03:00');
Schedule::command('seo:indexnow')->dailyAt('02:45');
Schedule::command('subscriptions:expire')->hourly();
