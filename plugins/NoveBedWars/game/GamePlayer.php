<?php

namespace Bedwars\game;

use BedWars\arena\Arena;
use pocketmine\Player;

class GamePlayer {
    
    const WAITING = 0;
    const PLAYING = 1;
    const SPECTATING = 2;
    
    public $arena;
    public $player;
    public $phase;
    public $team;
    public $isShopping;
    
    public function __construct(Arena $arena, Player $p){
        $this->arena = $arena;
        $this->player = $p;
        $this->phase = self::WAITING;
        $this->team = null;
        $this->isShopping = false;
    }
    
    public function getPlayer(){
        return $this->player;
    }
    
    public function setPhase($phase){
        $this->phase = $phase;
    }
    
    public function isPlaying(){
        return $this->phase === self::PLAYING ? true : false;
    }
    
    public function isSpectating(){
        return $this->phase === self::SPECTATING ? true : false;
    }
    
    public function isShopping(){
        return $this->isShopping;
    }

    public function setShopping($boolean){
        $this->isShopping = $boolean;
    }
    
    public function getTeam(){
        return $this->team;
    }
    
    public function addToTeam(Team $team){
        $this->team = $team;
    }
    
    public function removeFromTeam(){
        $this->team = null;
    }
    
}
