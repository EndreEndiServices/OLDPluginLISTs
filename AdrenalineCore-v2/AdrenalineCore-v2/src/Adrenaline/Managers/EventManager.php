<?php

namespace Adrenaline\Managers;

use Adrenaline\CoreLoader;
use Adrenaline\Tasks\TitleTask;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\Item;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\utils\UUID;

class EventManager implements Listener {

    public $plugin;
    private $timer = [];

    public function __construct(CoreLoader $plugin) {
        $this->plugin = $plugin;
    }

    public function onJoin(PlayerJoinEvent $event) {
        $event->setJoinMessage("");
        //$this->plugin->getServer()->getScheduler()->scheduleDelayedTask(new TitleTask($this->plugin, $event->getPlayer()), 16);
    }


    public function onChat(PlayerChatEvent $event) {
        if (!$event->getPlayer()->isOp() or !$event->getPlayer()->hasPermission("core.bypass")) {
            $name = strtolower($event->getPlayer()->getDisplayName());
            if (!isset($this->timer[$name]) or time() > $this->timer[$name]) {
                $this->timer[$name] = time() + 4;//The 4 is the cooldown time.
            } else {
                $event->getPlayer()->sendMessage($this->plugin->sendPrefix() . "Please wait " . round($this->timer[$name] - time()) . " more seconds to chat again!");
                $event->setCancelled();
            }
        }
    }

    public function onBuild(BlockPlaceEvent $event) {
        if ($event->getPlayer()->getLevel()->getFolderName() === "world") {
            if (!$event->getPlayer()->isOp() or !$event->getPlayer()->hasPermission("core.bypass")) {
                $event->setCancelled();
            }
        }
    }

    public function onBreak(BlockBreakEvent $event) {
        if ($event->getPlayer()->getLevel()->getFolderName() === "world") {
            if (!$event->getPlayer()->isOp() or !$event->getPlayer()->hasPermission("core.bypass")) {
                $event->setCancelled();
            }
        }
    }

}