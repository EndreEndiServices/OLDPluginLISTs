<?php

namespace CRCore\commands;

use pocketmine\Player;
use CRCore\core\api\API;
use pocketmine\Server;

use pocketmine\command\CommandSender;
use CRCore\commands\BaseCommand;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginCommand;

use pocketmine\level\Level;

use pocketmine\utils\TextFormat as C;

use CRCore\core\Loader;

class SetSpawnCommand extends BaseCommand{

        public function __construct(Loader $plugin){
        parent::__construct($plugin, "setspawn", "Nick command", "/setspawn", ["setspawn"]);
    }
     
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
                    if($sender instanceof Player){
                    if (!$sender->hasPermission("core.setspawn")) {
                        $sender->sendMessage(API::NO_PERM);
                        return false;
                    }
                        $sender->getLevel()->setSpawnLocation($sender);
                        $sender->getServer()->setDefaultLevel($sender->getLevel());
                        $sender->sendMessage(C::GREEN . "§8-=§bChrystal§fPE§r§8=- §bSuccess! Spawn Set!");
        }else{
          $sender->sendMessage(Core::USE_IN_GAME);
        }
            return true;
    }
}
