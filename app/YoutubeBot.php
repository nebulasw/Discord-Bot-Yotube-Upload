<?php
declare(strict_types=1);
namespace Woeler\YoutubeBot;

use DateTime;
use DateTimeZone;
use Madcoda\Youtube\Youtube;
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
     * YoutubeBot constructor.
     *
     * @throws \Exception
     */
    public function __construct(array $configuration)
    {
        $this->youtubeApiKey = $configuration['youtubeApiKey'];
        $this->youtubeChannelId = $configuration['youtubeChannelId'];
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
    protected function sendToDiscord($youtubeActivity)
    {
        $data = [
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

        $ch = curl_init($this->discordHookUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }

    /**
     * Send a message to Slack
     *
     * @param $youtubeActivity
     */
    protected function sendToSlack($youtubeActivity)
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
        $message->send();
    }

    /**
     * Shorten the description on newline
     */
    protected function makeShortDescription(string $longDescription): string
    {
        $description = explode(PHP_EOL, $longDescription, 2);

        return $description[0];
    }
}
