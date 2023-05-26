<?php

namespace MTCore;

use pocketmine\scheduler\Task;
use MTCore\MTCore;

class ChatDelay extends Task{
    
    public $plugin;
    public $player;
    public $finaltick;
    
    public function __construct(MTCore $plugin, $player) {
        $this->plugin = $plugin;
        $this->player = $player;
        $this->finaltick = $this->plugin->getServer()->getTick() + 100;
    }
    
    public function onRun($tick){
        unset($this->plugin->chatters[strtolower($this->player)]);
    }
}