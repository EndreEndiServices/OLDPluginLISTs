<?php
/**
 * -==+CastleRaid Core+==-
 * Originally Created by QuiverlyRivarly
 * Originally Created for CastleRaidPE
 *
 * @authors: CastleRaid Developer Team
 */
declare(strict_types=1);

namespace CRCore\commands;

use CRCore\core\api\API;
use CRCore\commands\BaseCommand;
use CRCore\core\Loader;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class FlyCommand extends BaseCommand{

    public function __construct(Loader $plugin){
        parent::__construct($plugin, "fly", "Flight mode", "/fly", ["fly"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender instanceof Player){
            $sender->sendMessage(API::NOT_PLAYER);
            return false;
        }
        if(!$sender->hasPermission("castleraid.fly")){
            $sender->sendMessage(API::NO_PERM);
            return false;
        }
        if(!$sender->isCreative()){
            if(!$sender->getAllowFlight()){
                $sender->setAllowFlight(true);
                $sender->setFlying(true);
                $sender->sendMessage(TextFormat::GREEN . "§8-=§bChrystal§fPE§r§8=- §bFly mode enabled.");
            }else{
                $sender->setAllowFlight(false);
                $sender->setFlying(false);
                $sender->sendMessage(TextFormat::RED . "§8-=§bChrystal§fPE§r§8=- §bFly mode disabled.");
            }
        }else{
            $sender->sendMessage(TextFormat::RED . "§8-=§bChrystal§fPE§r§8=- §bYou are already in creative mode!");
        }
        return true;
    }
}
