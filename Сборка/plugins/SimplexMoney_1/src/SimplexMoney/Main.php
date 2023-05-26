<?php

namespace SimplexMoney;

use pocketmine\level\Position;
use pocketmine\scheduler\CallbackTask;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

class Main extends PluginBase implements Listener {
    public function onEnable()
    {
        $this->economyAPI = \onebone\economyapi\EconomyAPI::getInstance ();
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "SimplexMoney")), 59 * 20);
    }
    public function SimplexMoney(){
        foreach($this->getServer()->getOnlinePlayers() as $p) {
            $money = $this->economyAPI->myMoney($p);
            $m = $money + 5;
            $this->economyAPI->setMoney($p, $m);
			$p->sendPopup("§7(§cНаграждение§7) §a×§f Вы получили §a5$ §fза игру на сервере!");
        }
    }
}
