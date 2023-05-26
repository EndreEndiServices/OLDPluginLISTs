<?php

declare(strict_types=1);

namespace BeatsCore\commands;

use BeatsCore\Core;
use pocketmine\command\{
    Command, CommandSender, PluginCommand
};
use pocketmine\level\{
    Level, Position
};
use pocketmine\Player;

class WildCommand extends PluginCommand{

    /** @var Core */
    private $plugin;

    public function __construct($name, Core $plugin){
        parent::__construct($name, $plugin);
        $this->setDescription("Teleport to the wild");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
        if(!$sender instanceof Player) return false;
        $x = mt_rand(1, 999);
        $z = mt_rand(1, 999);
        $y = $sender->getLevel()->getHighestBlockAt($x, $z) + 1;
        $sender->teleport(new Position($x, $y, $z, $sender->getLevel()));
        $sender->sendMessage("§l§a(!)§r §aYou have been teleported to a random spot in the wilderness!");
        $sender->addTitle("§aTeleporting...");
        return true;
    }
}