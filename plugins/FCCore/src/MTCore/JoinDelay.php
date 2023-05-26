<?php

namespace MTCore;

use pocketmine\scheduler\Task;
use pocketmine\Player;
use MTCore\MTCore;

class JoinDelay extends Task{
    
    private $plugin;
    private $player;
    
    public function __construct(MTCore $plugin, Player $p){
        $this->plugin = $plugin;
        $this->player = $p;
    }
    
    public function onRun($currentTick) {
        $this->player->teleport($this->plugin->lobby);
        $this->player->setRotation(270, 0);
    }
}