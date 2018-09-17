<?php
declare(strict_types=1);
namespace Woeler\YoutubeBot\Messaging\System;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Woeler\YoutubeBot\YoutubeVideo;

abstract class AbstractMessagingSystem
{
    const SYSTEM_IDENTIFIER = '';

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct()
    {
        $this->logger = new Logger('YouTube Bot Logger');
        $this->logger->pushHandler(new StreamHandler('bot.log', Logger::WARNING));
    }

    abstract public function send(YoutubeVideo $video, array $configuration): bool;
}
