<?php

namespace BlApi;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\CallbackTask;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\block\BlockBreakEvent;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as F;
use pocketmine\math\Vector3;
use pocketmine\item\Item;
use pocketmine\entity\Entity;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\level\Sound;
use pocketmine\block\Block;
use pocketmine\entity\Effect;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

  class Main extends PluginBase implements Listener
{
  	public $hack;
      private $banItems = array();
	  public $t;
	  public $hunger;
	  public $heal;
	
	/*
	* 
	* Включение сервера
	*
	*/
  	
    public function onEnable()
	{
		$this->EconomyC = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
		$this->perms = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
		
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "broadcaster")), 20 * 60);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "timer")), 20 * 3);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "bonus")), 20 * 1200);
		
		$this->distance = 3000000; //Ограничение мира
		
		$this->loadPlayers();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	      public function curl( $url ){
               $ch = curl_init( $url );
               curl_setopt( $ch, CURLOPT_RETURNTRANSFER, "true" );
               curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
               curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
               $response = curl_exec( $ch );
               curl_close( $ch );
               return $response;
          }
	
	      public function permsg(Player $p,$group){
		     $p->sendMessage("§8(§eGetStone§8)§f Твоя привилегия должна быть не ниже: §a$group");
		     $p->sendMessage("§8(§eGetStone§8)§f Приобрести§b $group §fможно на сайте: §cpay.get-stone.ru");
	}

       public function cmdc(PlayerCommandPreprocessEvent $e) {
           $p = $e->getPlayer();
           $msg = explode(" ",$e->getMessage());
             if(strtolower($msg[0] == "/me" || $msg[0] == "/msg" || $msg[0] == "/tell")) {
                $e->setCancelled();
                $p->sendMessage("§8(§cБезопасность§8)§f Эта команда §cзапрещена §fадминистрацией.");
     }
}
	
    public function bJoin(PlayerJoinEvent $event){
    	$p = $event->getPlayer();
        $data = date("H:i");
        $money = $this->EconomyC->myMoney($p);
        $onl = count($this->getServer()->getOnlinePlayers());
        $onlm = $this->getServer()->getMaxPlayers();
        $p->sendMessage("§8§l»§7-------§f*+*§7-------§f*+*§7-------§8«");
        $p->sendMessage("              §e§lGetMine §7§l ". $onl ."".F::DARK_GRAY."/§c". $onlm ."");
        $p->sendMessage("                   §7Твой баланс $money");
        $p->sendMessage("                §a§lpay.Get-Stone.ru");
        $p->sendMessage("§8§l»§7-------§f*+*§7-------§f*+*§7-------§8«");
		$p->sendTip("§l§eGetMine\n§7§l[". F::WHITE."". $onl ."". F::DARK_GRAY."/". F::WHITE ."". $onlm ."§7]");
         foreach($this->getServer()->getOnlinePlayers() as $pl){
                $group = $this->perms->getUserDataMgr()->getGroup($event->getPlayer())->getName();
            if($group == "Вип"){ 
          	$pl->sendMessage("§a+ §8(§bВип§8)§7 ".$event->getPlayer()->getName() ."");
          }
          if($group == "Супервип"){ 
          	$pl->sendMessage("§a+ §8(§dСупер вип§8)§7 ".$event->getPlayer()->getName() ."");
          }
          if($group == "Креатив"){
          	$pl->sendMessage("§a+ §8(§6Креатив§8)§7 ".$event->getPlayer()->getName() ."");
          }
           if($group == "Лорд"){
          	$pl->sendMessage("§a+ §8(§cЛорд§8)§7 ".$event->getPlayer()->getName() ."");
          }
          if($group == "Модератор"){
          	$pl->sendMessage("§a+ §8(§fМодератор§8)§7 ".$event->getPlayer()->getName() ."");
          }
          if($group == "Админ"){
          	$pl->sendMessage("§a+ §8(§4Админ§8)§7 ".$event->getPlayer()->getName() ."");
          }
          if($group == "Администратор"){
          	$pl->sendMessage("§a+ §8(§cАдминистратор§8)§7 ".$event->getPlayer()->getName() ."");
          }
          if($group == "Бессмертный"){
          	$pl->sendMessage("§a+ §8(§9Бессмертный§8)§7 ".$event->getPlayer()->getName() ."");
          }
          if($group == "Создатель"){
          	$pl->sendMessage("§a+ §8(§cСоздатель§8)§7 ".$event->getPlayer()->getName() ."");
          }
          if($group == "Зам.Создателя"){
          	$pl->sendMessage("§a+ §8(§cЗам.Создателя§8)§7 ".$event->getPlayer()->getName() ."");
          }
          if($group == "Властелин"){
          	$pl->sendMessage("§a+ §8(§cВластелин§8)§7 ".$event->getPlayer()->getName() ."");
          }
          if($group == "Легенда"){
          	$pl->sendMessage("§a+ §8(§5Легенда§8)§7 ".$event->getPlayer()->getName() ."");
          }
          if($group == "Флай"){
          	$pl->sendMessage("§a+ §8(§3Флай§8)§7 ".$event->getPlayer()->getName() ."");
      }
   }
}
            
	   public function onJoin(PlayerJoinEvent $e){
		  $group = $this->perms->getUserDataMgr()->getGroup($e->getPlayer())->getName();
          if($group == "Игрок"){ 
          	$e->getPlayer()->setDisplayName("§8(§eИгрок§8)§7 ".$e->getPlayer()->getName() ."");
              $e->getPlayer()->setNameTag("§8(§eИгрок§8)§7 ".$e->getPlayer()->getName() ."");
          }
          if($group == "Вип"){ 
          	$e->getPlayer()->setDisplayName("§8(§bВип§8)§7 ".$e->getPlayer()->getName() ."");
              $e->getPlayer()->setNameTag("§8(§bВип§8)§7 ".$e->getPlayer()->getName() ."");
          }
          if($group == "Супервип"){ 
          	$e->getPlayer()->setDisplayName("§8(§dСупер вип§8)§7 ".$e->getPlayer()->getName() ."");
              $e->getPlayer()->setNameTag("§8(§dСупер вип§8)§7 ".$e->getPlayer()->getName() ."");
          }
          if($group == "Креатив"){
          	$e->getPlayer()->setDisplayName("§8(§6Креатив§8)§7 ".$e->getPlayer()->getName() ."");
              $e->getPlayer()->setNameTag("§8(§6Креатив§8)§7 ".$e->getPlayer()->getName() ."");
          }
           if($group == "ДедМороз"){
          	$e->getPlayer()->setDisplayName("§8(§cДед Мороз§8)§7 ".$e->getPlayer()->getName() ."");
              $e->getPlayer()->setNameTag("§8(§cДед Мороз§8)§7 ".$e->getPlayer()->getName() ."");
          }
          if($group == "Модератор"){
          	$e->getPlayer()->setDisplayName("§8(§fМодератор§8)§7 ".$e->getPlayer()->getName() ."");
              $e->getPlayer()->setNameTag("§8(§fМодератор§8)§7 ".$e->getPlayer()->getName() ."");
          }
          if($group == "Админ"){
          	$e->getPlayer()->setDisplayName("§8(§4Админ§8)§7 ".$e->getPlayer()->getName() ."");
              $e->getPlayer()->setNameTag("§8(§4Админ§8)§7 ".$e->getPlayer()->getName() ."");
          }
          if($group == "Администратор"){
          	$e->getPlayer()->setDisplayName("§8(§cАдминистратор§8)§7 ".$e->getPlayer()->getName() ."");
              $e->getPlayer()->setNameTag("§8(§cАдминистратор§8)§7 ".$e->getPlayer()->getName() ."");
          }
          if($group == "Бессмертный"){
          	$e->getPlayer()->setDisplayName("§8(§9Бессмертный§8)§7 ".$e->getPlayer()->getName() ."");
              $e->getPlayer()->setNameTag("§8(§9Бессмертный§8)§7 ".$e->getPlayer()->getName() ."");
          }
          if($group == "Создатель"){
          	$e->getPlayer()->setDisplayName("§8(§cСоздатель§8)§7 ".$e->getPlayer()->getName() ."");
              $e->getPlayer()->setNameTag("§8(§cСоздатель§8)§7 ".$e->getPlayer()->getName() ."");
          }
          if($group == "Зам.Создателя"){
          	$e->getPlayer()->setDisplayName("§8(§cЗам.Создателя§8)§7 ".$e->getPlayer()->getName() ."");
              $e->getPlayer()->setNameTag("§8(§cЗам.Создателя§8)§7 ".$e->getPlayer()->getName() ."");
          }
          if($group == "Властелин"){
          	$e->getPlayer()->setDisplayName("§8(§cВластелин§8)§7 ".$e->getPlayer()->getName() ."");
              $e->getPlayer()->setNameTag("§8(§cВластелин§8)§7 ".$e->getPlayer()->getName() ."");
          }
          if($group == "Легенда"){
          	$e->getPlayer()->setDisplayName("§8(§5Легенда§8)§7 ".$e->getPlayer()->getName() ."");
              $e->getPlayer()->setNameTag("§8(§5Легенда§8)§7 ".$e->getPlayer()->getName() ."");
          }
          if($group == "Флай"){
          	$e->getPlayer()->setDisplayName("§8(§3Флай§8)§7 ".$e->getPlayer()->getName() ."");
              $e->getPlayer()->setNameTag("§8(§3Флай§8)§7 ".$e->getPlayer()->getName() ."");
    }
}

   public function noKick(PlayerPreLoginEvent $e){ 
      $p = $e->getPlayer(); 
      $name = $p->getName(); 
   foreach($p->getServer()->getOnlinePlayers() as $pl){ 
   if($n === $pl->getName()){ 
       $e->setKickMessage("§8(§cЗащита§8)\n§fИгрок с ником§b $name §fуже играет на сервере!");
       $e->setCancelled(true);
   }else{ 
       $e->setCancelled(false);
     }
  }
}

    public function bonus(){
		foreach ($this->getServer()->getOnlinePlayers() as $p) {
			$rand = mt_rand(50,100);
			$this->EconomyC->addMoney($p->getName(), $rand);
			$p->sendMessage("§8(§aБонус§8)§f Вы получили§a $rand Монеток §fза игру на сервере!");
	}
}
          
     public function PlayerInteractEvent(PlayerInteractEvent $e){
     	$p = $e->getPlayer();
         $block = $e->getBlock()->getId();
		 $inv = $p->getInventory()->getItemInHand()->getId();
		 $inv2 = $p->getInventory()->getItemInHand()->getName();
		
		 $x = $e->getBlock()->getX();
		 $y = $e->getBlock()->getY();
		 $z = $e->getBlock()->getZ();
		
		if($inv == 378){
			if(!$p->hasPermission("fapi.click")){
				return;
			}
			$name = $e->getBlock()->getName();
			$damg = $e->getBlock()->getDamage();
			$id = $e->getBlock()->getId();
			$p->sendMessage(F::GREEN."$name ".F::GRAY."| ".F::RED."x: $x ".F::GRAY."| ".F::GOLD."y: $y ".F::GRAY."| ".F::YELLOW."z: $z".F::GRAY." | ".F::AQUA.$id.":".$damg);
		}
		
	    if($inv == 325){
			$e->setCancelled(true);
			$p->sendMessage("§8(§cЗащита§8)§c Запрещено§f использовать!");
			return;
		}
		
		  if($x == -160 && $y == 72 && $z == 0){
			if(!isset($this->parkur[$p->getName()])){
                $this->parkur[$p->getName()] = 1;
                $rand = mt_rand(250,400);
			    $this->EconomyC->addMoney($p->getName(), $rand);
			    $p->sendMessage("§8(§aПаркур§8)§f Вы успешно прошли паркур ваш приз:§a $rand Монеток");
			    $p->sendMessage("§8(§aПаркур§8)§f Паркур проходиться только §a1 раз§f за перезагрузку.");
			    $p->teleport(new Vector3(-145,64,-4));
			 }else{
				$p->sendMessage("§8(§aПаркур§8)§f Паркур можно пройти только §a1 раз §fза перезагрузку.");
		         return;
		}
	}
}
	
	public function onExplode(EntityExplodeEvent $e){
		$e->setCancelled();
	}
	
	public function onPlayerPreLogin(PlayerPreLoginEvent $e){
		$p = $e->getPlayer();
		
		if(!$this->players->exists(strtolower($e->getPlayer()->getName())) && (count($this->getServer()->getOnlinePlayers()) >= 97))
		{
			$e->setKickMessage("§fHA CEPBEPE HET MECTA!\n§aКупите ".F::YELLOW."Вип".F::GREEN." на сайте\n".F::GOLD."»".F::AQUA." pay.get-stone.ru ".F::GOLD."«");
			$e->setCancelled(true);
		}
	}
	
	public function timer(){
		foreach($this->getServer()->getOnlinePlayers() as $p)
		{
			$this->t[$p->getName()] = 0;
		}
	}
	
	public function mQuit(PlayerQuitEvent $e)
	{
		$e->setQuitMessage(null);
	}
	
	public function broadcaster(){
		foreach($this->getServer()->getLevels() as $level){
			$level->save(\true);
		}
		foreach($this->getServer()->getOnlinePlayers() as $p){
			$p->save();
			if(!$p->hasPermission("capi.noad")){
				$rand = mt_rand(1, 9);
				if($rand == 1){$msg = "§fСписок всех команд сервера: §a/help";}
				if($rand == 2){$msg = "§fХочешь быть §aкрутым §fна сервере§a? §fКупи §eдонат §fна сайте: §cpay.get-stone.ru";}
				if($rand == 3){$msg = "§fДонат услуги: §cpay.get-stone.ru";}
				if($rand == 4){$msg = "§fНезнаешь где добыть ресурсы? пиши §a/mine";}
				if($rand == 5){$msg = "§fНе забудь вступить в нашу группу ВК §7- §a/gs_mcpe";}
				if($rand == 6){$msg = "§fНаша группа: §a/gs_mcpe";}
				if($rand == 7){$msg = "§fЕсть §aдонатер §fили §aгрифер §fкоторый вас обижает?\n§8(§cНапоминание§8)§f Сообщите о нарушителе:§a /report";}
				if($rand == 8){$msg = "§fХочешь§a бесплатную §fпривилегию?\n§8(§cНапоминание§8)§f Напиши команду§a /hack++ §fи испытай удачу!";}
				if($rand == 9){$mms = "§a> §fPAY.GET-STONE.RU§a <";}
				$p->sendMessage("§8(§cНапоминание§8) $msg");
				$p->sendTip("$mms");
				
			}
		}
	}
	
	public function dmg(EntityDamageEvent $e){
      $p = $e->getEntity();
  if($e instanceof EntityDamageByEntityEvent){
	 if($this->pvp[$p->getName()])
	     $e->setCancelled();
      if($this->pvp[$e->getDamager()->getName()])
	      $e->setCancelled();
    }
       if($this->god[$p->getName()] == 1)
           $e->setCancelled();
}
        
      public function onDamages(EntityDamageEvent $event){
         if($event->getCause() == EntityDamageEvent::CAUSE_FALL){
             $event->setCancelled();
      }
}
	
	  public function onPlayerDeath(PlayerDeathEvent $e){
		   $e->setDeathMessage(null);
		   $p = $e->getEntity();
		   $name = strtolower($p->getName());
	   if($p instanceof Player){
			$c = $p->getLastDamageCause();
			if($c instanceof EntityDamageByEntityEvent){
				$d = $c->getDamager();
				if($d instanceof Player){
					$this->getServer()->broadcastPopup("§3". $p->getName() ." §fбыл убит игроком:§b ". $d->getName() ."");
					$item = Item::get(397, 3, 1);
			        $item->setCustomName("§eГолова игрока§6 ".$player->getName());
			        $p->getLevel()->dropItem(new Vector3($p->getX(), $p->getY(), $p->getZ()), $item);
					  }
					}else{
				$this->getServer()->broadcastPopup(F::AQUA.$p->getName().F::WHITE." умер");
			}
		}		
		if($p->hasPermission("capi.save"))
		{
			unset($this->death[$p->getName()]);
			$this->death[$p->getName()] = new Position(
				round($p->getX()),
				round($p->getY()),
				round($p->getZ()),
				$p->getLevel()
			);
			$this->drops[$p->getName()][1] = $p->getInventory()->getArmorContents();
			$this->drops[$p->getName()][0] = $p->getInventory()->getContents();
			$e->setDrops(array());
			$p->sendMessage("§8(§eGetStone§8)§f Используйте: " .F::AQUA. "/back §f, чтобы вернуться на место смерти!");
		}
		else
		{
			$p->sendMessage("§8(§eGetStone§8)§f А у Вип игроков при смерти вещи§a сохраняются!");
			$p->sendMessage("§8(§eGetStone§8)§f Купить §aВип §fможно на сайте: §cpay.get-stone.ru ");
		}
	}
	
   public function PlayerRespawn(PlayerRespawnEvent $e){
		$p = $e->getPlayer();
		$p->setMaxHealth(40);
		$name = $p->getName();
		if($p->getPlayer()->hasPermission("capi.save"))
		{
			if(isset($this->drops[$p->getName()]))
			{
				$p->getInventory()->setContents($this->drops[$p->getName()][0]);
				$p->getInventory()->setArmorContents($this->drops[$p->getName()][1]);
				unset($this->drops[$p->getName()]);
				$p->sendMessage("§8(§eGetStone§8)§f Вы§c погибли,§f ваш инвентарь был§a сохранен!");
			}
		}
	}

            
	public function onChat(PlayerChatEvent $e){
		$p = $e->getPlayer();
		if($p->hasPermission("capi.spam")) return;
		if($this->t[$p->getName()] == 1){
			$p->sendMessage("§8(§aЧат§8) §fПожалуйста, §cне спамьте §fв чат!");
			$e->setCancelled();
		}else{
			$this->t[$p->getName()] = 1;
		}
	}
	
	public function mJoin(PlayerJoinEvent $ev){
		$ev->setJoinMessage(null);
		foreach($this->getServer()->getOnlinePlayers() as $p){
			$p->sendPopup(F::RED. $ev->getPlayer()->getName(). F::WHITE. " зашёл на сервер!");
  }
}
		
     public function EntityDamageEvent(EntityDamageEvent $e){
     	$p = $e->getEntity();
         $v = new Vector3(
			$p->getLevel()->getSpawnLocation()->getX(),
			$p->getPosition()->getY(),
			$p->getLevel()->getSpawnLocation()->getZ()
		);
		$r = $this->getServer()->getSpawnRadius();
		if($p instanceof Player)
		{
			if($e instanceof EntityDamageByEntityEvent)
			{
				$d = $e->getDamager();
				if($d instanceof Player)
				{
		     if(($p->getPosition()->distance($v) <= $r)){
				  $e->setCancelled();
				  $d->sendMessage("§8(§eGetStone§8)§f Запрещено §cдраться §fна спавне!");
						return;
					}
					
					if(($d->getGamemode() == 1) && ($p->getGamemode() == 0))
					{
						$e->setCancelled();
						$d->sendMessage("§8(§eGetStone§8)§f Вы§c не можете §fдраться в режиме §aКреатив!");
						return;
					}
					
					if($d->getAllowFlight(true)){
						$e->setCancelled();
						$d->sendMessage("§8(§eGetStone§8)§f Вы §cне можете §fдраться в режиме §aПолета!");
						return;
					}
					
					if($p->getAllowFlight(true)){
						$e->setCancelled();
						$d->sendMessage("§8(§eGetStone§8)§f У противника включен§a режим Полета!");
						return;
			}
	     }
	   }
	}
 }
 
      public function worldBorder(PlayerMoveEvent $e){
		$p = $e->getPlayer();
		$pos = $p->getLevel()->getSpawnLocation();
		$vector = new Vector3($pos->getX(),$p->getPosition()->getY(),$pos->getZ());
		if(!$p->hasPermission("capi.border"))
		{
			if(floor($p->distance($vector)) >= $this->distance)
			{
				$e->setCancelled();
				$p->sendMessage("§8(§aГраница§8)§f Здесь граница мира! Если вы застряли тут напишите: §b/spawn");
                $p->sendMessage("§8(§aГраница§8)§f Игроки выше §bВипа §fмогут пройти за §cграницу мира!");
			}
		}
	}
	
	public function itemHeld(PlayerItemHeldEvent $e)
	{
		$hand = $e->getPlayer()->getInventory()->getItemInHand()->getId();
		if($hand == 341){
			$e->getPlayer()->sendPopup("§aПроверка региона!");
			return;
		}
	}

       public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
        $date = date("d.m.y H:i");
        $id = "262396193"; //ID от вк страницы для /report
        $token = "d386420306a1d031ac01ef82f23b1cc9df840b6044526c79e42fc75fa5ffc0ff0eb9f4d79a8f133a09319"; //Токен от страницы для /report
    	$level = $this->getServer()->getDefaultLevel();
	    $x = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getX();
        $y = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getY();
		$z = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getZ();
       	switch($cmd->getName()){
              case "hack++":
               if(!isset($this->hack[$sender->getName()])){
                $this->hack[$sender->getName()] = 1;
                $sender->sendMessage("§8(§aВзлом§8)§f У вас не получилось §cвзломать §fадминку.");
                $sender->sendMessage("§8(§aВзлом§8)§f Попробуйте повторить попытку после §aперезагрузки§f сервера.");
                }else{
                	$sender->sendMessage("§8(§aВзлом§8)§f Вы уже §cвзламывали §fадминку, попробуйте после перезагрузки сервера.");
                }
                   break;
                   
                 case "spawn":
			      if($sender Instanceof Player){
				    $sender->teleport(new Vector3($x, $y, $z, $level));
				    $sender->sendMessage("§8(§eGetStone§8)§f Телепортация§7...");
			     }else{
				    $sender->sendMessage(F::RED."Комманда вводится только от имени игрока.");
			     }
			       break;
			
			     case "sleep":
			      $sender->sleepOn(new Vector3($sender->getX(), $sender->getY()+1, $sender->getZ()));
				  $sender->sendMessage("§8(§aОтдых§8)§f Вы успешно легли на грязный пол.");
				    break;
			
			      case "home":
			       if($sender Instanceof Player){
				   if($this->getHomeX($sender->getName()) != null && $this->getHomeY($sender->getName()) != null && $this->getHomeZ($sender->getName()) != null){
					   $sender->teleport(new Vector3($this->getHomeX($sender->getName()), $this->getHomeY($sender->getName()), $this->getHomeZ($sender->getName()), $level));
					   $sender->sendMessage("§8(§eGetStone§8)§f Телепортация§7...");
				   }else{
					   $sender->sendMessage("§8(§eGetStone§8)§f Вы еще §cне ставили§f точку дома!");
				       }
			       }else{
				       $sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
			           }
			        break;
			
			  case "report":
				if($args[0] == ""){
					$sender->sendMessage("§8(§aРепорт§8)§f Используйте: §b/report §c(Ваше сообщение)");
				}else{
				 if(!isset($this->report[$sender->getName()])){
                    $this->report[$sender->getName()] = 1;
					$name = $sender->getName();
					$msg = implode(" ", $args);
					$msgg = "($date) (Репорт) ". $name .": ". $msg ."";
                    $mms = urlencode($msgg);
					$this->curl("https://api.vk.com/method/messages.send?message=$mms&user_id=$id&access_token=$token");
					$sender->sendMessage("§8(§aРепорт§8)§f Ваша жалоба§a успешно§f отправлена!");
					$sender->sendMessage("§8(§aРепорт§8)§f В ближайшее время наш администратор ее расмотрит.");
					$sender->sendMessage("§8(§aРепорт§8)§f Спасибо за §eВашу помощь!");
					  }else{
						$sender->sendMessage("§8(§aРепорт§8)§c Вы уже использовали репорт!");
						$sender->sendMessage("§8(§aРепорт§8)§f Повторите попытку после перезагрузки сервера.");
					 }
				}
						break;
			
			     case "min":
			        if(!$sender Instanceof Player){
				       $sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
				           break;
			             }
			        if(!$sender->getPlayer()->hasPermission("capi.cmd.size")){
				         $this->permsg($sender,"Вип");
				        break;
			         }
			            $sender->setDataProperty(Player::DATA_SCALE, Player::DATA_TYPE_FLOAT, 0.50);
                        $sender->sendMessage("§8(§eGetStone§8)§f Вы успешно стали §bмаленьким §fдля всех!");
                      break;
                      
                      case "big":
			        if(!$sender Instanceof Player){
				       $sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
				           break;
			             }
			        if(!$sender->getPlayer()->hasPermission("capi.cmd.size")){
				         $this->permsg($sender,"Вип");
				        break;
			         }
			            $sender->setDataProperty(Player::DATA_SCALE, Player::DATA_TYPE_FLOAT, 2.50);
                        $sender->sendMessage("§8(§eGetStone§8)§f Вы успешно стали §bогромным §fдля всех!");
                      break;
                      
                  case "norm":
                    if(!$sender Instanceof Player){
				       $sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
				           break;
			             }
			        if(!$sender->getPlayer()->hasPermission("capi.cmd.size")){
				         $this->permsg($sender,"Вип");
				        break;
			         }
			             $sender->setDataProperty(Player::DATA_SCALE, Player::DATA_TYPE_FLOAT, 1);
                         $sender->sendMessage("§8(§eGetStone§8)§f Вы успешно стали §bобычным §fдля всех!");
                       break;
			
			      case "tpall":
			       if(!$sender Instanceof Player){
				       $sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
				    break;
			       }
			  if(!$sender->getPlayer()->hasPermission("capi.cmd.tpall")){
				   $this->permsg($sender,"Создатель");
				 break;
			 }
			    foreach($this->getServer()->getOnlinePlayers() as $p){
				$p->teleport(new Vector3($sender->getX(), $sender->getY()+1, $sender->getZ()));
				$p->sendMessage("§8(§eGetStone§8) " .F::AQUA. $sender->getName(). F::WHITE ." телепортировал всех в одну точку!");
			}
			   break;
			
			    case "dupe":
			     if(!$sender Instanceof Player){
				    $sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
				  break;
			   }
			    if(!$sender->getPlayer()->hasPermission("capi.cmd.dupe")){
				    $this->permsg($sender,"Делюкс");
				   break;
			       }
			   if($sender->getGamemode() !== 0){
				   $sender->sendMessage("§8(§aДюп§8)§f Вы§c не можете §fдюпать в режиме:§c Креатива!");
			     }else{
				  $inv = $sender->getInventory();
				  $i = $inv->getItemInHand();
				  $invid = $i->getId();
			  if($invid == 57 || 56 || 264 || 41 || 14 || 266 || 265 || 15 || 42 || 0 || 175 || 397 || 384 || 247)
				{
					$sender->sendMessage("§8(§aДюп§8)§f Вы§c не можете §fдюпнуть данный §bпредмет!");
				}
				else
				{
					$sender->sendMessage("§8(§aДюп§8)§f Вы§b успешно§f дюпнули предмет в руке");
                    $i->setCount(64);
                    $inv->addItem($i);
					$this->getLogger()->info("§8(§aДюп§8)§b ".$sender->getName()." §fдюпнул(а)§a ID:§b ".$inv->getItemInHand()->getId());
				   }
			   }
			    break;
			
			    case "mine":
			      $sender->teleport(new Vector3(-187,61,10));
			      $sender->sendMessage("§8(§aАвто-Шахта§8)§f Телепортация...");
			   break;
			
			      case "vips":
			       if(!$sender Instanceof Player){
				      if($sender->isOp()){
					    if(isset($args[0]) && isset($args[1])){
						  if($args[0] == "add"){
							$who_player = $this->getValidPlayer($args[1]);
							if($who_player instanceof Player){
								$name = $who_player->getName();
							}else{
								$name = $args[1];
							}
							if($this->addPlayer($name)){
								$sender->sendMessage("§8(§eGetStone§8)§f В список добавлен(а)".F::AQUA." $name");
							}else{
								$sender->sendMessage("§8(§eGetStone§8)§f $name §cуже добавлен(а) §fв список!");
							}
						}else{
							$sender->sendMessage("§8(§eGetStone§8)§f Используйте:§b /vips add §7(§eигрок§7)");
						}
					}else{
						$sender->sendMessage("§8(§eGetStone§8)§f Использование:§b /vips add §7(§eигрок§7)");
					}
				}else{
					$sender->sendMessage("§8(§aFullCraft§8)§f У вас§c нет прав §fна использование этой комманды!");
				   }
			   }
			   break;
			
			      case "clearchat":
			      case "cc":
			        if(!$sender->hasPermission("capi.cmd.cc")){
				       $this->permsg($sender,"Модератор");
				     break;
			       }
			 foreach($this->getServer()->getOnlinePlayers() as $p){
				$p->sendMessage("\n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n ");
				$p->sendMessage("§8(§aЧат§8)§b ".$sender->getName()."§f очистил(а) чат!");
			}
			break;
			
			   case "god":
			   if(!$sender Instanceof Player){
				    $sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
				  break;
			}
			   if(!$sender->getPlayer()->hasPermission("capi.cmd.god")){
				    $this->permsg($sender,"Админ");
				   break;
			   }
			       if($this->god[$sender->getName()] == 0){
                       $sender->sendMessage("§8(§aРежим§8)§f Режим §cбога §fвключен.");
                       $this->god[$sender->getName()] = 1;
                }else{
                       $sender->sendMessage("§8(§aРежим§8)§f Режим §cбога §fвыключен.");
                       $this->god[$sender->getName()] = 0;
             }
              break;
			
			   case "suicide":
			if(!$sender Instanceof Player){
				$sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
				break;
			}
			if(!$sender->getPlayer()->hasPermission("capi.cmd.suicide")){
				$this->permsg($sender,"Флай");
				break;
			}
			    $sender->setHealth(0);
			    $sender->sendMessage("§8(§eGetStone§8)§f Вы успешно совершили суицид!");
			    $this->getServer()->broadcastPopup(F::AQUA.$sender->getName().F::WHITE." совершил(а) суицид.");
			   break;
			
			  case "top":
			if(!$sender Instanceof Player){
				$sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
				break;
			}
			if(!$sender->getPlayer()->hasPermission("capi.cmd.gm")){
				$this->permsg($sender,"Креатив");
				break;
			}
			$sender->teleport(new Vector3($sender->getX(), 128, $sender->getZ()));
			$sender->sendMessage("§8(§eGetStone§8)§f Телепортация..");
			break;
			
			      case "sethome":
			       if($sender Instanceof Player){
				       $this->setHome($sender->getName(), $sender->getX(), $sender->getY(), $sender->getZ());
				       $sender->sendMessage("§8(§eGetStone§8)§f Точка дома§b успешно§f установлена!");
			    }else{
				       $sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
			       }
			     break;
			
			     case "clear":
			      if(!$sender Instanceof Player){
				     $sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
				       break;
			      }
			   if(!$sender->getPlayer()->hasPermission("capi.cmd.clear")){
				    $this->permsg($sender,"Вип");
				break;
			   }
			        $sender->getInventory()->clearAll();
			        $sender->sendMessage("§8(§eGetStone§8)§f Вы§b успешно§f очистили свой инвентарь!");
			     break;
			
			       case "donate":
			        $sender->sendMessage("§8* §eВип §7- §f56руб.§8(§fПодробнее: §apay.get-stone.ru§8)");
			        $sender->sendMessage("§8* §2Премиум §7- §f96руб.§8(§fПодробнее: §apay.get-stone.ru§8)");
			        $sender->sendMessage("§8* §bКреатив §7- §f156руб.§8(§fПодробнее: §apay.get-stone.ru§8)");
			        $sender->sendMessage("§8* §dАдмин §7- §f246руб.§8(§fПодробнее: §apay.get-stone.ru§8)");
			        $sender->sendMessage("§8* §3Модер §7- §f312руб.§8(§fПодробнее: §apay.get-stone.ru§8)");
			        $sender->sendMessage("§8* §cКр○н○р §7- §f454руб.§8(§fПодробнее: §apay.get-stone.ru§8)");
			    break;
			
			     case "gm":
			      if(!$sender Instanceof Player){
				    $sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
				break;
			   }
			     if(!$sender->getPlayer()->hasPermission("capi.cmd.gm")){
				      $this->permsg($sender,"Креатив");
				   break;
			     }
			if($sender->getGamemode() == 1){
				$sender->setGamemode(0);
				$sender->sendMessage("§8(§aРежим§8)§f Вы§b успешно§f сменили свой игровой режим на: §aВыживание.");
			}else{
				$sender->setGamemode(1);
				$sender->sendMessage("§8(§aРежим§8)§f Вы§b успешно§f сменили свой игровой режим на: §cКреатив.");
			}
			    break;
			
			   case "fly":
			if(!$sender Instanceof Player){
				$sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
				break;
			}
			if(!$sender->getPlayer()->hasPermission("capi.cmd.fly")){
				$this->permsg($sender,"Флай");
				break;
			}
			if($sender->getAllowFlight(true)){
				$sender->setAllowFlight(false);
				$sender->sendMessage("§8(§eGetStone§8)§f Вы§b успешно §cотключили §fрежим полёта!");
			}else{
				$sender->setAllowFlight(true);$sender->sendMessage("§8(§eGetStone§8)§f Вы§b успешно §aвключили §fрежим полёта!");
			}
			break;
			
			      case "back":
			if(!$sender Instanceof Player){
				$sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
				break;
			}
			if(!$sender->getPlayer()->hasPermission("capi.save")){
				$this->permsg($sender,"Вип");
				break;
			}
			if($this->death[$sender->getName()] instanceof Position){
				$sender->teleport($this->death[$sender->getName()]);
				$sender->sendMessage("§8(§eGetStone§8)§f Вы§b успешно§f телепортировались на место§c смерти!");
			}else{
				$sender->sendMessage("§8(§eGetStone§8)§f Вы §cне умерали!");
			}
			break;
			
			     case "heal":
			if(!$sender Instanceof Player){
				$sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
				break;
			}
			if(!$sender->getPlayer()->hasPermission("capi.cmd.heal")){
				$this->permsg($sender,"Вип");
				break;
			}
			if($sender->getGamemode() == 0){
				$sender->setHealth(40);
				$sender->setFood(20);
				$sender->sendMessage("§8(§eGetStone§8)§f Вы§b успешно§f восстановили свои §aжизни §fи §eголод!");
			}else{
				$sender->sendMessage("§8(§eGetStone§8)§f Вы§c не можете §fиспользовать данную команду в режиме: §cКреатива!");
			}
			break;
			    
			case "vanish":
			case "v":
			if(!$sender Instanceof Player){
				$sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
				break;
			}
			if(!$sender->getPlayer()->hasPermission("capi.cmd.vanish")){
				$this->permsg($sender,"Модератор");
				break;
			}
			if(isset($args[0])){
				if($args[0] == "on"){
					$effect = Effect::getEffect(14)->setVisible(false)->setAmplifier(10)->setDuration(1928000);
                    $sender->addEffect($effect);
					$sender->sendMessage("§8(§7Невидимость§8)§f Вы §aвключили §fневидимость!");
				}
				if($args[0] == "off"){
					$sender->removeAllEffects();
					$sender->sendMessage("§8(§7Невидимость§8)§f Вы §cвыключили §fневидимость!");
				    }
			    }else{
				    $sender->sendMessage("§8(§7Невидимость§8)§f Используйте:§b /v on §fили §coff!");
			     }
			        break;
			}
	   }
