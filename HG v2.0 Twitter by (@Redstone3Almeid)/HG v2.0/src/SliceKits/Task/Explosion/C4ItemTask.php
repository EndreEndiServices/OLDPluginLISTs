<?php

namespace SliceKits\Task\Explosion;

use pocketmine\item\Item;
use pocketmine\scheduler\PluginTask;
use pocketmine\Player;

use pocketmine\utils\TextFormat as color;

use SliceKits\Loader;

class C4ItemTask extends PluginTask{
    
    public function __construct(Loader $plugin, Player $player) {
		parent::__construct($plugin);
		$this->plugin = $plugin;
                $this->player = $player;
	}
        
        public function onRun($currentTick) {
            
                $this->player->getInventory()->removeItem(Item::get(282, 0, 1));
		$this->player->getInventory()->addItem(Item::get(113, 0, 1));
                
                $this->player->sendMessage("§c-> §aAgora você pode usar seu Kit!");
                $this->player->sendMessage("§c-> §aOlhe seu inventario");
                
	}
}

