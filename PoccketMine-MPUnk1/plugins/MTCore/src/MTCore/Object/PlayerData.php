<?php

namespace MTCore\Object;

use pocketmine\Player;

class PlayerData {

    /** @var Player $player */
    public $player;

    private $inLobby = true;

    /** @var int $tick */
    private $tick = 0;

    public function __construct(Player $p){
        $this->player = $p;
    }

    public function getPlayer() : Player {
        return $this->player;
    }

    public function inLobby() {
        return $this->inLobby;
    }

    public function setInLobby($value){
        $this->inLobby = $value;
    }

    public function setTick($tick){
        $this->tick = $tick;
    }

    public function getChatTick(){
        return $this->tick;
    }
}