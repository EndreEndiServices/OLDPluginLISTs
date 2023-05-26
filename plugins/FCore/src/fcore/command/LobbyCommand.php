<?php

declare(strict_types=1);

namespace fcore\command;

use fcore\FCore;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

/**
 * Class LobbyCommand
 * @package fcore\command
 */
class LobbyCommand extends Command {

    public $plugin;

    public function __construct(FCore $plugin) {
        $this->plugin = $plugin;
        parent::__construct("lobby", "Lobby command", null, ["spawn", "hub"]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if($sender instanceof Player) {
            $sender->sendMessage(FCore::getPrefix()."§aYou are transferred to the §9Lobby #1§a!");
            $this->plugin->scheduleMgr->runJoinTask($sender, false, false);
        }
    }

}