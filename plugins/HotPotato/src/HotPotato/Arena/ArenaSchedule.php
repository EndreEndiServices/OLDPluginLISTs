<?php

namespace HotPotato\Arena;

use pocketmine\scheduler\Task;

class ArenaSchedule extends Task{

    public $plugin;
    public $maxGameTime;

    public function __construct(Arena $plugin){
        $this->plugin = $plugin;
    }

    public function onRun($currentTick){

    }
}