<?php

namespace CRCore\commands;

use pocketmine\Player;
use CRCore\core\api\API;
use pocketmine\Server;
use pocketmine\command\CommandSender;
use CRCore\commands\BaseCommand;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\utils\TextFormat;
use CRCore\core\Loader;
use pocketmine\math\Vector3;
use pocketmine\level\sound\EndermanTeleportSound;
class SpawnCommand extends BaseCommand{

    public function __construct(Loader $plugin){
        parent::__construct($plugin, "spawn", "Spawn command", "/spawn", ["spawn"]);
    }
     
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
                    if($sender instanceof Player){
                        $level = $sender->getLevel();
                        $x = $sender->getX();
                        $y = $sender->getY();
                        $z = $sender->getZ();
                        $spawn = new Vector3($x, $y, $z);
                        $sender->sendMessage(API::PREFIX . TextFormat::GREEN . "§8-=§bChrystal§fPE§r§8=- §bTeleporting to Spawn.");
                        $sender->addTitle("§8-=§bChrystal§fPE§r§8=- §bTeleporting...");
                        $sender->teleport($this->getPlugin()->getServer()->getDefaultLevel()->getSafeSpawn());
                        $level->addSound(new EndermanTeleportSound($spawn));
        }else{
          $sender->sendMessage(TextFormat::RED . "You are not In-Game.");
        }
            return true;
    }
}
