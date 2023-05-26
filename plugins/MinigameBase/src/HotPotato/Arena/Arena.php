<?php

namespace HotPotato\Arena;

use HotPotato\HotPotato;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class Arena extends Manager implements Listener{

    public $plugin;
    public $players = [];
    public $game = 0;
    public $id;

    public function __construct(HotPotato $plugin, $id){
        parent::__construct($this);
        $this->plugin = $plugin;
        $this->id = $id;
        $this->data = $this->getOwner()->data[$this->getId()];
    }

    public function onBlockTouch(PlayerInteractEvent $e){
        $p = $e->getPlayer();
        if($this->inArena($p)){
            return;
        }
        if($this->isArenaFull()){
            $p->sendMessage($this->getPrefix().TextFormat::RED."Arena is full");
            return;
        }
    }

    public function onBlockBreak(BlockBreakEvent $e){

    }

    public function onBlockPlace(BlockPlaceEvent $e){

    }

    public function onDamage(EntityDamageEvent $e){

    }

    public function onQuit(PlayerQuitEvent $e){
        
    }

    public function startGame(){

    }

    public function stopGame(){

    }

    public function checkAlive(){

    }

    public function joinToArena(Player $p){

    }

    public function leaveArena(Player $p){

    }
}