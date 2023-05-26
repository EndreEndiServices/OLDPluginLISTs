<?php

namespace sitems; 

use pocketmine\level\particle\RedstoneParticle;
use pocketmine\math\Vector3;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\utils\Config;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Player;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\item\Item;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\entity\Effect;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\scheduler\CallbackTask;
use pocketmine\network\protocol\AddEntityPacket;
class main extends PluginBase implements Listener {
	public $pr,$m,$data,$data2;
public function onEnable(){
	
$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "setEffect")), 200);
	$this->data2 = new Config($this->getServer()->getDataPath()."/data.json",Config::JSON);
$this->data = new Config($this->getDataFolder() ."data.json",Config::JSON);
$this->getServer()->getPluginManager()->registerEvents($this, $this);
$this->m = $this->getServer()->getPluginManager()->getPlugin("money");
$this->pr = $this->getServer()->getPluginManager()->getPlugin("permissions");
}
public function ugar(EntityDamageEvent $e){
	if($e instanceof EntityDamageByEntityEvent){
		if($e->getEntity() instanceof Player && $e->getDamager() instanceof Player){
			$p = $e->getEntity();
			$d = $e->getDamager();
			if($d->getInventory()->getItemInHand()->getId() == 288 && !$e->isCancelled()){
				$p->setOnFire(1);
				$p->addEffect(Effect::getEffect(15)->setDuration(10)->setVisible(false));
			$pk = new AddEntityPacket();
			$pk->type = 93;
			$pk->eid = Entity::$entityCount++;
			$pk->metadata = array();
			$pk->x = $p->getX();
			$pk->y = $p->getY();
			$pk->z = $p->getZ();
			$pk->speedX = 0;
			$pk->speedY = 0;
			$pk->speedZ =0;
			$pk->yaw = 0;
			$pk->pitch = 0;
			foreach($this->getServer()->getOnlinePlayers() as $pl){
				$pl->dataPacket($pk);
			}
		}
		}
	}
}
function read($name){
		return $this->data2->get(strtolower($name));
	}
	function write($name,$type){
		$this->data2->set(strtolower($name),$type);
		$this->data2->save();
	}
	public function setEffect(){
		foreach($this->getServer()->getOnlinePlayers() as $p){
			if($p->getinventory()->getHelmet()->getId() == 314){
				switch($this->read($p->getName())){
					case 1:
						$p->addEffect(Effect::getEffect(16)->setDuration(200)->setVisible(false));
					break;
					case 2:
						$p->addEffect(Effect::getEffect(1)->setDuration(200)->setVisible(false));
					break;
					case 3:
						$p->addEffect(Effect::getEffect(14)->setDuration(200)->setVisible(false));
					break;
					case 4:
						$p->addEffect(Effect::getEffect(5)->setDuration(200)->setVisible(false));
					break;
				}
			} 
		}
	}
	public function first(PlayerJoinEvent $e){
		$n = $e->getPlayer()->getName();
		if($this->read($n,0)){
			$this->write($n,0);
		}
	}
