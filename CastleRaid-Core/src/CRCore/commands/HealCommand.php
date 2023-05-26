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

use CRCore\core\Loader;
use CRCore\core\api\API;
use CRCore\commands\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class HealCommand extends BaseCommand{

    public function __construct(Loader $plugin){
        parent::__construct($plugin, "heal", "Heals a player", "/heal", ["heal"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender instanceof Player){
            $sender->sendMessage(API::NOT_PLAYER);
            return false;
        }
        if(!$sender->hasPermission("castleraid.heal")){
            $sender->sendMessage(API::NO_PERM);
            return false;
        }
        $sender->setHealth(20);
        $sender->sendMessage(TextFormat::GREEN . "§8-=§bChrystal§fPE§r§8=- §bYou have been healed!");
        $sender->addTitle(TextFormat::RED . "§8-=§bChrystal§fPE§r§8=- §bYou have been healed!");
        return true;
    }
}
