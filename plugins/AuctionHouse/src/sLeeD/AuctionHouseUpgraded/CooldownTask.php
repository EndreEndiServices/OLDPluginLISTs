<?php

namespace sLeeD\AuctionHouseUpgraded;

use pocketmine\scheduler\Task;

class CooldownTask extends Task{

    private $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }


    public function onRun($tick){
        $this->plugin->timer();
    }
}
