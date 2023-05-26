<?php

namespace MF;

use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\level\Level;
use pocketmine\utils\Config;
use pocketmine\Inventory;
use pocketmine\item\Item;
use pocketmine\entity\Effect;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\scheduler\CallbackTask;

Class SAPI extends PluginBase implements Listener{

      public $eco;
      public $hack;
      public $pp;
      public $report;

public function onEnable(){
   $this->hack = [];
   $this->report = [];
   $this->gods = array();
   $this->vanish = array();
   $this->getServer()->getPluginManager()->registerEvents($this, $this);
   $this->eco = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
   $this->pp = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
      $this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "money")), 20 * 60 * 5);
}
public function money(){
     foreach($this->getServer()->getOnlinePlayers() as $p){
   $this->eco->addMoney($p, 100);
}
}
public function NoFall(EntityDamageEvent $e){ 
   $p = $e->getEntity(); 
          if($e->getCause() === EntityDamageEvent::CAUSE_FALL){ 
   $e->setCancelled(true);
} 
} 
public function breakStone(BlockBreakEvent $event){
   $player = $event->getPlayer();
   $block = $event->getBlock()->getId();
           if($block == 1 or $block == 4){
   $this->eco->addMoney($player, 5);
   $player->sendPopup("§a> §fТы сломал §6камень §fи получил §b5$ §a<");
}
}
public function breakWood(BlockBreakEvent $event){
   $player = $event->getPlayer();
   $block = $event->getBlock()->getId();
           if($block == 17){
   $this->eco->addMoney($player, 10);
   $player->sendPopup("§a> §fТы сломал §6дерево §fи получил §b10$ §a<");
}
}
public function breakOre(BlockBreakEvent $event){
   $player = $event->getPlayer();
   $block = $event->getBlock()->getId();
           if($block == 14 or $block == 15 or $block == 16 or $block == 21 or $block == 56){
   $this->eco->addMoney($player, 100);
   $player->sendPopup("§a> §fВы §aвыкопали §eценный ресурс §fи получили §6100$! §a<");
}
}

public function AntiSpam(PlayerChatEvent $e){
   $msg = $e->getMessage();
   $p = $e->getPlayer();
     $words = [".ru", ".net", ".com", ".org", ".tk", ".ua", ".pro", ".gnt", "блять", "блядь", "бля", "сука", "сучка", "хуй", "пизда", "дилдо", "член", "пенис", "хуйло", "долбоёб", "долбаеб", "дрочу", "дрочить", "анал", "секс", "секас", "пидр", "пидор", "педик", "пидараз", "пидарас", "писос", "писька", "вагина", "ебал", "ебать", "трахаться", "трахать", "лох", "ДЦП", "дцп", "мамку", "мамка", "заебал", "наебал", "наеб", "наёб", "елда", "елдак", "шлюха", "задрал", "IP", "айпи", "ip", "Ip", "СУКА", " БЛЯТЬ", "ПИДАР"];
        if(array_search($msg, $words) !== false){{
   $p->kick("§c× §fВы были кикнуты по причине: §aСпам/Мат §c×");
   $e->setCancelled(true);
       }
    }
}
public function ItemGuard(PlayerInteractEvent $event){
   $player = $event->getPlayer();
   $name = $player->getName();
   $id = $player->getInventory()->getItemInHand()->getId();
          if($id == 259 or $id == 325 or $id == 385 or $id == 8 or $id == 9 or $id == 10 or $id == 11 or $id == 7 or $id == 438 or $id == 46 or $id == 383){
   $player->getInventory()->setItemInHand(Item::get(0, 0));
   $event->setCancelled(true);
   $player->sendMessage("§a> §fПредмет с ID §b$id §cзапрещён!");
   $this->getLogger()->info("$name использует запрещённый предмет с ID $id");
    }
}
public function NoCreativeDamage(EntityDamageEvent $event){
        if($event instanceof EntityDamageByEntityEvent){
            $d = $event->getDamager();
            if($d instanceof Player){
                if($d->isCreative()){
                    $d->sendMessage("§a> §cНельзя §fдраться в §bкреативе!");
                    $event->setCancelled(true);
                }
            }
        }
    }
