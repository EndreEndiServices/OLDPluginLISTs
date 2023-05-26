<?php

namespace ChatFilter;

use pocketmine\utils\TextFormat;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\Utils;
use pocketmine\Player;

class ChatFilterTask extends PluginTask {

    public function __construct($owner) {
        $this->owner = $owner;
    }

    public function onRun($currentTick) {
        $this->getOwner()->filter->clearRecentChat();
    }
}
