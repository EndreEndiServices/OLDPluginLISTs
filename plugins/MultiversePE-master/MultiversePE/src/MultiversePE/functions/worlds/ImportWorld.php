<?php
namespace MultiversePE\functions\worlds;

use pocketmine\scheduler\PluginTask;
use pocketmine\level\LevelImport;
use pocketmine\Player;

class ImportWorld extends PluginTask{
  public function onRun(){
  }
  
  public function importWorld($name){
    $this->name = $name;
    $this->level->LevelImport->import($this->name); //Right?
  }
}
?>
