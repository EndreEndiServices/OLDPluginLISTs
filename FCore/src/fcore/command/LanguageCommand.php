<?php

declare(strict_types=1);

namespace fcore\command;

use fcore\profile\ProfileManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class LanguageCommand extends Command {

    public function __construct() {
        parent::__construct("lang", "Allows change language", null, ["language", "jazyk"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if($sender instanceof Player) {
            if(empty($args[0]) || !in_array($args[0], ["ces", "eng"])) {
                $sender->sendMessage("§c> Use /lang <ces|eng>");
                return;
            }
            ProfileManager::$players[$sender->getName()]["lang"] = $args[0];
            $sender->sendMessage("§a> Language changed!");
            return;
        }
    }

}