<?php
use Illuminate\Support\Facades\Schedule;

Schedule::command('quest:daily')->dailyAt('00:01');
Schedule::command('quest:weekly')->weeklyOn(1, '00:05');