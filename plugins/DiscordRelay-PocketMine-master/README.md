DiscordRelay Plugin
===============
_Allows you to relay messages between a PocketMine server and Discord!_

[![CircleCI](https://circleci.com/gh/JackNoordhuis/DiscordRelay-PocketMine/tree/master.svg?style=svg)](https://circleci.com/gh/JackNoordhuis/DiscordRelay-PocketMine/tree/master)

### About

DiscordRelay is a [PocketMine-MP](https://github.com/pmmp/PocketMine-MP) plugin that provides easy-to-configure
functionality so you can bridge your Minecraft server and Discord guild chats. You can easily make a one way bridge
from discord to your server, from your server to discord or both ways; you can even setup multiple channels at varying
log levels to be relayed to! All of this is done by editing the [Settings](https://github.com/JackNoordhuis/DiscordRelay-PocketMine/blob/master/resources/Settings.yml)
file that is automatically generated by the plugin when it is first installed, by default DiscordRelay will not work as
you need to provide your own bot token and setup the channels.

### Wiki

Cool, but how the heck do I set it up? Glad you asked! [Jump over to the wiki](https://github.com/JackNoordhuis/DiscordRelay-PocketMine/wiki/Configuring)
and find out!

### Download

Discord Relay is currently in the ALPHA stage of releases so some things may not work as expected and there is no
guarantee the plugin won't suddenly stop working. If you would like to try out the plugin head over [to the releases](https://github.com/JackNoordhuis/DiscordRelay-PocketMine/releases)
and grab the latest version compatible with your PocketMine server.

### Issues

Found a problem with DiscordRelay? Make sure to open an issue on the [issue tracker](https://github.com/JackNoordhuis/DiscordRelay-PocketMine/issues) and we'll get it sorted!

### Contributing

If you wish to dig into the depths of the source code and/or contribute to this monstrosity you'll be happy to hear
there's a helper script that will clone the source code and the wiki for you:

Using curl:

```bash
curl -sL https://raw.githubusercontent.com/JackNoordhuis/DiscordRelay-PocketMine/master/clone.sh | bash -s -
```

Or using wget:

```bash
wget -q -O - https://raw.githubusercontent.com/JackNoordhuis/DiscordRelay-PocketMine/master/clone.sh | bash -s -
```

You can now make your local modifications, commit and push to github! If you wish to use this helper script but want to
push your changes to a fork, simply run this command:

```bash
git remote set-url origin git@github.com:YOUR_USERNAME/DiscordRelay-PocketMine.git
```

If you're modifying the wiki, don't forget to `cd` into the wiki folder and `git init` before committing your changes
or nothing will happen due to the .gitignore being set to ignore this folder as it only exists locally for your
convenience.

__The content of this repo is licensed under the GNU Lesser General Public License v3. A full copy of the license is available [here](LICENSE).__