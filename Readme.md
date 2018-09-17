# Youtube Social Bot

This is a simple bot that posts new uploads of a specified channel to various social and chat channels.

## Configuration

Configuration is done via a `config.json` file. An example configuration file is provided with the project and is called `example.config.json`.

Install all the dependencies by running `composer install` in the bot root directory.

After configuring the bot set up a cronjob to the file `run.php`.

### Configuration options

| Configuration value | Purpose | Default |
| --- | --- | --- |
| `youtubeChannelId` | The ID of the YouTube channel you would like to fetch videos from. You can find this in the channel url `https://www.youtube.com/channel/ThisIsTheChannelId` | `''` |
| `botName` | The name the bot will take in Slack and Discord. | `YouTube Bot` |
| `botAvatarUrl` | A link to the avatar image you want the bot to use. Use png or jpg for the best results. | Youtube icon image (png) |
| `youtubeApiKey` | Your API key to the YouTube API. Read [this article](https://developers.google.com/youtube/v3/getting-started) on how to obtain one. | `null` |
| `embedColor` | A hexadecimal color code to give your messages a personal touch. | `FF0000` |
| `runIntervalMinutes` | The interval of the cronjob. Make sure this equals the cronjob interval to prevent duplicate posts or missing posts. | `30` |

#### Overriding the default message

The default message sent by the system looks like this `[CHANNEL_NAME] uploaded a new video! [VIDEO_URL]`. It is understandable that you would want to override this, especially for systems that use an embeds structure and already have a link present (like Discord or Slack). This is why you can configure for each added system an override message. You can use variable parameters in this option to style the message just the way you want. This parameter is called `overrideMessage` and is explained below.

| Override message parameter | Is replaced by |
| --- | --- |
| `[VIDEO_TITLE]` | The title of the video. |
| `[VIDEO_URL]` | The URL of the video. |
| `[VIDEO_DESCRIPTION]` | The description of the video. |
| `[CHANNEL_NAME]` | The name of the channel that uploaded the video |

#### Configuring Slack

You can configure multiple Slack hooks:

```json
"systems": [
    {
      "slack": [
        {
          "enabled": true,
          "hookUrl": "https://hooks.slack.com/services/SomeNiceWebhookUrl",
          "overrideMessage": "[CHANNEL_NAME] uploaded a new video!"
        }
      ]
    }
  ]
```

| Configuration value | Purpose |
| ---| --- |
| `hookUrl` | The webhook URL obtained from Slack to which the bot should post. Read [this article](https://api.slack.com/incoming-webhooks) on how to obtain one. |
| `enabled` | A boolean value to turn this message on or off. |
| `overrideMessage` (optional) | An override for the default message. See [here](#overriding-the-default-message) how to format this option. |

#### Configuring Discord

You can configure multiple Discord hooks:

```json
"systems": [
    {
      "discord": [
        {
          "enabled": true,
          "hookUrl": "https://discordapp.com/api/webhooks/12345/SomeNiceWebhookUrl"
          "overrideMessage": "[CHANNEL_NAME] uploaded a new video! It's called [VIDEO_TITLE]."
        }
      ]
    }
  ]
```

| Configuration value | Purpose |
| ---| --- |
| `hookUrl` | The webhook URL obtained from Discord to which the bot should post. Read [this article](https://support.discordapp.com/hc/en-us/articles/228383668-Intro-to-Webhooks) on how to obtain one. |
| `enabled` | A boolean value to turn this message on or off. |
| `overrideMessage` (optional) | An override for the default message. See [here](#overriding-the-default-message) how to format this option. |


#### Configuring Telegram

You can configure multiple Telegram bots/chats:

```json
"systems": [
    {
      "telegram": [
        {
          "enabled": true,
          "botToken": "SomeNice:Token",
          "chatId": "1234567890"
        }
      ]
    }
  ]
```

| Configuration value | Purpose |
| ---| --- |
| `botToken` | The token of the bot that will send the message. Read [this article](https://core.telegram.org/bots#botfather) on how to obtain one. |
| `chatId` | The ID of the chat where the message should be sent to (bot needs to be in this chat). |
| `enabled` | A boolean value to turn this message on or off. |
| `overrideMessage` (optional) | An override for the default message. See [here](#overriding-the-default-message) how to format this option. |


#### Configuring Twitter

You can configure multiple twitter accounts, but you will need to create an app via [the Twitter developer portal](https://apps.twitter.com) in order to obtain your keys. When you have done that retrieve your own access token and also place it in the config.

```json
"systems": [
    {
      "twitter": [
          {
            "enabled": false,
            "consumerKey": "SomeConsumerKey",
            "consumerSecret": "SomeConsumerSecret",
            "accessToken": "SomeAccessToken",
            "accessTokenSecret": "SomeAccessTokenSecret"
          }
        ]
    }
  ]
```

| Configuration value | Purpose |
| ---| --- |
| `consumerKey` | Needed to post tweets to your account. |
| `consumerSecret` | Needed to post tweets to your account. |
| `accessToken` | Needed to post tweets to your account. |
| `accessTokenSecret` | Needed to post tweets to your account. |
| `enabled` | A boolean value to turn tweeting this message on or off. |
| `overrideMessage` (optional) | An override for the default message. See [here](#overriding-the-default-message) how to format this option. |
