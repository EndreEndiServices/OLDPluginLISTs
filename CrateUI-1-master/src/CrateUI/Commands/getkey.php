<?php

namespace CrateUI\Commands;

use CrateUI\Main;
use pocketmine\utils\Config;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\item\Item;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\level\Level;
use pocketmine\Server;
use pocketmine\Player;

class getkey extends Command{

    public function __construct($name){
         parent::__construct(
        $name
       );
	$this->setDescription("get crate key");
	$this->setAliases(["key"]);
	$this->setPermission("crate.key");
    }

    public function execute(CommandSender $sender, string $label, array $args){
        if (!$this->testPermission($sender)){
        return true;
        }
        $inv = $sender->getInventory();
        $commonname = Item::get(131,1,1);
        $votename = Item::get(131,2,1);
        $rarename = Item::get(131,3,1);
        $mythicname = Item::get(131,4,1);
        $legendaryname = Item::get(131,5,1);
        $e = Enchantment::getEnchantment(0);
        if (count($args) < 1){
            $sender->sendMessage("§b===>§eKeys§b<===");
            $sender->sendMessage("§a/getkey Common §e: §bGet Common key.");
            $sender->sendMessage("§c/getkey Vote §e: §bGet Vote key.");
            $sender->sendMessage("§6/getkey Rare §e: §bGet Rare key.");
            $sender->sendMessage("§6/getkey Mythic §e: §bGet Mythic key.");
            $sender->sendMessage("§b/getkey Legendary §9: §bGet Legendary key.");
            $sender->sendMessage("§b===>§eKeys§b<===");
            return false;
        }
        switch ($args[0]){
            case "1":
            case "common":
            case "Common":
            $e->setLevel(-1);
            $commonname->addEnchantment($e);
            $commonname->setCustomName("§aCommon");
            $inv->addItem($commonname);
            $sender->sendMessage("§eYou receive §aCommon §eKey.");
            break;
            case "2":
            case "vote":
            case "Vote":
            $e->setLevel(-1);
            $votename->addEnchantment($e);
            $votename->setCustomName("§cVote");
            $inv->addItem($votename);
            $sender->sendMessage("§eYou receive §cVote §eKey.");
            break;
            case "3":
            case "rare":
            case "Rare":
            $e->setLevel(-1);
            $rarename->addEnchantment($e);
            $rarename->setCustomName("§6Rare");
            $inv->addItem($rarename);
            $sender->sendMessage("§eYou receive §6Rare §eKey.");
            break;
            case "4":
            case "mythic":
            case "Mythic":
            $e->setLevel(-1);
            $mythicname->addEnchantment($e);
            $mythicname->setCustomName("§5Mythic");
            $inv->addItem($mythicname);
            $sender->sendMessage("§eYou receive §5Mythic §eKey.");
            break;
            case "5":
            case "legendary":
            case "Legendary":
            $e->setLevel(-1);
            $legendaryname->addEnchantment($e);
            $legendaryname->setCustomName("§9Legendary");
            $inv->addItem($legendaryname);
            $sender->sendMessage("§eYou receive §9Legendary §eKey.");
        }
    }
}
