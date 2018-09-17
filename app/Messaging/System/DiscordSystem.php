<?php
declare(strict_types=1);
namespace Woeler\YoutubeBot\Messaging\System;

use Woeler\YoutubeBot\Configuration\Configuration;
use Woeler\YoutubeBot\YoutubeVideo;

class DiscordSystem extends AbstractMessagingSystem
{
    const SYSTEM_IDENTIFIER = 'discord';

    public function send(YoutubeVideo $video, array $configuration): bool
    {
        if (empty($configuration['hookUrl'])) {
            $this->logger->error('Posting to Discord is enabled, but you have not configured a webhook.');

            return false;
        }

        $data = [
            'username' => Configuration::getConfigurationValue('botName'),
            'content' => $video->getChannelName() . ' uploaded a new video to YouTube!',
            'avatar_url' => Configuration::getConfigurationValue('botAvatarUrl'),
            'embeds' => [[
                'title' => $video->getTitle(),
                'description' => $video->getDescription(),
                'url' => $video->getUrl(),
                'color' => hexdec(Configuration::getConfigurationValue('embedColor')),
                'image' => [
                    'url' => $video->getImageUrl(),
                ],
                'fields' => [
                ],
                'footer' => [
                    'text' => $video->getChannelName(),
                    'icon_url' => Configuration::getConfigurationValue('botAvatarUrl'),
                ],
            ]],
        ];

        $ch = curl_init($configuration['hookUrl']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (204 !== $httpcode) {
            $this->logger->warning(
                'Unexpected status code returned by Discord.',
                [
                    'expected' => 204,
                    'actual' => $httpcode,
                    'videoUrl' => $video->getUrl(),
                ]
            );

            return false;
        }

        return true;
    }
}
