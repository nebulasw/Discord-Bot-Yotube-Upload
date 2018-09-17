<?php
declare(strict_types=1);
namespace Woeler\YoutubeBot\Messaging\System;

use Twitter;
use Woeler\YoutubeBot\YoutubeVideo;

class TwitterSystem extends AbstractMessagingSystem
{
    const SYSTEM_IDENTIFIER = 'twitter';

    public function send(YoutubeVideo $video, array $configuration): bool
    {
        if (empty($configuration['consumerKey'])
            || empty($configuration['consumerSecret'])
            || empty($configuration['accessToken'])
            || empty($configuration['accessTokenSecret'])) {
            $this->logger->error('Your Twitter configuration is incomplete');

            return false;
        }

        $twitter = new Twitter($configuration['consumerKey'], $configuration['consumerSecret'], $configuration['accessToken'], $configuration['accessTokenSecret']);
        $message = $video->getChannelName() . ' posted a new video! ' . $video->getTitle() . PHP_EOL . $video->getUrl();
        $twitter->send($message);

        return true;
    }
}