public function onDisable(){
$this->data->save();
}
public function getT($n){
return $this->data->get(strtolower($n));
}
public function setT($n,$z){
$this->data->set(strtolower($n),$z);
$this->data->save();
}
public function join(PlayerJoinEvent $e){
$p = $e->getPlayer();
$n = $p->getName();
if($this->getT($n) == null)
$this->setT($n,0);
}
public function interact(PlayerInteractEvent $e){
	$p = $e->getPlayer();
	if($p->getInventory()->getItemInHand()->getId() == 266){
		if($e->getAction() == 3){
			$p->getInventory()->removeItem(Item::get(266,0,1));
			$r = mt_rand(0,4);
			switch($r){
				case 0:
				$t = "бесконечную силу";
				$ef = Effect::getEffect(5);
				$ef->setDuration(99*99*99*99*99);
				$ef->setAmplifier(2);
				$ef->setVisible(false);
				$p->addEffect($ef);
				break;
				case 1:
				$rnd = mt_rand(50,300);
				$t = "{$rnd} монет";
				$this->m->addM($p->getName(),$rnd);
				break;
				case 2:
				$rnd = mt_rand(1,20);
				$t = "{$rnd} алмазов";
				$p->getInventory()->addItem(Item::get(264,0,$rnd));
				break;
				case 3:
				$t = "§cтошноту на 2 минуты";
				$ef = Effect::getEffect(9);
				$ef->setDuration(2 * 20 * 60);
				$ef->setAmplifier(2);
				$ef->setVisible(false);
				$p->addEffect($ef);
				break;
				case 4:
				$rnd = mt_rand(20,200);
				$t = "§bтоп меч§e, но потеряли {$rnd} монет";
				$this->m->takeM($p->getName(),$rnd);
				$tiem = Item::get(276,0,1);
				$tiem->addEnchantment(Enchantment::get(16));
				$tiem->addEnchantment(Enchantment::get(17));
				$tiem->addEnchantment(Enchantment::get(18));
				$tiem->addEnchantment(Enchantment::get(19));
				$tiem->addEnchantment(Enchantment::get(20));
				$tiem->addEnchantment(Enchantment::get(21));
				$tiem->addEnchantment(Enchantment::get(34));
				$p->getInventory()->addItem($item);
				break;
				
			}
			$p->sendMessage("§7[§a+§7] §eВы съели хурму и получили ".$t);
		}
	}
	if($p->getInventory()->getItemInHand()->getId() == 175){
		if($e->getAction() == 3){
			$r = mt_rand(50,200);
			$this->m->addM($p->getName(),$r);
			$p->sendMessage("§7[§a+§7] §eПолучено {$r} монет");
			$p->getInventory()->removeItem(Item::get(175,0,1));
		}else
		$p->sendMessage("§7[§ai§7] §eАктивация: зажать в воздухе");
	}
}
 public function onCommand(CommandSender $s,Command $c,$label,array $args){
	 	if($c == "nh"){
			$p = $s;
			if($p->getInventory()->getHelmet()->getId() == 314){
				$n = $p->getName();
				if(!isset($args[0])){
					$p->sendMessage("§7--- §eNano§aHelmet §7---\n".
					"§9/nh 1 §8- §7Ночное зрение\n".
					"§9/nh 2 §8- §7Скорость\n".
					"§9/nh 3 §8- §7Невидимость\n".
					"§9/nh 4 §8- §7Сила\n".
					"§9/nh 0 §8- §7Отключить нано-шлем\n");
				}else if($args[0] === 0 or 1 or 2 or 3 or 4){
					$p->sendMessage("§a> §bНано-шлем §eперенастроен");
					$this->write($n,$args[0]);
				}else{
					$p->sendMessage("§с> §eСуб-команда \"".$args[0]."\" не найдена");
				}
			}else{
				$p->sendMessage("§с> §eУ Вас нет нано-шлема");
			}
		}
if($c == "addt") {
if($s->isOp()){
$s->sendMessage(" ");
$this->setT($args[0], $args[1]);
}else
$s->sendMessage(" хацкер");
}
if($c == "tt"){
if(isset($args[0])){
if($args[0] == 1){

if($this->getT($s->getName()) == 1){
$this->setT($s->getName(),0);
$s->sendMessage("§7[§a+§7] §eПолучен талисман. §c(теряется при смерти!)");
$s->getInventory()->addItem(Item::get(341,0,1));
}else
$s->sendMessage("§7[§c×§7] §eУ Вас нет этого талисмана!");
}else if($args[0] == 2){
 if($this->getT($s->getName()) == 2){
$this->setT($s->getName(),0);
$s->sendMessage("§7[§a+§7] §eПолучен талисман. §c(теряется при смерти!)");
$s->getInventory()->addItem(Item::get(378,0,1));
}else
$s->sendMessage("§7[§c×§7] §eУ Вас нет этого талисмана!");
}else
$s->sendMessage("§7[§c×§7] §eНеверный номер.");
}else
$s->sendMessage("§e––– §2Талисманы §e–––\n§e- §6Ваши талисманы(доступные для получения): §7".$this->getT($s->getName())."\n§e- §6/tt 1 §7– §6талисман скорости\n§e- §6/tt 2 §7– §7талисман силы");
}
}
public function move(PlayerMoveEvent $e){

$p = $e->getPlayer();
if($p->getInventory()->contains(Item::get(341,0,1))){
$ef = Effect::getEffect(1);

$ef->setDuration(200);
$ef->setAmplifier(3);
$ef->setVisible(false);
$p->addEffect($ef);
$ef2 = Effect::getEffect(8);

$ef2->setDuration(200);
$ef2->setAmplifier(1);
$ef2->setVisible(false);
$p->addEffect($ef2);
}
if($p->getInventory()->contains(Item::get(378,0,1))){
$ef21 = Effect::getEffect(5);

$ef21->setDuration(200);
$ef21->setAmplifier(4);
$ef21->setVisible(false);
$p->addEffect($ef21);
$ef22 = Effect::getEffect(8);

$ef22->setDuration(200);
$ef22->setAmplifier(1);
$ef22->setVisible(false);
$p->addEffect($ef22);
}
}
}
