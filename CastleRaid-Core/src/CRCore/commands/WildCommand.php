<?php

declare(strict_types=1);

namespace CRCore\commands;

use CRCore\core\Loader;
use CRCore\core\api\API;
use CRCore\commands\BaseCommand;
use pocketmine\command\{
    Command, CommandSender, PluginCommand
};
use pocketmine\level\{
    Level, Position
};
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;

class WildCommand extends BaseCommand{

    /** @var Core */
    private $plugin;
            public function __construct(Loader $plugin){
        parent::__construct($plugin, "wild", "Teleport to wilderness", "/wild", ["wild"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
        if(!$sender instanceof Player) return false;
        $x = mt_rand(1, 999);
        $z = mt_rand(1, 999);
        $y = $sender->getLevel()->getHighestBlockAt($x, $z) + 1;
        $sender->teleport(new Position($x, $y, $z, $sender->getLevel()));
        $sender->sendMessage(TF::GREEN . "§8-=§bChrystal§fPE§r§8=- §bYou have been teleported to a random spot in the wilderness!");
        $sender->addTitle("§8-=§bChrystal§fPE§r§8=- §bTeleporting...");
        return true;
    }
}
