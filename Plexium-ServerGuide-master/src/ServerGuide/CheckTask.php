<?php
namespace ServerGuide;

use pocketmine\tile\Container;

use pocketmine\scheduler\PluginTask;

class CheckTask extends PluginTask{
	
	public function __construct(ServerGuide $plugin){
	    parent::__construct($plugin);
	}
	
	public function onRun($tick){
		 $item = $this->getOwner()->getHelpItem();
	    foreach($this->getOwner()->getServer()->getOnlinePlayers() as $player){
	      if(!$player->getInventory()->contains($item)){
		     $player->getInventory()->addItem($item);
		   }
	    }
	    $check = clone $item; # Make sure not messing up the count
	    $check->setCount(2);
	    foreach($this->getOwner()->getServer()->getOnlinePlayers() as $player){
	      if($player->getInventory()->contains($check)){
		     $player->getInventory()->removeItem($item);
		   }
	    }
	    # Prevent duping on containers
	    foreach($this->getOwner()->getServer()->getLevels() as $level){
	       foreach($level->getTiles() as $tile){
	         if($tile instanceof Container){
		        if($tile->getInventory()->contains($item)){
			       $tile->getInventory()->removeItem($item);
			     }
		      }
	       }
	    }
	}
}