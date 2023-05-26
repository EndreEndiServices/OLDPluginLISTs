<?php

namespace Cases;

use pocketmine\item\Item; 
use pocketmine\event\Listener; 
use pocketmine\entity\Entity;
use pocketmine\event\player\PlayerJoinEvent; 
use pocketmine\command\Command; 
use pocketmine\command\CommandSender; 
use pocketmine\event\player\PlayerInteractEvent; 
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config; 
use pocketmine\math\Vector3; 

class Main extends PluginBase implements Listener {
  private $items; 
  private $cfg; 
  private $groups; 
  private $pp; 
	
	public function onEnable() {
@mkdir($this->getDataFolder()); 
$this->cfg = new Config($this->getDataFolder(). "config.yml", Config::YAML, ["secret-key" => base64_encode(mt_rand(100000, 999999)), "join-no" => "§6У вас нет предметов с §aДонат-кейса§6!", "join-yes" => "§6Вы приобрели кейс(ы) в авто-донате! §6Чтобы получить этот(и) кейс(ы), введите команду: /case get", "open-case" => "§6Вам выпала превелегия §a{group}§6! Чтобы §a получить§6 данную превелегию, введите: §a/case getgroup", "got-group" => "§6Вы успешно получили группу {group}", "case-unknown-cmd" => "§6Используйте так: §a/case (суб-команда)", "no-cases" => "§6У вас не  кейсов! Приобретите их у нас в авто-донате!", "get-case" => "§6Вы получили кейс(ы)! Удачного открытия!", "full-inventory" => "§6Не хватает места в инвентаре! Что не выдалось - лежит около вас!", "no-group" => "§6Вы не имеете групп с §aДонат-кейсов§6!", "case-name" => "§6Донат-кейс", "groups" => array("Admin", "Admin", "Owner")]); 
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->pp = $this->getServer()->getPluginManager()->getPlugin("PurePerms"); 
	}

public function onJoin(PlayerJoinEvent $e){
$p = $e->getPlayer(); 
if(isset($this->items[strtolower($p->getName())])){
$p->sendMessage($this->cfg->get("join-yes")); 
}else{
$p->sendMessage($this->cfg->get("join-no")); 
} 
}

public function onTap(PlayerInteractEvent $e){
	$i = $e->getItem(); 
	$p = $e->getPlayer(); 
	$name = $i->getCustomName() == "§r§r§6§r". $this->cfg->get("case-name"); 
	if($i->getId() == 407 && $name){
		$i->setCount(1); 
		$p->getInventory()->removeItem($i); 
		$group = array_rand($this->cfg->get("groups")); 
		$group = $this->cfg->get("groups")[$group]; 
		$p->sendMessage(str_replace("{group}", $group, $this->cfg->get("open-case"))); 
		$this->groups[strtolower($p->getName())] = $group; 
		}
// 407
}

public function onCommand(CommandSender $p, Command $cmd, $label, array $args){
if(strtolower($cmd->getName()) == "case"){
if(isset($args[0])){
switch(strtolower($args[0])){
case "getgroup": 
if(isset($this->groups[strtolower($p->getName())])){
$this->pp->getUserDataMgr()->setGroup($p, $this->pp->getGroup($this->groups[strtolower($p->getName())]), null);
$p->sendMessage(str_replace("{group}", $this->groups[strtolower($p->getName())], $this->cfg->get("got-group"))); 
unset($this->groups[strtolower($p->getName())]); 
	}else{
		$p->sendMessage($this->cfg->get("no-group")); 
		}
break; 
case "test": 
if(!$p->isOp()) return; 
$this->items[strtolower($p->getName())] =+ 3; 
break; 
case "get": 
if(isset($this->items[strtolower($p->getName())])){
$case = Item::get(407, 0, 1); 
$case->setCustomName("§r§r§6§r". $this->cfg->get("case-name")); 
while($this->items[strtolower($p->getName())] > 0){
$this->items[strtolower($p->getName())]--; 
if($p->getInventory()->canAddItem($case)){
$p->getInventory()->addItem($case); 
}else{
$p->getLevel()->dropItem(new Vector3($p->x, $p->y + 1, $p->z), $case); 
$error = true; 
}
}
$p->sendMessage($this->cfg->get("get-case")); 
if($error){
$p->sendMessage($this->cfg->get("full-inventory")); 
}
unset($this->items[strtolower($p->getName())]); 
}else{
$p->sendMessage($this->cfg->get("no-cases")); 
}
break; 
default: 
$p->sendMessage($this->cfg->get("case-unknown-cmd")); 
break; 
}
}else{
$p->sendMessage($this->cfg->get("case-unknown-cmd")); 
}
}elseif($cmd->getName() == "devcase"){
	if($args[0] == $this->cfg->get("secret-key")){
		$this->items[strtolower($args[2])] = $args[1]; 
   $p->sendMessage("Выдано $args[1] ключей игроку $args[2]"); 
		}
	}
}

}