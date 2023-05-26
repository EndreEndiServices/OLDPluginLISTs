<?php

namespace Games;

use pocketmine\scheduler\Task;


class GamesClock extends Task{

    private $plugin;

    public function __construct(Games $plugin){
        $this->plugin=$plugin;
        
    }

    public function onRun(int $tick){
        $this->plugin->getServer()->getAsyncPool()->submitTask(new Query($this->plugin->HOST, $this->plugin->USER, $this->plugin->PASS));

    }


}
