<?php

namespace BedWars\mysql;

use pocketmine\scheduler\Task;

class MySQLPingTask extends Task {

    private $mysqlmgr;
    
    public function __construct(MySQLManager $mysqlmgr){
        $this->mysqlmgr = $mysqlmgr;
    }
	

    public function onRun($currentTick){
        $database = $this->mysqlmgr->getDataBase();
        $database->ping();
    }
}