public function ShowJoin(PlayerJoinEvent $e){
   $e->setJoinMessage(null);
}
public function ShowDeath(PlayerDeathEvent $e){
   $e->setDeathMessage(null);
}
public function ShowQuit(PlayerQuitEvent $e){
   $e->setQuitMessage(null);
}
public function onCommand(CommandSender $sender, Command $command, $label, array $args){
  switch($command->getName()){
   case "gm":
           if($sender->isSurvival()){
   $sender->setGamemode(1);
   $sender->sendMessage("§a> §fВаш §eигровой режим §aизменён на §bКреатив");
    }
    else{
   $sender->setGamemode(0);
   $sender->sendMessage("§a> §fВаш §eигровой режим §aизменён на §cВыживание");
}
   break;
   case "fly":
         if($sender->getAllowFlight()){
   $sender->setAllowFlight(false);
   $sender->sendMessage("§a> §fВы §cвыключили §dFly-режим");
   }
   else{
   $sender->setAllowFlight(true);
   $sender->sendMessage("§a> §fВы §aвключили §dFly-режим!");
}
   break;
   case "tpall":
   $name = $sender->getName();
     foreach($this->getServer()->getOnlinePlayers() as $p){
   $x = $sender->getX();
   $y = $sender->getY();
   $z = $sender->getZ();
   $p->teleport(new Vector3($x, $y, $z));
   $p->sendMessage("§a> §fИгрок §e$name §fтелепортировал §bвсех игроков в одну точку");
}
   break;
   case "heal":
   $sender->setHealth(20);
   $sender->setFood(20);
   $sender->sendMessage("§a> §fВы §aуспешно §fисцелили себя!");
   break;
   case "ci":
   $sender->getInventory()->clearAll();
   $sender->sendMessage("§a> §fВы §aуспешно §fочистили свой §eинвентарь!");
   break;
   case "god":
			if(isset($this->gods[$sender->getName()])){ 	
			$sender->sendMessage("§a> §fРежим §aбога §cотключен"); 		
    unset($this->gods[$sender->getName()]); 			}else{ 				
   $this->gods[$sender->getName()] = true; $sender->sendMessage("§a> §fРежим §aбога включён, §fтеперь вас не могут бить!"); 		
	} 
   break;
   case "hack":
          if($this->hack[$sender->getName()] != 1){
   $this->hack[$sender->getName()] = 1;
   $rand = mt_rand(100, 1000);
   $sender->sendMessage("§a> §fВам
§cнеудалось §bвзломать админку. §fВаше число: §7$rand");
   }
   else{
   $sender->sendMessage("§a> §fТы уже вводил эту §bКоманду! §fПопробуй после рестарта §dсервера");
}
   break;
   case "v":
			if(isset($this->vanish[$sender->getName()])){ 	
   $sender->removeAllEffects();
			$sender->sendMessage("§a> §fВы отключили режим §eневедимости"); 		
    unset($this->vanish[$sender->getName()]); 			}else{ 				
   $sender->addEffect(Effect::getEffect(14)->setVisible(false)->setAmplifier(10)->setDuration(1928000));
   $this->vanish[$sender->getName()] = true; $sender->sendMessage("§a> §fВы §aвключили §eневедимость"); 		
	} 
   break;
   case "report":
          if(!(isset($args[0]))){
   $sender->sendMessage("§a> §fИспользуй: §a/report <текст>");
}
          if(isset($args[0])){
          if($this->report[$sender->getName()] != 1){
   $this->report[$sender->getName()] = 1;
   $name = $sender->getName();
   $msg = implode(" ", $args);
   $this->getServer()->broadcastMessage("§a> §fИгрок §a$name §fнаписал жалобу: §b$msg");
     }else{
   $sender->sendMessage("§a> §fТы уже отправлял §bжалобу!");
}
}
   break;
   case "dupe":
			   if($sender->getGamemode() !== 0){
				   $sender->sendMessage("§a> §f Вы§c не можете §fдюпать в режиме §bКреатива!");
			     }else{
				  $inv = $sender->getInventory();
				  $i = $inv->getItemInHand();
				  $invid = $i->getId();
					$sender->sendMessage("§a> §fВы§b успешно§f дюпнули предмет в руке");
                    $i->setCount(64);
}
			     break;
          case "sleep":
          if($sender instanceof Player){
   $sender->sleepOn(new Vector3($sender->getX(), $sender->getY()+1, $sender->getZ()));
				  $sender->sendMessage("§a> §f Вы §aуспешно §fлегли §bпоспать §fна §eгрязный пол");
     }
     else{
   $sender->sendMessage("Эту команду можно использовать только в игре!");
   }
				    break;
       case "clearchat":
          if($sender instanceof Player){
   $name = $sender->getName();
   $this->getServer()->broadcastMessage("\n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n ");
	$this->getServer()->broadcastMessage("§a> §fИгрок §6$name §aочистил чат!");
     }
     else{
   $sender->sendMessage("Эту команду можно использовать только в игре!");
   }
     break;
   case "spawn":
   $safespawn = $sender->getLevel()->getSafeSpawn(); 
   $x = $safespawn->getX();
   $y = $safespawn->getY();
   $z = $safespawn->getZ();
   $sender->teleport(new Vector3($x, $y, $z));
   $sender->sendMessage("§a> §eТелепортация §f...");
   break;
   case "top":
   $sender->teleport(new Vector3($sender->getX(), 128, $sender->getZ()));
   $sender->sendMessage("§a> §eТелепортация §f...");
   break;
}
}
public function GodMode(EntityDamageEvent $e){ 
   $player = $e->getEntity(); 		
   if($player instanceof Player){ 			   if(isset($this->gods[$player->getName()])){ 				$e->setCancelled(true); 			
} 	
	} 
	} 
public function onDisable(){}
}
?>