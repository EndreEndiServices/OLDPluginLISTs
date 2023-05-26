<?php

namespace ParadoxUHC\Tasks;

use ParadoxUHC\UHC;
use pocketmine\scheduler\PluginTask;
use pocketmine\plugin\Plugin;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;

class DeathTask extends PluginTask {
    
    public $time = 30;
    public $player;
    public $plugin;
    
    public function __construct(UHC $plugin, Player $player){
        $this->plugin = $plugin;
        $this->player = $player;
        parent::__construct($plugin);
    }
  
    public function onRun($currentTick){
        $this->time--;
        if($this->time == 0){
            $name = $this->player->getName();
            if($this->plugin->getLanguage($this->player) == "english"){
                $this->player->kick($this->plugin->getPrefix(). TF::GRAY . ' Thanks for playing in the UHC!', false);
            }
            if($this->plugin->getLanguage($this->player) == "spanish"){
                $this->player->kick($this->plugin->getPrefix(). TF::GRAY . ' Gracias por jugar en el UHC!', false);
            }
            $this->plugin->getServer()->removeWhitelist(strtolower($name));
            $this->plugin->getServer()->removeWhitelist($name);
            $this->cancel();
            
        }
        
    }
    
    public function cancel(){
        $task = $this->getTaskId();
        $this->getOwner()->getServer()->getScheduler()->cancelTask($task);
    }
    
    
}