<?php

declare(strict_types=1);

namespace fcore\pvp\kit;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\Player;

class WarriorKit implements Kit {

    /**
     * @return string
     */
    public function getName(): string {
        return "Warrior";
    }

    /**
     * @return string
     */
    public function getImage(): string {
        return "http://icons.iconarchive.com/icons/chrisl21/minecraft/256/Diamond-Axe-icon.png";
    }

    /**
     * @return int
     */
    public function getCost(): int {
        return 500;
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
        $inv->setItem(0, $this->addSharpness(Item::get(Item::DIAMOND_AXE)));
        $inv->setItem(8, Item::get(Item::APPLE, 0, 64));
    }

	private function addSharpness(Item $item): Item {
		$e = Enchantment::getEnchantment(Enchantment::SHARPNESS);
		$item->addEnchantment(new EnchantmentInstance($e, 2));
		return $item;
	}

	private function addProtection(Item $item): Item {
		$e = Enchantment::getEnchantment(Enchantment::PROTECTION);
		$item->addEnchantment(new EnchantmentInstance($e, 1));
		return $item;
	}
}