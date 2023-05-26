<?php
namespace MultiversePE\functions\worlds;

use pocketmine\scheduler\PluginTask;
use pocketmine\Player;

class DeleteWorld extends PluginTask{
  protected $plugin;
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}
  
  public function onRun(){
  }
  
  public function deleteWorld($name){
    $this->name = $name;
    if(file_exists($this->dataPath ."worlds/".$name."/")){
      unlink($this->dataPath ."worlds/".$name."/");
    }
  }
}
?>
