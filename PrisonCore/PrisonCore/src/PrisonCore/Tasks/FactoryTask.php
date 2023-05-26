<?php
namespace PrisonCore\Tasks;

use pocketmine\Payer;
use pocketmine\Server;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\scheduler\PluginTask;
use PrisonCore\Core;
use onebone\economyapi\EconomyAPI;

class FactoryTask extends PluginTask{
	 
	 const TIME = 60;
	 protected $current = -1;
	
    public function __construct(Core $plugin){
         $this->plugin = $plugin;
         $this->cfg = $plugin->cfg;
         $this->max = $this->cfg["deposit.max.money"];
         $this->min = $this->cfg["deposit.min.money"];
         $this->depositTime = $this->cfg["deposit.time"];
         $this->prefix = $plugin->prefix;
         $this->deposit = $this->cfg["deposit.item"];
         parent::__construct($plugin);
	  }
	 public function getCore(){
	      return $this->plugin;
		}
	 public function onRun($tick){
		 $this->getCore()->factory->reload();
	    $this->current++;
	    if($this->current > $this->depositTime){
		   $this->current = -1;
		  }
		if(count($this->getCore()->getServer()->getOnlinePlayers()) !== 0){
	    foreach($this->getCore()->getServer()->getOnlinePlayers() as $players){
		  $player = $this->getCore()->getServer()->getPlayer($players->getName());
		   if($this->getCore()->factoryExists($player)){
			  $seconds = $this->depositTime - $this->current;
			  if($this->cfg["show.factory.popup"] == "true"){
			    $player->sendPopup("§l§a[FactoryManager] §8››§r§7 Next deposit in ".$seconds." seconds\n\n\n");
			   }
			  if($this->current == $this->depositTime){
				  $this->getCore()->giveFactoryIncome($player);
				//$level = $this->getCore()->getFactoryLevel($player);
				//$itemdata = explode(":", $this->deposit);
				//$item = Item::get($itemdata[0], $itemdata[1], $level);
				//$player->getInventory()->addItem($item);
				//$money = rand($this->min, $this->max * $level);
				//EconomyAPI::getInstance()->addMoney($player, $money);
				//$player->sendMessage("§a§l[FactoryManager]§8 ›› §r§7Successfully recieved a diposit!");
				//$player->sendMessage("§a§l[FactoryManager]§8 ›› §r§7Your factory has made ".$money."$ this session!");
			   $this->current = -1;
			}
       }
	}
}
}
}