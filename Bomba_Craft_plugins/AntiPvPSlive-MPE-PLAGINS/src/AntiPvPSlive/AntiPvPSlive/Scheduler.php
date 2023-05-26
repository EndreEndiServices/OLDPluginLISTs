<?php #Скачано с https://vk.com/mpe_plagins
namespace AntiPvPSlive\AntiPvPSlive;

use pocketmine\Player;
use pocketmine\scheduler\PluginTask;

class Scheduler extends PluginTask{

    public function __construct($plugin){
        $this->plugin = $plugin;
        parent::__construct($plugin);
    }

    public function onRun($currentTick){
        foreach($this->plugin->players as $player=>$time){
            if((time() - $time) > $this->plugin->interval){
                $p = $this->plugin->getServer()->getPlayer($player);
                if($p instanceof Player){
                    $p->sendMessage("§f(§4§lPvP§f) §6Вы можете играть!");
                    unset($this->plugin->players[$player]);
                }else unset($this->plugin->players[$player]);
            }
        }
    }
}