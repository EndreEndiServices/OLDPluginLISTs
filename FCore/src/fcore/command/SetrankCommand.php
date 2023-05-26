<?php

declare(strict_types=1);

namespace fcore\command;

use fcore\FCore;
use fcore\profile\ProfileManager;
use fcore\profile\RankManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

/**
 * Class SetrankCommand
 * @package fcore\command
 */
class SetrankCommand extends Command {

    public $plugin;

    public function __construct(FCore $plugin) {
        $this->plugin = $plugin;
        parent::__construct("setrank", "Setrank command", null, []);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if($sender instanceof Player && $sender->isOp() && in_array(strtolower($sender->getName()), ["vixikcz", "mcpemastertvx"])) {
            if(isset($args[0]) && isset($args[1])) {
                $player = $this->plugin->getServer()->getPlayer($args[0]);
                if($player instanceof Player) {
                    RankManager::setRank($player, $args[1]);
                    if($args[1] == "vip") {
                        $sender->sendMessage("§aPlayer {$player->getName()} recieved VIP!");
                        $player->sendMessage("§a> You are recieved VIP!");
                        ProfileManager::addCoins($player, 10000.0);
                    }
                }
            }
        }
    }

}