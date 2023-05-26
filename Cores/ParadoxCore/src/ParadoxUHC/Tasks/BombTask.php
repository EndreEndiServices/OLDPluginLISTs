<?php

namespace ParadoxUHC\Tasks;

use ParadoxUHC\UHC;
use pocketmine\scheduler\PluginTask;
use pocketmine\plugin\Plugin;
use pocketmine\Player;
use pocketmine\level\Position;
use pocketmine\level\Explosion;
use pocketmine\utils\TextFormat as TF;

class BombTask extends PluginTask {
    
    public $time = 30;
    public $player;
    public $plugin;
    public $position;
    
    public function __construct(Plugin $plugin, Player $player, Position $position){
        $this->plugin = $plugin;
        $this->player = $player;
        $this->position = $position;
        parent::__construct($plugin);
    }
  
    public function onRun($currentTick){
        $this->time--;
        
        if($this->time === 0){
            $this->explodeBomb($this->player, $this->position);
            $this->cancel();
        }
        
    }
    
    public function cancel(){
        $task = $this->getTaskId();
        $this->plugin->getServer()->getScheduler()->cancelTask($task);
    }
    
    public function explodeBomb(Player $player, Position $position){
        $explosion = new Explosion($position, 3);
        $explosion->explodeA();
        $explosion->explodeB();
        $this->plugin->getServer()->broadcastMessage($this->plugin->getPrefix().TF::GRAY.$player->getDisplayName()."'s corpse has exploded!");
    }
    
}