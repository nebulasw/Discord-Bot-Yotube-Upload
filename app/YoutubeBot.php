<?php
declare(strict_types=1);
namespace Woeler\YoutubeBot;

use DateTime;
use DateTimeZone;
use Madcoda\Youtube\Youtube;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Slack;
use SlackAttachment;
use SlackMessage;

class YoutubeBot
{
    /**
     * @var string
     */
    protected $youtubeApiKey;

    /**
     * @var string
     */
    protected $youtubeChannelId;

    /**
     * @var string
     */
    protected $slackHookUrl;

    /**
     * @var string
     */
    protected $discordHookUrl;

    /**
     * @var bool
     */
    protected $slackEnabled;

    /**
     * @var bool
     */
    protected $discordEnabled;

    /**
     * @var string|null
     */
    protected $botAvatarUrl;

    /**
     * @var string
     */
    protected $botName;

    /**
     * @var string
     */
    protected $colorHex;

    /**
     * @var int
     */
    protected $runIntervalMinutes;

    /**
     * @var Slack
     */
    protected $slack;

    /**
     * @var Youtube
     */
    protected $youtube;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * YoutubeBot constructor.
     *
     * @throws \Exception
     */
    public function __construct(array $configuration)
    {
        $this->logger = new Logger('YouTube Bot Logger');
        $this->logger->pushHandler(new StreamHandler('bot.log', Logger::WARNING));
        $this->youtubeApiKey = $configuration['youtubeApiKey'] ?? '';
        $this->youtubeChannelId = $configuration['youtubeChannelId'] ?? '';
        $this->slackHookUrl = $configuration['slackHookUrl'] ?? '';
        $this->discordHookUrl = $configuration['discordHookUrl'] ?? '';
        $this->slackEnabled = $configuration['slackEnabled'] ?? false;
        $this->discordEnabled = $configuration['discordEnabled'] ?? false;
        $this->botAvatarUrl = $configuration['botAvatarUrl'] ?? 'https://www.youtube.com/yt/about/media/images/brand-resources/icons/YouTube-icon-our_icon.png';
        $this->botName = $configuration['botName'] ?? 'Youtube Video Bot';
        $this->slack = new Slack($this->slackHookUrl);
        $this->slack->setDefaultUsername($this->botName);
        $this->slack->setDefaultIcon($this->botAvatarUrl);
        $this->youtube = new Youtube(['key' => $configuration['youtubeApiKey']]);
        $this->colorHex = str_replace('#', '', $configuration['embedColor'] ?? 'FF0000');
        $this->runIntervalMinutes = $configuration['runIntervalMinutes'] ?? 30;
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

            $dt = new DateTime($activity->snippet->publishedAt, new DateTimeZone('UTC'));
            $now = new DateTime();
            $now->setTimezone(new DateTimeZone('UTC'));

            if (($now->getTimestamp() - $dt->getTimestamp()) < ($this->runIntervalMinutes * 60)) {
                if ($this->slackEnabled) {
                    $this->sendToSlack($activity);
                }
                if ($this->discordEnabled) {
                    $this->sendToDiscord($activity);
                }
            }
        }
    }

    /**
     * Send a message to Discord
     *
     * @param $youtubeActivity
     */
    protected function sendToDiscord($youtubeActivity): bool
    {
        if (empty($this->discordHookUrl)) {
            $this->logger->error('Posting to Discord is enabled, but you have not configured a webhook.');

            return false;
        }

        $httpcode = $this->runCurl(json_encode($this->buildDiscordMessage($youtubeActivity)));

        if (204 !== $httpcode) {
            $this->logger->warning(
                'Unexpected status code returned by Discord.',
                [
                    'expected' => 204,
                    'actual' => $httpcode,
                    'videoId' => $youtubeActivity->contentDetails->upload->videoId,
                ]
            );

            return false;
        }

        return true;
    }

    /**
     * Send a message to Slack
     *
     * @param $youtubeActivity
     */
    protected function sendToSlack($youtubeActivity): bool
    {
        if (empty($this->slackHookUrl)) {
            $this->logger->error('Posting to Slack is enabled, but you have not configured a webhook.');

            return false;
        }

        $success = $this->buildSlackMessage($youtubeActivity)->send();

        if (!$success) {
            $this->logger->warning('Posting to Slack was not successful.', ['videoId' => $youtubeActivity->contentDetails->upload->videoId]);

            return false;
        }

        return true;
    }

    /**
     * Shorten the description on newline
     */
    protected function makeShortDescription(string $longDescription): string
    {
        $description = explode(PHP_EOL, $longDescription, 2);

        return $description[0] ?? $longDescription;
    }

    /**
     * Builds a Slack message
     *
     * @param $youtubeActivity
     */
    protected function buildSlackMessage($youtubeActivity): SlackMessage
    {
        $message = new SlackMessage($this->slack);
        $attachement = new SlackAttachment($youtubeActivity->snippet->channelTitle . ' uploaded a new video to YouTube! https://www.youtube.com/watch?v=' . $youtubeActivity->contentDetails->upload->videoId);
        $attachement->setPretext($youtubeActivity->snippet->channelTitle . ' uploaded a new video to YouTube!');
        $attachement->setTitle($youtubeActivity->snippet->title, 'https://www.youtube.com/watch?v=' . $youtubeActivity->contentDetails->upload->videoId);
        $attachement->setImage($youtubeActivity->snippet->thumbnails->maxres->url);
        $attachement->setColor('#' . $this->colorHex);
        $attachement->setText($this->makeShortDescription($youtubeActivity->snippet->description));
        $attachement->addButton('View on YouTube', 'https://www.youtube.com/watch?v=' . $youtubeActivity->contentDetails->upload->videoId);
        $message->addAttachment($attachement);

        return $message;
    }

    /**
     * Builds a Discord embeds array
     *
     * @param $youtubeActivity
     */
    protected function buildDiscordMessage($youtubeActivity): array
    {
        return [
            'username' => $this->botName,
            'content' => $youtubeActivity->snippet->channelTitle . ' uploaded a new video to YouTube!',
            'avatar_url' => $this->botAvatarUrl,
            'embeds' => [[
                'title' => $youtubeActivity->snippet->title,
                'description' => $this->makeShortDescription($youtubeActivity->snippet->description),
                'url' => 'https://www.youtube.com/watch?v=' . $youtubeActivity->contentDetails->upload->videoId,
                'color' => hexdec($this->colorHex),
                'image' => [
                    'url' => $youtubeActivity->snippet->thumbnails->maxres->url,
                ],
                'fields' => [
                ],
                'footer' => [
                    'text' => $youtubeActivity->snippet->channelTitle,
                    'icon_url' => $this->botAvatarUrl,
                ],
            ]],
        ];
    }

    /**
     * Runs a CURL POST request
     *
     * @param null|mixed $data
     */
    protected function runCurl($data = null): int
    {
        $ch = curl_init($this->discordHookUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        if (null !== $data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpcode;
    }
}
