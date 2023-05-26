<?php

declare(strict_types=1);

namespace fcore\pvp\kit;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\Player;

class BomberKit implements Kit {

    /**
     * @return string
     */
    public function getName(): string {
        return "Bomber";
    }

    /**
     * @return string
     */
    public function getImage(): string {
        return "http://icons.iconarchive.com/icons/chrisl21/minecraft/256/Tnt-icon.png";
    }

    /**
     * @return int
     */
    public function getCost(): int {
        return 1000;
    }

    /**
     * @return bool
     */
    public function isOnlyForVip(): bool {
        return false;
    }

    /**
     * @return bool
     */
    public function isFree(): bool {
        return false;
    }

    public function equip(Player $player) {
        $inv = $player->getInventory();
        $env = $player->getArmorInventory();
        $inv->clearAll();
        $env->setBoots($this->addProtection(Item::get(Item::GOLD_BOOTS)));
        $env->setLeggings($this->addProtection(Item::get(Item::GOLD_LEGGINGS)));
        $env->setChestplate($this->addProtection(Item::get(Item::IRON_CHESTPLATE)));
        $env->setHelmet($this->addProtection(Item::get(Item::GOLD_HELMET)));
        $inv->setItem(0, Item::get(Item::STONE_SWORD));
        $inv->setItem(1, Item::get(Item::TNT, 0, 24));
        $inv->setItem(8, Item::get(Item::APPLE, 0, 64));
    }

	private function addProtection(Item $item): Item {
		$e = Enchantment::getEnchantment(Enchantment::PROTECTION);
		$item->addEnchantment(new EnchantmentInstance($e, 1));
		return $item;
	}
}