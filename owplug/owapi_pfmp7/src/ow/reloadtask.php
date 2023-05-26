<?php

namespace ow;

use pocketmine\scheduler\PluginTask;

class reloadtask extends PluginTask{

    public function __construct(owapi $plugin){
        $this->plugin = $plugin;
        parent::__construct($plugin);
    }

    public function onRun($tick){
		$this->plugin->reloadTime();
    }

}