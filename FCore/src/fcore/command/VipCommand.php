<?php

declare(strict_types=1);

namespace fcore\command;

use fcore\FCore;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

/**
 * Class VipCommand
 * @package fcore\command
 */
class VipCommand extends Command {

    public $plugin;

    public function __construct(FCore $plugin) {
        $this->plugin = $plugin;
        parent::__construct("vip", "Info about vip", null, []);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        $sender->sendMessage("§7----- === [ §6VIP §7] === -----\n".
            "§eYou can buy it on our website\n".
            "§6http://factionpe.tk §e.\n".
            "§eIf you bought VIP, you will get\n".
            "§6All gadgets, kits and particles,\n".
            "§6You will able to join full server.\n".
            "§6You will get 10.000 coins");
    }

}