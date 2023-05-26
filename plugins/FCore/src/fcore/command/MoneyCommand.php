<?php

declare(strict_types=1);

namespace fcore\command;

use fcore\FCore;
use fcore\profile\ProfileManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class MoneyCommand extends Command {

    public $plugin;

    public function __construct(FCore $plugin) {
        $this->plugin = $plugin;
        parent::__construct("money", "Displays money status", null, ["coins", "cash"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if($sender instanceof Player) {
            $sender->sendMessage(FCore::getPrefix()."§aYour money status: ".ProfileManager::getPlayerProfileData($sender, "coins")." coins.");
        }
    }
}