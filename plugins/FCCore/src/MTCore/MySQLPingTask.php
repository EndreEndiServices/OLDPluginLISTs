<?php

namespace MTCore;

use pocketmine\scheduler\Task;
use MTCore\MySQLManager;

class MySQLPingTask extends Task
{
		
    public function __construct(MySQLManager $plugin){
        $this->plugin = $plugin;
    }
	

    public function onRun($currentTick){
        $this->plugin->getDatabase()->ping();
    }
}