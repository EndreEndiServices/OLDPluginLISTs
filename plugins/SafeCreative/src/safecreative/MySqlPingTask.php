<?php

namespace safecreative;

use pocketmine\scheduler\Task;

class MySQLPingTask extends Task
{
		
    public function __construct(MySqlManager $plugin){
        $this->plugin = $plugin;
    }
	

    public function onRun($currentTick){
        $this->plugin->getDatabase()->ping();
    }
}