<?php
namespace PrisonCore;

use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\inventory\Inventory;

use pocketmine\network\protocol\SetPlayerGameTypePacket;

class BasicLib{
	 
	 /* @param disable fly function */
	
	public static function disableFlight(Player $player){
	     $player->setAllowFlight(false);
	     $pk = new SetPlayerGameTypePacket();
	     $pk->gamemode = $player->gamemode & 0x01;
	     $player->dataPacket($pk);
	     $player->setFlying(false);
	     $player->sendSettings();
		}
		
	/* @param customGive */
	
	public static function customGive(Player $player, Item $item, $customname){
	     if(isset($customname)){
		    $item->setCustomName($customname);
		  }
		  $player->getInventory()->addItem($item);
    }

	
	/* @param send custom tip */
	
	public static function sendCustomTip(Player $player, String $tip, int $height){
	    if(is_numeric($height)){
		    $tmp = str_repeat("\n", $height);
		    $player->sendPopup($tip.$height);
		    }
		}
}