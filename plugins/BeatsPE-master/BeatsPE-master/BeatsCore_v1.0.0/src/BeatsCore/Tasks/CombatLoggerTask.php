<?php

namespace BeatsCore\Tasks;

use pocketmine\Player;
use pocketmine\scheduler\PluginTask;

use BeatsCore\Core;

class CombatLoggerTask extends PluginTask{

    public function __construct(Core $plugin){
        $this->plugin = $plugin;
        parent::__construct($plugin);
    }

    public function onRun(int $currentTick){
        foreach($this->plugin->players as $player=>$time){
            if((time() - $time) > $this->plugin->interval){
                $p = $this->plugin->getServer()->getPlayer($player);
                if($p instanceof Player){
                    $p->sendMessage("§l§8(§4!§8)§r §cYou are out of combat, you can now logout!§r");
                    unset($this->plugin->players[$player]);
                }else unset($this->plugin->players[$player]);

            }

        }

    }

}