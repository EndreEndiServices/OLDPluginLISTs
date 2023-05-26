<?php

namespace Box;

use pocketmine\item\Item;
use pocketmine\scheduler\Task;


class FinishClock extends Task{

    private $plugin;
    
    public function __construct(Box $plugin){
        $this->plugin=$plugin;
        
    }

    public function onRun(int $tick){
    	$this->plugin->Text("null", 1);
    	$this->plugin->Item(Item::get(0, 0, 0), 1);

    	$this->plugin->allowed = true;

    }


}
