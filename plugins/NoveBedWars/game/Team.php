<?php

namespace BedWars\game;

use BedWars\arena\Arena;

class Team {
    
    /** @var Arena $arena */
    public $arena;
    /** @var GamePlayer[] $players */
    public $players;
    public $color;
    public $chat;
    /** @var Bed $bed */
    public $bed;
    // public $enderchest;
    
    public function __construct(Arena $arena, $color, $chat, $players){
        $this->arena = $arena;
        $this->players = $players;
        $this->color = $color;
        $this->chat = $chat;
        $this->bed = $arena->beds[$color];
    }
    
    public function getPlayers(){
        return $this->players;
    }
    
    public function messagePlayers($msg){
        try {
            foreach ($this->players as $name => $game) {
                $p = $game->getPlayer();
                if ($p->isOnline()) {
                    $p->sendMessage($this->arena->bw->prefix . $msg);
                }
            }
        }
        catch (Exception $e){
            echo $e;
        }
    }
    
    public function addPlayer(GamePlayer $game){
        $temp = [$game->getPlayer()->getName() => $game];
        $this->players = \array_merge($temp, $this->players);
    }
    
    public function removePlayer(GamePlayer $game){
        unset($this->players[$game->getPlayer()->getName()]);
    }
    
    public function getBed(){
        return $this->bed;
    }
    
    public function getColor(){
        return $this->color;
    }
    
    public function getChat(){
        return $this->chat;
    }
    
}
