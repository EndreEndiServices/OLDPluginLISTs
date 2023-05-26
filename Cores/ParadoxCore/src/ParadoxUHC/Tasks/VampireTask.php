<?php

namespace ParadoxUHC\Tasks;

use ParadoxUHC\UHC;
use pocketmine\scheduler\PluginTask;
use pocketmine\plugin\Plugin;
use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\block\Transparent;
use pocketmine\block\Solid;
use pocketmine\utils\TextFormat as TF;

class VampireTask extends PluginTask {
    
    public $time = 120;
    public $player;
    public $plugin;
    
    public function __construct(Plugin $plugin, Player $player){
        $this->plugin = $plugin;
        $this->player = $player;
        parent::__construct($plugin);
    }
  
    public function onRun($currentTick)
    {
        $this->time--;
        $x = $this->player->x;
        $y = $this->player->y;
        $z = $this->player->z;
        $timeOfDay = abs($this->player->getLevel()->getTime() % 24000);
        if (0 < $timeOfDay and $timeOfDay < 13000) {
            if ($this->time == 0) {
                if ($this->plugin->getLanguage($this->player) == "english") {
                    $this->player->sendMessage(TF::GREEN . "Your vampire effects have worn off!");
                }
                if ($this->plugin->getLanguage($this->player) == "spanish") {
                    $this->player->sendMessage(TF::GREEN . "Sus efectos vampiro han desaparecido!");
                }
                $this->cancel();

            }
        }
    }

    
    public function cancel(){
        $task = $this->getTaskId();
        $this->plugin->getServer()->getScheduler()->cancelTask($task);
    }
    
    
}