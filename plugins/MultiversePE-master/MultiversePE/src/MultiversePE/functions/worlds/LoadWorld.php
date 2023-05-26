<?php
namespace MultiversePE\functions\worlds;

use pocketmine\scheduler\PluginTask;
use pocketmine\Player;

class LoadWorld extends PluginTask{
  public function onRun(){
  }
  
  public function loadWorld($name){
    $this->name = $name;
    $this->getServer()->loadLevel($this->name);
  }
}
?>
