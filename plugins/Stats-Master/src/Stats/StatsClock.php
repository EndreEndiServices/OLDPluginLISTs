<?php

namespace Stats;

use pocketmine\scheduler\Task;


class StatsClock extends Task{

    private $interval = 0;

    private $timer1 = 0, $timer2 = 0, $timer3 = 0;

    private $plugin;

    public function __construct(Stats $plugin){
        $this->plugin=$plugin;

    }

    public function onRun(int $tick){
        $this->interval += 1;

        if($this->interval == 10){ $this->timer1 += 1;
            $this->plugin->sendStats($this->plugin->getConfig()->get("stats.table.name.".$this->timer1), false, "POINTS", 79448094830);

        }elseif($this->interval == 12){ $this->timer2 += 1;
            $this->plugin->sendStats($this->plugin->getConfig()->get("stats.table.name.".$this->timer2), false, "KILLS", 89448094830);

        }elseif($this->interval == 14){ $this->timer3 += 1;
            $this->plugin->sendStats($this->plugin->getConfig()->get("stats.table.name.".$this->timer3), false, "VICTORIES", 99448094830);

        }

        if($this->interval >= 14){
            $this->interval = 0;

        }

        $most = $this->plugin->getConfig()->get("stats.utmost");

    	if($this->timer1 >= $most){
    	    $this->timer1 = 0;

    	}elseif($this->timer2 >= $most){
    	    $this->timer2 = 0;

    	}elseif($this->timer3 >= $most){
    	    $this->timer3 = 0;

    	}
    
    }


}
