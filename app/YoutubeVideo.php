<?php
declare(strict_types=1);
namespace Woeler\YoutubeBot;

use DateTime;
use DateTimeZone;
use Woeler\YoutubeBot\Configuration\Configuration;

class YoutubeVideo
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $channelName;

    /**
     * @var string
     */
    protected $imageUrl;

    /**
     * @var DateTime
     */
    protected $publishDateTime;

    public function __construct($youtubeActivity)
    {
        $this->title = $youtubeActivity->snippet->channelTitle;
        $this->url = 'https://www.youtube.com/watch?v=' . $youtubeActivity->contentDetails->upload->videoId;
        $this->description = self::makeShortDescription($youtubeActivity->snippet->description);
        $this->channelName = $youtubeActivity->snippet->channelTitle;
        $this->imageUrl = $youtubeActivity->snippet->thumbnails->maxres->url;
        $this->publishDateTime = new DateTime($youtubeActivity->snippet->publishedAt, new DateTimeZone('UTC'));
    }

    /**
     * @return mixed
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getChannelName(): string
    {
        return $this->channelName;
    }

    /**
     * @return mixed
     */
    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    public function getPublishDateTime(): DateTime
    {
        return $this->publishDateTime;
    }

    public function isNew(): bool
    {
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('UTC'));

        return ($now->getTimestamp() - $this->publishDateTime->getTimestamp()) < (Configuration::getConfigurationValue('runIntervalMinutes') * 60);
    }

    /**
     * Shorten the description on newline
     */
    private static function makeShortDescription(string $longDescription): string
    {
        $description = explode(PHP_EOL, $longDescription, 2);

        return $description[0] ?? $longDescription;
    }
}
