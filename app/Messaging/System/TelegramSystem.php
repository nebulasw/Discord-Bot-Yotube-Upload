<?php
declare(strict_types=1);
namespace Woeler\YoutubeBot\Messaging\System;

use Woeler\YoutubeBot\YoutubeVideo;

class TelegramSystem extends AbstractMessagingSystem
{
    const SYSTEM_IDENTIFIER = 'telegram';

    public function send(YoutubeVideo $video, array $configuration): bool
    {
        $params = [
            'chat_id' => $configuration['chatId'],
            'text' => $video->getChannelName() . ' uploaded a new video to YouTube! ' . PHP_EOL . $video->getUrl(),
        ];

        $ch = curl_init('https://api.telegram.org/bot' . $configuration['botToken'] . '/sendMessage');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $result = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($result, true);

        if (!$result['ok']) {
            $this->logger->warning('Posting to Telegram was not successful.', ['videoUrl' => $video->getUrl()]);

            return false;
        }

        return true;
    }
}
