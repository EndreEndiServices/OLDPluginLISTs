<?php 

namespace YEnchantPE;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\inventory\PlayerInventory;
use pocketmine\item\Item;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;


class Main extends PluginBase implements Listener{

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getLogger()->info("Плагин успешно был включен!");
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
		switch($cmd->getName()){
			case "enchant":
			if($sender->getInventory()->getItemInHand()->getId() != 0){
				$item = $sender->getInventory()->getItemInHand();
				$item->addEnchantment(Enchantment::getEnchantment(1)->setLevel(1000)); 
				$item->addEnchantment(Enchantment::getEnchantment(2)->setLevel(1000));
				$item->addEnchantment(Enchantment::getEnchantment(3)->setLevel(1000));
				$item->addEnchantment(Enchantment::getEnchantment(4)->setLevel(1000));
				$item->addEnchantment(Enchantment::getEnchantment(5)->setLevel(1000));
				$item->addEnchantment(Enchantment::getEnchantment(6)->setLevel(1000));
				$item->addEnchantment(Enchantment::getEnchantment(7)->setLevel(1000));
				$item->addEnchantment(Enchantment::getEnchantment(8)->setLevel(1000));
				$item->addEnchantment(Enchantment::getEnchantment(9)->setLevel(1000));
				$item->addEnchantment(Enchantment::getEnchantment(10)->setLevel(1000));
				$item->addEnchantment(Enchantment::getEnchantment(11)->setLevel(1000));
				$item->addEnchantment(Enchantment::getEnchantment(12)->setLevel(1000));
				$item->addEnchantment(Enchantment::getEnchantment(13)->setLevel(1000));
                $item->addEnchantment(Enchantment::getEnchantment(14)->setLevel(1200));
				$item->addEnchantment(Enchantment::getEnchantment(15)->setLevel(1000));
                $item->addEnchantment(Enchantment::getEnchantment(16)->setLevel(1000));
                $item->addEnchantment(Enchantment::getEnchantment(17)->setLevel(1000));
                $item->addEnchantment(Enchantment::getEnchantment(18)->setLevel(1000));
                $item->addEnchantment(Enchantment::getEnchantment(19)->setLevel(1000));
                $item->addEnchantment(Enchantment::getEnchantment(20)->setLevel(1000));
                $item->addEnchantment(Enchantment::getEnchantment(21)->setLevel(1000));
                $item->addEnchantment(Enchantment::getEnchantment(22)->setLevel(1000));
                $item->addEnchantment(Enchantment::getEnchantment(23)->setLevel(1000));
                $item->addEnchantment(Enchantment::getEnchantment(24)->setLevel(1000));
				$sender->getInventory()->removeItem($item);
				$sender->getInventory()->addItem($item);
				$sender->sendMessage("§fПредмет был зачарован на 1000 уровень");
				}else{
					$sender->sendMessage("§сОшибка, возьмите предмет в руку!");
				}
		}
	}
}