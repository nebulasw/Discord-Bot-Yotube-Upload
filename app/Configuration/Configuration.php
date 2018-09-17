<?php
declare(strict_types=1);
namespace Woeler\YoutubeBot\Configuration;

class Configuration
{
    const DEFAULT_embedColor = 'FF0000';
    const DEFAULT_botName = 'Youtube Bot';
    const DEFAULT_botAvatarUrl = 'https://www.youtube.com/yt/about/media/images/brand-resources/icons/YouTube-icon-our_icon.png';
    const DEFAULT_runIntervalMinutes = 30;

    public static function getConfigurationValue(string $value)
    {
        $file = file_get_contents('config.json');
        $config = json_decode($file, true);

        if (empty($config[$value])) {
            return \constant('self::DEFAULT_' . $value);
        }

        return $config[$value];
    }

    public static function all(): array
    {
        $file = file_get_contents('config.json');

        return json_decode($file, true);
    }
}
