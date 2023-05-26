<?php

namespace HotPotato\Arena;


use pocketmine\Player;

class Manager{

    public $plugin;

    public function __construct(Arena $plugin){
        $this->plugin = $plugin;
    }

    public function getArenaPlayers(){
        $players = [];
        foreach($this->plugin->players as $p){
            if(isset($p['ins'])){
                $players[strtolower($p['ins']->getName())] = $p['ins'];
            }
        }
        return $players;
    }

    public function isArenaFull(){
        count($this->getArenaPlayers()) >= 20 ? true : false;
    }

    public function inArena(Player $p){
        isset($this->getArenaPlayers()[strtolower($p->getName())]) ? true : false;
    }

    public function getLobbyPlayers(){
        $players = [];
        foreach($this->plugin->players as $p){
            if(isset($p['lobby']) && $p['lobby'] === true){
                $players[strtolower($p['ins']->getName())] = $p['ins'];
            }
        }
        return $players;
    }

    public function getOwner(){
        return $this->plugin->plugin;
    }

    public function getId(){
        return $this->plugin->id;
    }

    public function getPrefix(){
        return "";
    }
}