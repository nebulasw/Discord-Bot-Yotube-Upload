# Youtube Slack/Discord webhook bot

This is a simple bot that posts new uploads of a specified channel to Slack and/or Discord via a webhook.

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

#### Configuring Slack

You can configure multiple Slack hooks:

```json
"systems": [
    {
      "slack": [
        {
          "enabled": true,
          "hookUrl": "https://hooks.slack.com/services/SomeNiceWebhookUrl"
        }
      ]
    }
  ]
```

| Configuration value | Purpose |
| ---| --- |
| `hookUrl` | The webhook URL obtained from Slack to which the bot should post. Read [this article](https://api.slack.com/incoming-webhooks) on how to obtain one. |
| `enabled` | A boolean value to turn this message on or off. |

#### Configuring Discord

You can configure multiple Discord hooks:

```json
"systems": [
    {
      "discord": [
        {
          "enabled": true,
          "hookUrl": "https://discordapp.com/api/webhooks/12345/SomeNiceWebhookUrl"
        }
      ]
    }
  ]
```

| Configuration value | Purpose |
| ---| --- |
| `hookUrl` | The webhook URL obtained from Discord to which the bot should post. Read [this article](https://support.discordapp.com/hc/en-us/articles/228383668-Intro-to-Webhooks) on how to obtain one. |
| `enabled` | A boolean value to turn this message on or off. |

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