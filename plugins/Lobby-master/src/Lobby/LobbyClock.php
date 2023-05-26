<?php

namespace Lobby;

use pocketmine\scheduler\Task;


class LobbyClock extends Task{

    private $plugin;

    public function __construct(Lobby $plugin){
        $this->plugin=$plugin;
        
    }

    public function onRun(int $tick){
        $this->plugin->onUpdate();

    }


}
