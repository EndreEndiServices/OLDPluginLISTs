<?php

namespace AuctionHouse\Chest;

use pocketmine\inventory\{ChestInventory, InventoryType};
use pocketmine\Player;
use pocketmine\block\Block;

class CustomChestInventory extends ChestInventory{

	public function onOpen(Player $who) : void{
		parent::onOpen($who);
	}

	public function onClose(Player $who) : void{
		$this->holder->sendReplacement($who);
		parent::onClose($who);
		unset(\AuctionHouse\Main::getInstance()->clicks[$who->getId()]);
		$this->holder->close();
	}
	
}
