<?php

declare(strict_types=1);

namespace BeatsCore\items;

use pocketmine\item\{
	Item, ItemFactory
};

class ItemManager{

	public static function start() : void{
		ItemFactory::registerItem(new EnderPearl(), true);
		Item::addCreativeItem(Item::get(Item::ENDER_PEARL));
	}
}