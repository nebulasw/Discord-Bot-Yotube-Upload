<?php
declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Woeler\YoutubeBot\Configuration\Configuration;
use Woeler\YoutubeBot\YoutubeBot;

require 'vendor/autoload.php';

$logger = new Logger('YouTube Bot Logger');
$logger->pushHandler(new StreamHandler('bot.log', Logger::WARNING));

try {
    $bot = new YoutubeBot(Configuration::all());
    $bot->run();
} catch (Exception $e) {
    $logger->error('The bot encountered an exception.', ['file' => $e->getFile(), 'line' => $e->getLine(), 'message' => $e->getMessage()]);
}
