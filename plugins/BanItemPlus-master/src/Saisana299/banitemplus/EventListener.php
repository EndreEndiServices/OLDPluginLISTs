<?php

namespace Saisana299\banitemplus;

use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\utils\Config;

class EventListener implements Listener {

    private $BanItemPlus;
        
    public function __construct(BanItemPlus $BanItemPlus)
    {
        $this->BanItemPlus = $BanItemPlus;
    }

    public function onInteract(PlayerInteractEvent $event) {
        if ($event->isCancelled()){
            return;
        }
        $player = $event->getPlayer();
        if($player->isOp()){
        	return;
        }
        $worldname = $player->getLevel()->getFolderName();
        $id = $event->getItem()->getId();
        $damage = $event->getItem()->getDamage();
        if($this->BanItemPlus->banned->exists($id.":".$damage)) {
            $worlds = $this->BanItemPlus->banned->getAll()[$id.":".$damage]["whiteworlds"];
            if(in_array($worldname, $worlds)){
                return true;
            }
            $player->sendPopup("§eこのアイテムの使用は禁止されています");
            $event->setCancelled();
        }elseif($this->BanItemPlus->banned->exists($id)){
        	$worlds = $this->BanItemPlus->banned->getAll()[$id]["whiteworlds"];
            if(in_array($worldname, $worlds)){
                return true;
            }
            $player->sendPopup("§eこのアイテムの使用は禁止されています");
            $event->setCancelled();
        }
    }

    public function onPlace(BlockPlaceEvent $event) {
        if ($event->isCancelled()){
            return;
        }
        $player = $event->getPlayer();
        if($player->isOp()){
        	return;
        }
        $worldname = $player->getLevel()->getFolderName();
        $id = $event->getItem()->getId();
        $damage = $event->getItem()->getDamage();
        if($this->BanItemPlus->banned->exists($id.":".$damage)) {
            $worlds = $this->BanItemPlus->banned->getAll()[$id.":".$damage]["whiteworlds"];
            if(in_array($worldname, $worlds)){
                return true;
            }
            $event->setCancelled();
        }elseif($this->BanItemPlus->banned->exists($id)){
        	$worlds = $this->BanItemPlus->banned->getAll()[$id]["whiteworlds"];
            if(in_array($worldname, $worlds)){
                return true;
            }
            $event->setCancelled();
        }
    }

    public function onEat(PlayerItemConsumeEvent $event) {
        if ($event->isCancelled()){
            return;
        }
        $player = $event->getPlayer();
        if($player->isOp()){
        	return;
        }
        $worldname = $player->getLevel()->getFolderName();
        $id = $event->getItem()->getId();
        $damage = $event->getItem()->getDamage();
        if($this->BanItemPlus->banned->exists($id.":".$damage)) {
            $worlds = $this->BanItemPlus->banned->getAll()[$id.":".$damage]["whiteworlds"];
            if(in_array($worldname, $worlds)){
                return true;
            }
            $event->setCancelled();
        }elseif($this->BanItemPlus->banned->exists($id)){
        	$worlds = $this->BanItemPlus->banned->getAll()[$id]["whiteworlds"];
            if(in_array($worldname, $worlds)){
                return true;
            }
            $event->setCancelled();
        }
    }
}
