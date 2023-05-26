<?php

namespace HotPotato\Arena;

use HotPotato\HotPotato;
use pocketmine\entity\Entity;
use pocketmine\entity\PrimedTNT;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\level\Location;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Float;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class Arena extends Manager implements Listener{

    public $plugin;
    public $players = [];
    public $holders = [];
    public $game = 0;
    public $id;
    public $level;

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
        $this->joinToArena($p);
    }

    public function onBlockBreak(BlockBreakEvent $e){
        if(!$e->getPlayer()->isOp()){
            $e->setCancelled();
        }
    }

    public function onBlockPlace(BlockPlaceEvent $e){
        if(!$e->getPlayer()->isOp()){
            $e->setCancelled();
        }
    }

    public function onDamage(EntityDamageEvent $e){
        $e->setCancelled();
    }

    public function onQuit(PlayerQuitEvent $e){
        if($this->inArena($e->getPlayer())){
            $this->leaveArena($e->getPlayer());
        }
    }

    public function startGame($force = false){
        if($force !== false && count($this->getArenaPlayers()) < 8){

        }

    }

    public function stopGame(){

    }

    public function checkAlive(){

    }

    public function joinToArena(Player $p){

    }

    public function leaveArena(Player $p){

    }

    public function onPotatoChange(Player $new, Player $old = null){
        $tnt = Entity::createEntity("PrimedTNT", $this->level->getChunk(), self::getTNTNbt($new->getLocation()));
        $this->holders[strtolower($new->getName())] = $tnt;
        //$tnt->setPositionAndRotation(new Vector3($new->x, $new->y, $new->z), $new->yaw, $new->pitch);
        $inv = $new->getInventory();
        $inv->setItem(0, Item::get(392, 0, 1));
        $inv->setHotbarSlotIndex(0, 0);
        $inv->sendContents($new);
        if($old == null){
            return;
        }
        $this->holders[strtolower($old->getName())]->kill();
        $this->holders[strtolower($old->getName())]->close();
        unset($this->holders[strtolower($old->getName())]);
        $old->getInventory()->clearAll();
    }

    public static function getTNTNbt(Location $pos){
        $nbt = new Compound();
        $nbt->Pos = new Enum("Pos", [
            new Double(0, $pos->x),
            new Double(1, $pos->y),
            new Double(2, $pos->z),
        ]);
        $nbt->Motion = new Enum("Motion", [
            new Double(0, 0),
            new Double(1, 0),
            new Double(2, 0),
        ]);
        $nbt->Rotation = new Enum("Rotation", [
            new Float(0, $pos->yaw),
            new Float(1, $pos->pitch)
        ]);
        return $nbt;
    }

    public function onMove(PlayerMoveEvent $e){
        $p = $e->getPlayer();
        if(!isset($this->holders[strtolower($p->getName())])){
            return;
        }
        $tnt = $this->holders[strtolower($p->getName())];
        $to = $e->getTo();
        $tnt->setMotion(new Vector3($to->x, $to->y, $to->z));
        $tnt->setRotation($p->yaw, $p->pitch);
    }

    public function selectHolders(){
        $players = [];
        if(count($all = $this->getArenaPlayers()) > 10 && count($all) < 18){
            $max = 2;
        }
        elseif(count($all) < 10){
            $max = 1;
        }
        else{
            $max = 3;
        }
        $keys = array_rand($all, $max);
        foreach($keys as $key){
            $players[] = $all[$key];
        }
        return $players;
    }
}