<?php

namespace BedWars\task;

use BedWars\BedWars;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class PortalTask extends Task {

    public $bw;
    public $p;
    
    public function __construct(BedWars $bw, Player $p){
        $this->bw = $bw;
        $this->p = $p;
    }
    
    public function onRun($t){
        if (!$this->p->getLevel()->getBlockIdAt($this->p->getFloorX(), $this->p->getFloorY(), $this->p->getFloorZ()) === 90){
            return;
        }
        $arena = $this->bw->arena["bw-".\mt_rand(1,3)];
        $arena->joinToArena($this->p);
    }
    
}
