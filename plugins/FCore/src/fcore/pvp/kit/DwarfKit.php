<?php

declare(strict_types=1);

namespace fcore\pvp\kit;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\Player;

class DwarfKit implements Kit {

    /**
     * @return string
     */
    public function getName(): string {
        return "Dwarf";
    }

    /**
     * @return string
     */
    public function getImage(): string {
        return "http://icons.iconarchive.com/icons/chrisl21/minecraft/256/Wooden-Sword-icon.png";
    }

    /**
     * @return int
     */
    public function getCost(): int {
        return 2000;
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
        $inv->clearAll();
        $inv->setBoots($this->addProtection(Item::get(Item::GOLD_BOOTS)));
        $inv->setLeggings($this->addProtection(Item::get(Item::GOLD_LEGGINGS)));
        $inv->setChestplate($this->addProtection(Item::get(Item::IRON_CHESTPLATE)));
        $inv->setHelmet($this->addProtection(Item::get(Item::GOLD_HELMET)));
        $inv->setItem(0, Item::get(Item::STONE_SWORD));
        $inv->setItem(8, Item::get(Item::APPLE, 0, 64));
        $player->setScale(0.4);
    }

    private function addProtection(Item $item): Item {
        $e = new Enchantment(Enchantment::PROTECTION, "Protection", 1, 0, 1);
        $e->setLevel(1);
        $item->addEnchantment($e);
        return $item;
    }
}