######################################################################################################
      public function why($entityName)
	{
		if(!is_file($this->getDataFolder()."data/homes/".$entityName.".yml"))
		{
			$this->createData($entityName);
		}
	}
	public function createData($entityName)
	{
		if(!is_file($this->getDataFolder()."data/homes/".$entityName.".yml"))
		{
			@mkdir($this->getDataFolder() . "data/homes/");
			$data = new Config($this->getDataFolder() . "data/homes/".$entityName.".yml", Config::YAML);
			$data->set("x", null);
			$data->set("y", null);
			$data->set("z", null);
			$data->save();
		}
	}
	public function getHomeX($entityName)
	{
		$this->why($entityName);
		$sFile = (new Config($this->getDataFolder() . "data/homes/".$entityName.".yml", Config::YAML))->getAll();return $sFile["x"];
	}
	public function getHomeY($entityName)
	{
		$this->why($entityName);
		$sFile = (new Config($this->getDataFolder() . "data/homes/".$entityName.".yml", Config::YAML))->getAll();
		return $sFile["y"];
	}
	public function getHomeZ($entityName)
	{
		$this->why($entityName);
		$sFile = (new Config($this->getDataFolder() . "data/homes/".$entityName.".yml", Config::YAML))->getAll();return $sFile["z"];
	}
	public function setHome($entityName, $x, $y, $z)
	{
		$this->why($entityName);
		$sFile = (new Config($this->getDataFolder() . "data/homes/".$entityName.".yml", Config::YAML))->getAll();
		$sFile["x"] = (int) $x;
		$sFile["y"] = (int) $y;
		$sFile["z"] = (int) $z;
		$fFile = new Config($this->getDataFolder() . "data/homes/".$entityName.".yml", Config::YAML);
		$fFile->setAll($sFile);
		$fFile->save();
	}
##########################################################################################################################################
	private function loadPlayers()
	{
		@mkdir($this->getDataFolder(), 0777, true);
		$this->players = new Config($this->getDataFolder() . "/data/vipslots.txt", Config::ENUM, array());
	}
	private function getValidPlayer($name)
	{
		$player = $this->getServer()->getPlayer($name);
		return $player instanceof Player ? $player : $this->getServer()->getOfflinePlayer($name);
	}
	public function addPlayer($player)
	{
		$name = $this->getValidPlayer($player);
		if($name instanceof Player)
		{
			$p = strtolower($name->getName());
		}
		else
		{
			$p = strtolower($player);
		}
		if($this->players->exists($p)) return false;
			$this->players->set($p, true);
			$this->players->save();
		return true;
	}
	public function remPlayer($player)
	{
		$name = $this->getValidPlayer($player);
		if($name instanceof Player)
		{
			$p = strtolower($name->getName());
		}
		else
		{
			$p = strtolower($player);
		}
		if(!$this->players->exists($p)) return false;
			$this->players->remove($p);
			$this->players->save();
		return true;
	 }
   }