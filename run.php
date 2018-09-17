<?php
declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Woeler\YoutubeBot\YoutubeBot;

require 'vendor/autoload.php';

$file = file_get_contents('config.json');
$config = json_decode($file, true);
$logger = new Logger('YouTube Bot Logger');
$logger->pushHandler(new StreamHandler('bot.log', Logger::WARNING));

try {
    $bot = new YoutubeBot($config);
    $bot->run();
} catch (Exception $e) {
    $logger->error('The bot encountered an exception.', ['file' => $e->getFile(), 'line' => $e->getLine(), 'message' => $e->getMessage()]);
}
