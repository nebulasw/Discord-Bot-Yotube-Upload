<?php
declare(strict_types=1);
namespace Woeler\YoutubeBot;

use Madcoda\Youtube\Youtube;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Woeler\YoutubeBot\Messaging\System\AbstractMessagingSystem;
use Woeler\YoutubeBot\Messaging\System\DiscordSystem;
use Woeler\YoutubeBot\Messaging\System\SlackSystem;
use Woeler\YoutubeBot\Messaging\System\TelegramSystem;

class YoutubeBot
{
    /**
     * @var string
     */
    protected $youtubeChannelId;

    /**
     * @var Youtube
     */
    protected $youtube;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var string
     */
    private $youtubeApiKey;

    /**
     * @var array
     */
    private $systems;

    /**
     * YoutubeBot constructor.
     *
     * @throws \Exception
     */
    public function __construct(array $configuration)
    {
        $this->logger = new Logger('YouTube Bot Logger');
        $this->logger->pushHandler(new StreamHandler('bot.log', Logger::WARNING));
        $this->youtubeChannelId = $configuration['youtubeChannelId'] ?? '';
        $this->youtube = new Youtube(['key' => $configuration['youtubeApiKey']]);
        $this->youtubeApiKey = $configuration['youtubeApiKey'];
        $this->systems = $configuration['systems'];
    }

    /**
     * Run the bot
     *
     * @throws \Exception
     */
    public function run()
    {
        if (empty($this->youtubeApiKey)) {
            $this->logger->error('You have not set a YouTube API key.');

            return;
        }
        if (empty($this->youtubeChannelId)) {
            $this->logger->error('You have not set a YouTube channel id.');

            return;
        }

        $activities = $this->youtube->getActivitiesByChannelId($this->youtubeChannelId);

        foreach ($activities as $activity) {
            if ($activity->contentDetails->upload->videoId === null) {
                continue;
            }

            $video = new YoutubeVideo($activity);

            if ($video->isNew()) {
                foreach ($this->systems as $systemArray) {
                    foreach ($systemArray as $systemIdentifier => $system) {
                        foreach ($system as $configuration) {
                            if ($configuration['enabled']) {
                                if ('slack' === $systemIdentifier) {
                                    $this->postVideo(new SlackSystem(), $video, $configuration);
                                }
                                if ('discord' === $systemIdentifier) {
                                    $this->postVideo(new DiscordSystem(), $video, $configuration);
                                }
                                if ('telegram' === $systemIdentifier) {
                                    $this->postVideo(new TelegramSystem(), $video, $configuration);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    private function postVideo(AbstractMessagingSystem $system, YoutubeVideo $video, array $configuration)
    {
        $system->send($video, $configuration);
    }
}
