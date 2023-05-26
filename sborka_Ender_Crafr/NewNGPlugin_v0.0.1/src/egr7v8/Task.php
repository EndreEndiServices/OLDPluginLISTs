<?php

namespace egr7v8;

use pocketmine\scheduler\PluginTask;
use pocketmine\Server;

class Task extends PluginTask {
	
  public function __construct($plugin) {
    $this->plugin = $plugin;
    $this->start = false;
    parent::__construct($plugin);
  }
  
  public function onRun($ticks) {
    if($this->start) {
      $this->plugin->gift();
    } else {
      $this->start = true;
    }
  }
}