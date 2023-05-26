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

use CRCore\commands\BaseCommand;
use CRCore\core\Loader;
use CRCore\core\api\API;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class ClearInventoryCommand extends BaseCommand{

    public function __construct(Loader $plugin){
        parent::__construct($plugin, "clearinv", "Clear your inventory", "/clearinv", ["clearinv"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender instanceof Player){
            $sender->sendMessage(API::NOT_PLAYER);
            return false;
        }
        if(!$sender->hasPermission("castleraid.clearinv")){
            $sender->sendMessage(API::NO_PERM);
            return false;
        }
        $sender->getInventory()->clearAll();
        $sender->sendMessage(TextFormat::GREEN . "§8-=§bChrystal§fPE§r§8=- §bYour inventory has been cleared!");
        $sender->addTitle(TextFormat::GREEN . "§8-=§bChrystal§fPE§r§8=- §bInventory cleared!");
        return true;
    }
}
