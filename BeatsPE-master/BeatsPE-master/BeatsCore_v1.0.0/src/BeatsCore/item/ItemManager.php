<?php

declare(strict_types = 1);

namespace BeatsCore\item;

use pocketmine\item\{Item, ItemFactory};

class ItemManager{

	public static function init(){
		ItemFactory::registerItem(new EnderPearl(), true);
		
		Item::initCreativeItems();
	}
}