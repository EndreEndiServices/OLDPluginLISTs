<?php

declare(strict_types=1);

namespace fcore\task;

use fcore\FCore;
use fcore\lang\Language;
use fcore\profile\ProfileManager;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class BroadcastTask extends Task {

    public function onRun(int $currentTick) {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $messages = Language::_(ProfileManager::lang($player), "broadcaster");
            $player->sendMessage(FCore::getPrefix().$messages[array_rand($messages, 1)]);
        }
    }
}