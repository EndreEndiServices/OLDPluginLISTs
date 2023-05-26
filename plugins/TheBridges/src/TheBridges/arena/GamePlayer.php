<?php

namespace TheBridges\arena;

use pocketmine\Player;
use TheBridges\arena\Arena;
use TheBridges\arena\Team;
use TheBridges\kit\Kit;
use TheBridges\kit\KitManager;

class GamePlayer extends Player{

    /** @var  Player $player */
    public $player;
    public $kit;
    public $team;

    public function __construct(Player $p){
        $this->player = $p;
    }
    
    public function getArena(){
      return $this->team->getArena();
    }
    
    public function getKits(){
      return $this->team->getArena()->kitmgr->getKits($this);
    }
    
    public function getKit(){
      return $this->team->getArena()->kitmgr->getKit($this);
    }
    
    public function setKit(Kit $kit){
      $this->team->getArean()->kitmgr->setKit($this, $kit);
    }
    
    public function getTeam(){
      return $this->team;
    }
    
}