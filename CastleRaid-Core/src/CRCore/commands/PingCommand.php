<?php

namespace CRCore\commands;

use pocketmine\Player;
use CRCore\core\api\API;
use pocketmine\Server;

use pocketmine\command\CommandSender;
use CRCore\commands\BaseCommand;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginCommand;

use pocketmine\utils\TextFormat as C;

use CRCore\core\Loader;

class PingCommand extends BaseCommand{


    public function __construct(Loader $plugin){
        parent::__construct($plugin, "ping", "Show your ping", "/ping", ["ms, latency"]);
    }
     
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
                    if($sender instanceof Player){
                        $sender->sendMessage(C::GREEN . "§8-=§bChrystal§fPE§r§8=- §bPing: " . $sender->getPing() . "ms");
                                }else{
          $sender->sendMessage(C::RED . "You are not In-Game.");
        }
            return true;
    }
}
