<?php
declare(strict_types=1);
namespace Woeler\YoutubeBot\Messaging\System;

use Slack;
use SlackAttachment;
use SlackMessage;
use Woeler\YoutubeBot\Configuration\Configuration;
use Woeler\YoutubeBot\YoutubeVideo;

class SlackSystem extends AbstractMessagingSystem
{
    const SYSTEM_IDENTIFIER = 'slack';

    public function send(YoutubeVideo $video, array $configuration): bool
    {
        if (empty($configuration['hookUrl'])) {
            $this->logger->error('Posting to Slack is enabled, but you have not configured a webhook.');

            return false;
        }

        $slack = new Slack($configuration['hookUrl']);
        $slack->setDefaultUsername(Configuration::getConfigurationValue('botName'));
        $slack->setDefaultIcon(Configuration::getConfigurationValue('botAvatarUrl'));

        $message = new SlackMessage($slack);

        $attachement = new SlackAttachment($video->getChannelName() . ' uploaded a new video to YouTube! ' . $video->getUrl());
        $attachement->setPretext($this->buildMessage($video, $configuration['overrideMessage'] ?? null));
        $attachement->setTitle($video->getTitle(), $video->getUrl());
        $attachement->setImage($video->getImageUrl());
        $attachement->setColor('#' . Configuration::getConfigurationValue('embedColor'));
        $attachement->setText($video->getDescription());
        $attachement->addButton('View on YouTube', $video->getUrl());

        $message->addAttachment($attachement);
        $success = $message->send();

        if (!$success) {
            $this->logger->warning('Posting to Slack was not successful.', ['videoUrl' => $video->getUrl()]);

            return false;
        }

        return true;
    }
}
