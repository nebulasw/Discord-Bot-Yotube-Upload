<?php
declare(strict_types=1);

use Woeler\YoutubeBot\YoutubeBot;

require 'vendor/autoload.php';

$file = file_get_contents('config.json');
$config = json_decode($file, true);

try {
    $bot = new YoutubeBot($config);
    $bot->run();
} catch (Exception $e) {
    print_r('The bot encountered an exception: '.$e->getFile().' on line '.$e->getLine().PHP_EOL.$e->getMessage().PHP_EOL);
}
