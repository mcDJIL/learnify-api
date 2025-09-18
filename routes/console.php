<?php
use Illuminate\Support\Facades\Schedule;

Schedule::command('quest:daily')->dailyAt('00:15');
Schedule::command('quest:weekly')->weeklyOn(1, '00:25');