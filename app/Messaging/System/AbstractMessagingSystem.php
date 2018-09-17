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

    protected function buildMessage(YoutubeVideo $video, string $overrideMessage = null): string
    {
        if (null === $overrideMessage) {
            return $video->getChannelName() . ' uploaded a new video! ' . $video->getUrl();
        }

        $overrideMessage = str_replace(['[VIDEO_TITLE]', '[VIDEO_URL]', '[CHANNEL_NAME]', '[VIDEO_DESCRIPTION]'], [$video->getTitle(), $video->getUrl(), $video->getChannelName(), $video->getDescription()], $overrideMessage);

        return $overrideMessage;
    }

    abstract public function send(YoutubeVideo $video, array $configuration): bool;
}
