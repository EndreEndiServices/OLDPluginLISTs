<?php

namespace ColorMatch\Arena;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Player;
use ZombieSurvival\ZombieSurvival;
use pocketmine\math\Vector3;
use pocketmine\level\Position;
use pocketmine\block\Block;
use pocketmine\entity\Effect;
use pocketmine\item\Item;
use pocketmine\block\IronDoor;
use pocketmine\entity\Zombie;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Float;

class Arena implements Listener{

    private $id;
    public $plugin;
    public $data;
    public $level;
    
    public $lobbyp = [];
    public $ingamep = [];
    public $spec = [];
    
    public $game = 0;
    
    public $spawnpos = 0;
    
    public $winners = [];
    
    public $wave = 1;
    
    public $starting = false;
    
    public $task;
    
    public function __construct($id, ZombieSurvival $plugin){
        $this->id = $id;
        $this->plugin = $plugin;
        $this->level = $this->plugin->getServer()->getLevelByName($this->data["world"]);
        $this->enableScheduler();
    }
    
    public function enableScheduler(){
        $this->plugin->getServer()->getScheduler()->scheduleRepeatingTask($this->task = new ArenaSchedule($this), 20);
    }
    
    public function onBlockTouch(PlayerInteractEvent $e){
        $b = $e->getBlock();
        $p = $e->getPlayer();
        if($b instanceof IronDoor && !$p->isOp()){
            $e->setCancelled(true);
        }
        if("$b->x:$b->y:$b->z" == "{$this->data["joinsign"]->x}:{$this->data["joinsign"]->y}:{$this->data["joinsign"]->z}"){
            $this->joinToArena($p);
        }
    }
    
    public function getPlayerMode(Player $p){
        if(isset($this->lobbyp[strtolower($p->getName())])){
            return 0;
        }
        if(isset($this->ingamep[strtolower($p->getName())])){
            return 1;
        }
        if(isset($this->spec[strtolower($p->getName())])){
            return 2;
        }
        return false;
    }
    
    public function messageArenaPlayers($msg){
        $ingame = array_merge($this->lobbyp, $this->ingamep, $this->spec);
        foreach($ingame as $p){
            $p->sendMessage($this->plugin->getPrefix().$msg);
        }
    }
    
    public function messageAlivePlayers($msg){
        foreach($this->ingame as $p){
            $p->sendMessage($this->plugin->getPrefix().$msg);
        }
    }
    
    public function joinToArena(Player $p){
            if($this->game === 1){
                $p->sendMessage($this->plugin->getPrefix()."Arena is running");
                return;
            }
            $p->teleport($this->data['pspawns'][$this->spawnpos]);
            $this->spawnpos++;
            $this->lobbyp[strtolower($p->getName())] = $p;
            $this->messageArenaPlayers("{$p->getDisplayName()} se pripojil");
            return;
    }
    
    public function leaveArena(Player $p){
        if($this->getPlayerMode($p) === 0){
            unset($this->lobbyp[strtolower($p->getName())]);
            $p->teleport(new Position($this->plugin->getServer()->getDefaultLevel()->getSpawnLocation()));
            $this->spawnpos--;
        }
        if($this->getPlayerMode($p) === 1){
            unset($this->ingamep[strtolower($p->getName())]);
            $this->messageArenaPlayers($p->getDisplayName()." se odpojil. Zbyvaji dva hraci");
            $this->checkAlive();
        }
        if($this->getPlayerMode($p) === 2){
            unset($this->spec[strtolower($p->getName())]);
            $p->teleport(new Position($this->plugin->getServer()->getDefaultLevel()->getSpawnLocation()));
        }
        $p->sendMessage($this->plugin->getPrefix()."Opoustis arenu...");
    }
    
    public function startGame(){
            $this->game = 1;
            $spawn = 0;
            foreach($this->lobbyp as $p){
                unset($this->lobbyp[strtolower($p->getName())]);
                $this->ingamep[strtolower($p->getName())] = $p;
            }
            $this->messageArenaPlayers("Game started!");
            $this->newWave(1);
    }
    
