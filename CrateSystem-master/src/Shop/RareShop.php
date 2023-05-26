<?php

namespace Shop;

use pocketmine\Player;

use pocketmine\utils\Config;

use pocketmine\level\Level;
use pocketmine\level\sound\EndermanTeleportSound;

use pocketmine\inventory\Inventory;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;

use pocketmine\math\Vector3;

use pocketmine\plugin\Plugin;

use pocketmine\utils\Textformat as C;

use CrateSystem\Main;

class RareShop{

    private $plugin;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    public function Start($sender){

        $this->Rare = new Config($this->plugin->getDataFolder() . "Shop.yml", Config::YAML);

        $money = $this->plugin->ecoapi->myMoney($sender);
        $hasmoneycfg = $this->Rare->getNested("Rare.cost");
        $takemoney = $this->plugin->ecoapi->reduceMoney($sender, $hasmoneycfg);

        $e = Enchantment::getEnchantment((int) 0);

        #Sound
        $level = $sender->getLevel();
        $x = $sender->getX();
        $y = $sender->getY();
        $z = $sender->getZ();
        $pos1 = new Vector3($x, $y, $z);

        if($money >= $hasmoneycfg){

            $rareitem = Item::get(131,3,1);
            $rareitem->addEnchantment(new EnchantmentInstance($e, (int) -0));
            $rareitem->setCustomName("§6Rare");
            $sender->getInventory()->addItem($rareitem);

            $level->addSound(new EndermanTeleportSound($pos1));

            $sender->sendMessage(C::WHITE . "You bough crate: " . C::GOLD . "Rare " . C::AQUA . "for " . C::YELLOW . "$" . $hasmoneycfg);
        }else{
            $sender->sendMessage(C::RED . "You don't have enough money to buy " . C::GOLD . "Rare Crate");
            $sender->sendMessage(C::AQUA . "You only have: " . C::YELLOW . "$" . $money);
            $sender->sendMessage(C::WHITE . "You needed: " . C::YELLOW . "$" . $hasmoneycfg);
        }
    }
}