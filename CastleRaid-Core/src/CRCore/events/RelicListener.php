<?php
/**
 * -==+CastleRaid Core+==-
 * Originally Created by QuiverlyRivarly
 * Originally Created for CastleRaidPE
 *
 * @authors: CastleRaid Developer Team
 */
declare(strict_types=1);

namespace CRCore\events;

use CRCore\core\Loader;
use CRCore\core\api\API;
use pocketmine\block\Stone;
use onebone\economyapi\EconomyAPI;
use pocketmine\block\Iron_ore;
use pocketmine\block\Diamond_ore;
use pocketmine\block\Coal_ore;
use pocketmine\block\Gold_ore;
use pocketmine\block\Redstone_ore;
use pocketmine\block\Emerald_ore;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\utils\TextFormat;

class RelicListener implements Listener{

    private $main;

    public function __construct(Loader $main){
        $this->main = $main;
        $main->getServer()->getPluginManager()->registerEvents($this, $main);
    }

    public function onBreak(BlockBreakEvent $event) : void{
        $nbt = new CompoundTag("", [new IntTag("relic", 1)]);
        $loot = Item::get(Item::NETHER_STAR)->setCustomName("Relic")->setCustomBlockData($nbt);
        $loot->setCustomName("Relic");
        $player = $event->getPlayer();
        if($event->getBlock()->getId() == Stone::STONE){
            if(mt_rand(1, 100) <= 8){
                $player->getInventory()->addItem($loot);
            }
        }
    }

    public function onClick(PlayerInteractEvent $event) : void{
        $item = $event->getItem();
        $player = $event->getPlayer();
        $inv = $player->getInventory();
        if($item->getId() !== Item::NETHER_STAR) return;
        if(!$item->hasCustomName() || !$item->hasCustomBlockData()) return;
        if($item->getCustomBlockData()->getInt("relic") !== 1) return;
        $inv->removeItem($item);
        $player->sendMessage(API::PREFIX . TextFormat::GREEN . "Opening your Relic...");
        $player->sendMessage(API::PREFIX . TextFormat::GREEN . "Opened!");
        $inv->addItem(Item::get(Item::DIAMOND_BLOCK));
        $inv->addItem(Item::get(Item::IRON_BLOCK));
        $inv->addItem(Item::get(Item::GOLD_BLOCK));
        $inv->addItem(Item::get(Item::REDSTONE_BLOCK));
        $inv->addItem(Item::get(Item::COAL_BLOCK));
        EconomyAPI::getInstance()->addMoney($player, 5000);
    }
}