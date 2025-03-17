<?php
require_once __DIR__ . '/../vendor/autoload.php';

use AmirGeek\TelegramBot\Core\Bot;

$bot = new Bot();
$bot->handleWebhook();
