<?php

namespace ys;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\PlayerJoinEvent;
use onebone\economyapi\EconomyAPI;
use pocketmine\Player;
use pocketmine\utils\Config;

class pr extends PluginBase implements Listener {
  $rec = 0;

public function onEnable(){
$this->getServer()->getPluginManager()->registerEvents($this, $this);
$this->saveDefaultConfig();
}
public function onJoin(PlayerJoinEvent $e){
$p = $e->getPlayer();
$rec++
$record = $this->getConfig()->get("record");
$mon = $this->getConfig()->get("money");
if($rec == $record){
foreach($this->getServer()->getOnlinePlayers() as $all){
$this->getServer()->getPluginManager()->getPlugin("EconomyAPI")->addMoney($all, $mon);
$all->sendMessage("Рекорд онлайна достигнут в $record игроков, держи $mon $");
}
}
}
}