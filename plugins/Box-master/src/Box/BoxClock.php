<?php

namespace Box;

use pocketmine\Player;
use pocketmine\scheduler\Task;


class BoxClock extends Task{

    private $plugin;

    private $player;
    
    public function __construct(Box $plugin, Player $player){
        $this->plugin=$plugin;

        $this->player=$player;
        
    }

    public function onRun(int $tick){
    	$this->plugin->Random($this->player);

    }


}
