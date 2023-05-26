<?php

namespace Admin;

use pocketmine\scheduler\Task;

class VanishClock extends Task{

    private $plugin;

    public function __construct(Admin $plugin){
        $this->plugin=$plugin;
        
    }

    public function onRun(int $tick){
        $this->plugin->Vanish();

    }

}
