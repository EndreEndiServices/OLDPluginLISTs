<?php

namespace kvetinac97\arena;

use kvetinac97\TheBridges;
use kvetinac97\utils\WorldManager;
use kvetinac97\utils\ChestRefill; //Ten potom můžeš Honzo použít i v SGkách

class Arena {
 
  const OFF = 0;
  const PRESTART = 1;
  const WAITING = 2;
  const NOBRIDGE = 3;
  const BRIDGES = 4;
  const FALLING = 5;

    public $main;
    private $name;
    public $map;
    protected $data;
    private $worldmgr;
    private $chestrefill;
    public $kitmgr;
    public $listener;
 
    public function __construct(TheBridges $main, $name){
      $this->main = $main;
      $this->name = $name;
      $this->map = array_rand(\scandir($this->getDataPath()."worlds/bridges/"));
      $this->worldmgr = new WorldManager($this);
      $this->worldmgr->addWorld($this->map);
      $this->map = $this->main->getServer()->getLevelByName($this->map);
      $this->chestrefill = new ChestRefill($this, $this->map);
      $this->chestrefill->refill();
      $this->kitmgr = new KitManager($this)
      $this->data = [
        "phase" => $this::OFF,
        "players" => [
          "all" => new Team("all", []),
          "red" => new Team("red", []),
          "blue" => new Team("blue", []),
          "green" => new Team("green", []),
          "yellow" => new Team("yellow", []),
          "spectators" => new Team("spectators", [])
        ],  
      ];
      $this->main->getServer()->getScheduler()->scheduleRepeatingTask(new ArenaSchedule($this->main, $this), 20);
    }

    public function getTeam($color){
      return $this->data["players"][$color];
    }  

    public function getPhase(){
      return $this->data["phase"];
    }

    public function setPhase($value){
      $this->data["phase"] = $value;
    }
 
    public function getMap(){
      return $this->map;
    }
 
    public function getPlayers(){
      return $this->data["players"]["all"]->getPlayers();
    }
 
    public function getSpecs(){
      return $this->data["players"]["spectators"]->getPlayers();
    }
    
    public function getViewers(){
      return \array_merge($this->data["players"]["all"],$this->data["players"]["spectators"]);
    }
    
    public function getPlayerTeam($p){
      return $this->getTeam($this->data["players"]["all"][$p->getName()]);
    }
 
    public function getMapData(){
      $pole = [
      "default" => [
        "name" => "TheBridgesOne" //nějak jsem zapomněl jak se ta mapa jmenuje :/
        "red" => [48,31,-54],
        "blue" => [-55,30,-48],
        "green" => [-48,30,54],
        "yellow" => [55,30,48],
        "border" => [-179,173,-189,118,21]
        ]  
      ]; //Další mapy budou brzo!
      return $pole[$this->map->getName()];
     }
 
     public function isRunning(){
      if ($this->getPhase() !== $this::OFF){
       return true;
      }
      else {
       return false;
     }
    }
    
    public function isFull(){
      if ($this->data["players"]["all"]->getCount() < 50){
        return true;
      }
      else {
        return false;
      }
    }
    
    public function joinPlayer(Player $p){
      $pole = [$p->getName() => $p->getName()];
      $this->data["all"] = \array_merge($pole,$this->data["all"]);
      $x = 0;
      $y = 0;
      $z = 0; //Nevím jaké jsou souřadnice
      $p->teleport($x,$y,$z,$this->map); //Zde mi chybí připojovací aréna
    }
     
}