<?php

declare(strict_types=1);

namespace fcore\pvp\kit;

use fcore\FCore;
use fcore\profile\ProfileManager;
use pocketmine\inventory\PlayerInventory;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\Player;

class VipKit implements Kit {

    /**
     * @return string
     */
    public function getName(): string {
        return "Vip";
    }

    /**
     * @return string
     */
    public function getImage(): string {
        return "http://icons.iconarchive.com/icons/chrisl21/minecraft/256/Diamond-Sword-icon.png";
    }

    /**
     * @return int
     */
    public function getCost(): int {
        return 0;
    }

    /**
     * @return bool
     */
    public function isOnlyForVip(): bool {
        return true;
    }

    /**
     * @return bool
     */
    public function isFree(): bool {
        return false;
    }

    public function equip(Player $player) {
        if(!ProfileManager::isVip($player)) {
            $player->sendMessage("§cThis kit is only for VIP!");
            FCore::$instance->scheduleMgr->runJoinTask($player, false, false);
            return;
        }
        /** @var PlayerInventory $inv */
        $inv = $player->getInventory();
        $inv->clearAll();
        $inv->setBoots($this->addProtection(Item::get(Item::GOLD_BOOTS)));
        $inv->setLeggings($this->addProtection(Item::get(Item::GOLD_LEGGINGS)));
        $inv->setChestplate($this->addProtection(Item::get(Item::IRON_CHESTPLATE)));
        $inv->setHelmet($this->addProtection(Item::get(Item::GOLD_HELMET)));
        $inv->setItem(0, Item::get(Item::DIAMOND_AXE));
        $inv->setItem(8, Item::get(Item::APPLE, 0, 64));
    }

    private function addProtection(Item $item): Item {
        $e = new Enchantment(Enchantment::PROTECTION, "Protection", 1, 0, 1);
        $e->setLevel(1);
        $item->addEnchantment($e);
        return $item;
    }
}