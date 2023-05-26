<?php

namespace TreasureHunt;

use pocketmine\event\Listener;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\inventory\ChestInventory;
use TreasureHunt\TreasureHunt;
use LbCore\player\LbPlayer;
use LbCore\LbCore;

class EventListener implements Listener {
	
	private $teeShirt;

	public function __construct() {
		$this->teeShirt = TreasureHunt::getInstance();
	}

	public function onInventoryOpen(InventoryOpenEvent $event) {
		$player = $event->getPlayer();
		
		$playerName = strtolower($player->getName());
		$playerIsLbsgStaff = in_array($playerName, LbCore::$lbsgStaffNames);
		
		if($player instanceof LbPlayer && ($player->countryIsoCode == 'US' || $playerIsLbsgStaff)){
			$inv = $event->getInventory();
			if ($inv instanceof ChestInventory) {
				$holder = $inv->getHolder();
				$x = $holder->getX();
				$y = $holder->getY();
				$z = $holder->getZ();
				//collect statistics by this chest for db
				$coordsString = "{$x}:{$y}:{$z}";
				
				if (isset(TreasureHunt::$chestsStat[$coordsString])) {
					TreasureHunt::$chestsStat[$coordsString] += 1;
				} else {
					TreasureHunt::$chestsStat[$coordsString] = 1;
				}
				$teeShirtChest = TreasureHunt::getTeeShirtChest();
				if (is_null($teeShirtChest)) {
					return;
				}
				$coords = explode(':', $teeShirtChest->coords);
				if($coords[0] == $x && $coords[1] == $y && $coords[2] == $z){
					if ($playerIsLbsgStaff && !$this->teeShirt->isAdminWinnerAllowed()) {
						$player->sendMessage('Yes, the prize is here. Just don\'t tell anyone');
					} else {
						$this->teeShirt->addWinners($player, $x, $y, $z);
					}
				}
			}
		}
	}
	
}