    public function onQuit(PlayerQuitEvent $e){
        if($this->getPlayerMode($e->getPlayer()) !== false){
            $this->leaveArena($e->getPlayer());
        }
    }
    
    public function onKick(PlayerKickEvent $e){
        if($this->getPlayerMode($e->getPlayer()) !== false){
            $this->leaveArena($e->getPlayer());
        }
    }
    
    public function checkAlive(){
        if(count($this->ingamep) <= 1){
            $this->broadcastResult();
            $this->stopGame();
        }
    }
    
    public function stopGame(){
        $this->unsetAllPlayers();
        $this->game = 0;
        $this->task->time = 0;
    }
    
    public function unsetAllPlayers(){
        foreach($this->ingamep as $p){
            unset($this->ingamep[strtolower($p->getName())]);
            $p->teleport(new Position());
        }
        foreach($this->lobbyp as $p){
            unset($this->lobbyp[strtolower($p->getName())]);
            $p->teleport(new Position());
        }
        foreach($this->spec as $p){
            unset($this->spec[strtolower($p->getName())]);
            $p->teleport(new Position());
        }
    }
    
    public function onRespawn(PlayerRespawnEvent $e){
        $p = $e->getPlayer();
        
    }
    
    public function onDeath(PlayerDeathEvent $e){
        $p = $e->getEntity();
        
    }
    
    public function onDropItem(PlayerDropItemEvent $e){
        $p = $e->getPlayer();
        if(!$p->isOp()){
            $e->setCancelled();
        }
    }
    
    public function onHit(EntityDamageEvent $e){
        if($e->getDamager() instanceof Player){
            if($e instanceof EntityDamageByEntityEvent){
                $p = $e->getEntity();
                if($this->getPlayerMode($p) !== 1 || $p instanceof Human){
                    $e->setCancelled(true);
                }
            }
        }
    }
    
    public function onBlockBreak(BlockBreakEvent $e){
        $p = $e->getPlayer();
        $b = $e->getBlock();
        if(!$p->isOp()){
            $e->setCancelled(true);
        }
    }
    
    public function onBlockPlace(BlockPlaceEvent $e){
        $p = $e->getPlayer();
        $b = $e->getBlock();
        if(!$p->isOp()){
            $e->setCancelled(true);
        }
    }
    
    public function onMove(PlayerMoveEvent $e){
        if($this->getPlayerMode($p) === 0){
            $e->setCancelled();
        }
    }
    
    public function broadcastResult(){
        foreach($this->ingamep as $p){
            foreach($this->getServer()->getOnlinePlayers as $pl){
                $p->sendMessage(TextFormat::BLUE.TextFormat::BOLD."{$p->getName()} survived");
                $p->sendTip(TextFormat::BLUE.TextFormat::BOLD."{$p->getName()} survived");
            }
        }
    }
    
    public function getNBT() {
		$nbt = new Compound("", [
			"Pos" => new Enum("Pos", [
				new Double("", 0),
				new Double("", 0),
				new Double("", 0)
			]),
			"Motion" => new Enum("Motion", [
				new Double("", 0),
				new Double("", 0),
				new Double("", 0)
			]),
			"Rotation" => new Enum("Rotation", [
				new Float("", 0),
				new Float("", 0)
			]),
		]);
		return $nbt;
    }
    
    public function spawnZombies(){
        foreach($this->data['zspawns'] as $v3){
            $chunk = $level->getChunk($v3->x >> 4, $v3->z >> 4, false);
            $nbt = $this->getNBT();
            $zo = new Zombie($chunk,$nbt);
            $zo->setPosition($v3);
            $zo->spawnToAll();
        }
    }
    
    public function newWave($wave){
        $this->wave = $wave;
        foreach($this->ingamep as $p){
            $p->sendTip(TextFormat::BLUE."WAVE $wave");
            $p->sendMessage(TextFormat::GRAY."==========[ ".TextFormat::AQUA."Progress".TextFormat::GRAY." ]==========\n"
                         . TextFormat::BLUE. "             WAVE $wave"
                    .                        "==========================");
        }
    }
}