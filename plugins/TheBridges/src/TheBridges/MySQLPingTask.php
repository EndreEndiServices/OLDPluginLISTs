<?php

namespace TheBridges;

use pocketmine\scheduler\Task;
use TheBridges\MySQLManager;

class MySQLPingTask extends Task
{
		
    public function __construct(MySQLManager $plugin){
        $this->plugin = $plugin;
    }
	

    public function onRun($currentTick){
        $this->plugin->getDatabase()->ping();
    }
}