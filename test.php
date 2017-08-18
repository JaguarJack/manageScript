<?php
include __DIR__ . '/core/start.php';

use Cron\Crontab;

$master = new Crontab();
$master->start();