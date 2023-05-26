<?php
namespace mg;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat as color;
use pocketmine\plugin\Plugin;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\Player;
use pocketmine\item\Item;

class FloatingTextTask extends \pocketmine\scheduler\PluginTask{
    public $fT;
    public $level;

    public function __construct($plugin, FloatingTextParticle $floatingText, $level){
        $this->plugin = new $plugin;
        $this->fT = $floatingText;
        $this->level = $level;
        parent::__construct($plugin);
    }
    public function onRun($Tick) {
        $w = $this->level;
        if($w instanceof \pocketmine\level\Level){
        $this->fT->setInvisible(true);
        $w->addParticle($this->fT);
    
        }
    }
}