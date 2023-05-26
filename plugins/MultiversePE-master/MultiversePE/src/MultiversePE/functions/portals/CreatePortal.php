<?php
namespace MultiversePE\functions\portals;

use pocketmine\scheduler\PluginTask;
use pocketmine\Player;

class CreatePortal extends PluginTask{
  public function onRun(){
  }
  
  public function createPortal($name){
    $this->name = $name;
    //TODO: Create portal with name $this->name
  }
}
?>
