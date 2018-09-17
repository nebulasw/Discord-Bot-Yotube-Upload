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
| `slackHookUrl` | The webhook URL obtained from Slack to which the bot should post. Read [this article](https://api.slack.com/incoming-webhooks) on how to obtain one. | `''` |
| `discordHookUrl` | The webhook URL obtained from Discord to which the bot should post. Read [this article](https://support.discordapp.com/hc/en-us/articles/228383668-Intro-to-Webhooks) on how to obtain one. | `''` |
| `botName` | The name the bot will take in Slack and Discord. | `YouTube Bot` |
| `botAvatarUrl` | A link to the avatar image you want the bot to use. Use png or jpg for the best results. | Youtube icon image (png) |
| `youtubeApiKey` | Your API key to the YouTube API. Read [this article](https://developers.google.com/youtube/v3/getting-started) on how to obtain one. | `null` |
| `embedColor` | A hexadecimal color code to give your messages a personal touch. | `FF0000` |
| `runIntervalMinutes` | The interval of the cronjob. Make sure this equals the cronjob interval to prevent duplicate posts or missing posts. | `30` |
| `slackEnabled` | A boolean value to turn Slack messages on or off. | `false` |
| `discordEnabled` | A boolean value to turn Discord messages on or off. | `false` |