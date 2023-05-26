<?php

namespace TheBridges\arena;

use TheBridges\TheBridges;
use TheBridges\arena\Arena;
use TheBridges\arena\GamePlayer;

class Team {

    private $main;
    protected $color;
    /** @var GamePlayer[] $players*/
    private $players;

    public function __construct(Arena $main, $color, $players){
      $this->main = $main;
      $this->color = $color;
      $this->players = $players;
      parent::__construct($main);
    }

    public function getColor(){
      return $this->color;
    }
 
    public function getArena(){
      return $this->main;
    }
 
    public function getPlayers(){
      return $this->players;
    }
 
    public function getCount(){
      return \count($this->players);
    }
 
    public function messageTeamPlayers($msg){
      foreach ($this->players as $p){
        $p->sendMessage(\str_replace("&","§",($this->arena->main->getPrefix().$msg)));
      }
    }
    
    public function addPlayer(GamePlayer $p){
      if ($p->getTeam()->getColor() == $this->color){
        $p->sendMessage($this->arena->main->getPrefix().TextFormat::RED."You're already in ".$this->color."team!");
        return;
      }
      if ($this->getCount() > 20){
        $p->sendMessage($this->arena->main->getPrefix().TextFormat::RED."This team is full!");
        return;
      }
      $this->messageTeamPlayers("&a".$p->getName()." has joined &7[&e".$this->getCount()."&7/&a20&7]");
      $pole = [$p->getName() => $p->getName()];
      $this->players = \array_merge($pole, $this->players);
      $this->arena->getTeam("all")->messageTeamPlayers("&a".$p->getName()." has joined &7[&e".$this->getCount()."&7/&a50&7]");
    }

    public function removePlayer(GamePlayer $p){
      if (!isset($this->players[$p->getName()])){
        return;
      }
      unset($this->players[$p->getName()]);
    }

}