<?php
namespace FApi;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\CallbackTask;

use pocketmine\event\Listener;
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

class FApi extends PluginBase implements Listener
{
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
		$this->EconomyS = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");// Экономика
		$this->guard = $this->getServer()->getPluginManager()->getPlugin("FGuard");// Приват
		$this->perms = $this->getServer()->getPluginManager()->getPlugin("PurePerms");// Приват
		$this->sauth = $this->getServer()->getPluginManager()->getPlugin("ServerAuth");// Авторизация
		
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "broadcaster")), 20 * 60);// Автосообщения
		
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "timer")), 20 * 3);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "heal")), 20 * 60);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "hunger")), 20 * 60);
		
		$this->spawn = 120;// ДИСТАНЦИЯ СПАУНА
		$this->distance = 1500;// ДИСТАНЦИЯ МИРА
		
		$this->loadPlayers();
		$this->protectworlds = $this->getConfig()->get("worlds");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->path = $this->getDataFolder();
		@mkdir($this->path);
		$this->cfg = new Config($this->path."config.yml", Config::YAML,array());
		
		@mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
		
		$banItems = $this->getConfig()->get("ban-items");
        foreach($banItems as $itemsBan){
            $this->banItems[] = $itemsBan;
        }
	}
	
	
	public function onChat(PlayerChatEvent $e){
		$p = $e->getPlayer();
		if($p->hasPermission("fapi.spam")) return;
		if($this->t[$p->getName()] == 1){
			$p->sendMessage(F::RED."§7(§bFire§cCraft§7)§a Пожалуйста§f, не спамьте в чат§c!");
			$e->setCancelled();
		}else{
			$this->t[$p->getName()] = 1;
		}
	}
	
	public function broadcaster(){
		foreach($this->getServer()->getLevels() as $level){
			$level->save(\true);
		}
		foreach($this->getServer()->getOnlinePlayers() as $p){
			$p->save();
			if(!$p->hasPermission("fapi.noad")){
				$rand = mt_rand(1, 5);
				if($rand == 1){$msg = F::YELLOW."Напиши ".F::RED."/help, ".F::YELLOW."если хочешь узнать доступные Команды!";}
				if($rand == 2){$msg = F::YELLOW."Купить привилегии можно на нашем сайте - ".F::RED."pay.mys.su";}
				if($rand == 3){$msg = F::YELLOW."Хочешь крутой донат? Приобрети на сайте - ".F::RED."pay.mys.su";}
				if($rand == 4){$msg = F::YELLOW."Собрал много коинсов? Обменяй их в обменнике: ".F::RED."/coins!";}
				if($rand == 5){$msg = F::YELLOW."Не забудь вступить в нашу группу ВК - ".F::RED."vk.com/myssu";}
				$p->sendMessage(F::GOLD. "↱");
				$p->sendMessage(F::GOLD. "| ".F::YELLOW.$msg);
				$p->sendMessage(F::GOLD. "↳");
			}
		}
	}
	
	public function permsg(Player $p,$group){
		$p->sendMessage(F::YELLOW. "§7(§bFire§cCraft§7)" .F::GOLD. "§b Ваша§f привилегия должна быть не ниже: ".F::GREEN.$group);
		$p->sendMessage(F::YELLOW. "§7(§bFire§cCraft§7)" .F::GOLD. "§b Приобрести " .F::GREEN.$group.F::GOLD. " §fможно на сайте: ".F::YELLOW."pay.fire-pe.ru");
	}
	
    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
		$level = $this->getServer()->getDefaultLevel();
		$x = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getX();
        $y = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getY();
		$z = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getZ();
        switch($cmd->getName()){

			case "spawn":
			if($sender Instanceof Player){
				$sender->teleport(new Vector3($x, $y, $z, $level));
				$sender->sendMessage(F::YELLOW. "§7(§bFire§cCraft§7)§b Телепортация...");
			}else{
				$sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
			}
			break;
			
			case "sethome":
			if($sender Instanceof Player){
				$this->setHome($sender->getName(), $sender->getX(), $sender->getY(), $sender->getZ());
				$sender->sendMessage(F::YELLOW. "§7(§bFire§cCraft§7)" .F::GREEN. " §fТочка §bдома§a успешно§f установлена§c!");
			}else{
				$sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
			}
			break;

			case "home":
			if($sender Instanceof Player){
				if($this->getHomeX($sender->getName()) != null && $this->getHomeY($sender->getName()) != null && $this->getHomeZ($sender->getName()) != null){
					$sender->teleport(new Vector3($this->getHomeX($sender->getName()), $this->getHomeY($sender->getName()), $this->getHomeZ($sender->getName()), $level));
					$sender->sendMessage(F::YELLOW. "§7(§bFire§cCraft§7)§b Телепортация...");
				}else{
					$sender->sendMessage(F::DARK_RED. "§7(§bFire§cCraft§7)" .F::RED. " §bВы§f еще не ставили точку§a дома§c!");
				}
			}else{
				$sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
			}
			break;
			
			case "tpall":
			if(!$sender Instanceof Player){
				$sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
				break;
			}
			if(!$sender->getPlayer()->hasPermission("fapi.cmd.tpall")){
				$this->permsg($sender,"Бог(а)");
				break;
			}
			foreach($this->getServer()->getOnlinePlayers() as $p){
				$p->teleport(new Vector3($sender->getX(), $sender->getY()+1, $sender->getZ()));
				$p->sendMessage(F::YELLOW. "§7(§bFire§cCraft§7)" .F::RED. " §aБог: " .F::GREEN. $sender->getName(). F::GOLD . "§f телепортировал§b всех§f в одну точку§c!");
			}
			break;
			
			case "clear":
			if(!$sender Instanceof Player){
				$sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
				break;
			}
			if(!$sender->getPlayer()->hasPermission("fapi.cmd.clear")){
				$this->permsg($sender,"Премиум(а)");
				break;
			}
			$sender->getInventory()->clearAll();
			$sender->sendMessage(F::YELLOW. "§7(§bFire§cCraft§7)" .F::GOLD. " §bВы§a успешно§f очистили свой инвентарь§c!");
			break;
			
			case "gm":
			if(!$sender Instanceof Player){
				$sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
				break;
			}
			if(!$sender->getPlayer()->hasPermission("fapi.cmd.gm")){
				$this->permsg($sender,"Креатив(а)");
				break;
			}
			if($sender->getGamemode() == 1){
				$sender->setGamemode(0);
				$sender->sendMessage(F::YELLOW. "§7(§bFire§cCraft§7)" .F::GOLD. " §bВы§a успешно§f сменили§b свой§f игровой режим на: ".F::GREEN."Выживание".F::GOLD.".");
			}else{
				$sender->setGamemode(1);
				$sender->sendMessage(F::YELLOW. "§7(§bFire§cCraft§7)" .F::GOLD. " §bВы§a успешно§f сменили§b свой§f игровой режим на: ".F::AQUA."Креатив".F::GOLD.".");
			}
			break;
			
			case "fly":
			if(!$sender Instanceof Player){
				$sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
				break;
			}
			if(!$sender->getPlayer()->hasPermission("fapi.cmd.fly")){
				$this->permsg($sender,"Флай(я)");
				break;
			}
			if($sender->getAllowFlight(true)){
				$sender->setAllowFlight(false);
				$sender->sendMessage(F::YELLOW. "§7(§bFire§cCraft§7)" .F::GOLD. " §bВы§a успешно ".F::RED."отключили ".F::GOLD."§fрежим полёта§c!");
			}else{
				$sender->setAllowFlight(true);$sender->sendMessage(F::YELLOW. "§7(§bFire§cCraft§7)" .F::GOLD. " §bВы§a успешно ".F::GREEN."включили ".F::GOLD."§fрежим полёта");
			}
			break;
			
			case "back":
			if(!$sender Instanceof Player){
				$sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
				break;
			}
			if(!$sender->getPlayer()->hasPermission("fapi.save")){
				$this->permsg($sender,"Премиум(а)");
				break;
			}
			if($this->death[$sender->getName()] instanceof Position){
				$sender->teleport($this->death[$sender->getName()]);//unset($this->death[$sender->getName()]);
				$sender->sendMessage(F::RED. "§7(§bFire§cCraft§7)§b Вы§a успешно§f телепортировались на место§6 смерти§c!");
			}else{
				$sender->sendMessage(F::RED. "§7(§bFire§cCraft§7) §bВам§f нужно сначало§6 умереть§c!");
			}
			break;
			
			case "heal":
			if(!$sender Instanceof Player){
				$sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
				break;
			}
			if(!$sender->getPlayer()->hasPermission("fapi.cmd.heal")){
				$this->permsg($sender,"Вип(а)");
				break;
			}
			if($sender->getGamemode() == 0){
				$sender->setHealth(20);
				$sender->setFood(20);
				$sender->sendMessage(F::YELLOW."§7(§bFire§cCraft§7)".F::GOLD." §bВы§a успешно§f восстановили§b свои ".F::GREEN."жизни ".F::GOLD."и ".F::AQUA."голод§c!");
			}else{
				$sender->sendMessage(F::DARK_RED. "§7(§bFire§cCraft§7)".F::RED." §bВы§f не можете использовать§a данную§f команду в режиме: §eКреатива§c!");
			}
			break;
			
			case "dupe":
			if(!$sender Instanceof Player){
				$sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
				break;
			}
			if(!$sender->getPlayer()->hasPermission("fapi.cmd.dupe")){
				$this->permsg($sender,"Лорд(а)");
				break;
			}
			if($sender->getGamemode() !== 0){
				$sender->sendMessage(F::DARK_RED. "§7(§bFire§cCraft§7)" .F::RED. " §bВы§f не можете дюпать в режиме:§e Креатива§!");
			}else{
				$inv = $sender->getInventory();
				$i = $inv->getItemInHand();
				$invid = $i->getId();
				if($invid == 57 || 56 || 264 || 41 || 14 || 266 || 265 || 15 || 42 || 0 || 175 || 397 || 384 || 247)
				{
					$sender->sendMessage(F::DARK_RED. "§7(§bFire§cCraft§7)" .F::RED. " §bВы§f не можете дюпнуть данный §aпредмет§c!");
				}
				else
				{
					$sender->sendMessage(F::YELLOW. "§7(§bFire§cCraft§7)" .F::GOLD. " §bВы§a умпешно§f дюпнули§a предмет§f в руке");$i->setCount(64);$inv->addItem($i);
					$this->getLogger()->info(F::GREEN."§7(§bFire§cCraft§7)§b ".$sender->getName()." §fдюпнул(а)§a ID:§6 ".$inv->getItemInHand()->getId());
				}
			}
			break;
				
			case "vanish":
			case "v":
			if(!$sender Instanceof Player){
				$sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
				break;
			}
			if(!$sender->getPlayer()->hasPermission("fapi.cmd.vanish")){
				$this->permsg($sender,"Вип(а)");
				break;
			}
			if(isset($args[0])){
				if($args[0] == "on"){
					$effect = Effect::getEffect(14)->setVisible(false)->setAmplifier(10)->setDuration(1928000);$sender->addEffect($effect);
					$sender->sendMessage(F::YELLOW. "§7(§bFire§cCraft§7)" .F::GOLD. " §bВы " .F::GREEN. "включили " .F::GOLD. "§7невидимость§c!");
				}
				if($args[0] == "off"){
					$sender->removeAllEffects();
					$sender->sendMessage(F::YELLOW. "§7(§bFire§cCraft§7)" .F::GOLD. " §bВы " .F::RED. "выключили " .F::GOLD. "§7невидимость§c!");
				}
			}else{
				$sender->sendMessage(F::YELLOW. "§7(§bFire§cCraft§7)" .F::GOLD. " §aИспользование:§b /vanish" .F::GREEN. " on " .F::GOLD. "или " .F::RED. "off§c!");
			}
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
								$sender->sendMessage(F::YELLOW. "§7(§bFire§cCraft§7)".F::WHITE."§a В список добавлен(а)".F::WHITE." $name");
							}else{
								$sender->sendMessage(F::YELLOW. "§7(§bFire§cCraft§7)".F::GOLD." $name ".F::WHITE."§aуже добавлен(а) в список§c!");
							}
						}else{
							$sender->sendMessage(F::YELLOW."§7(§bFire§cCraft§7)§a Использование:§b /vips add §7(§eигрок§7)");
						}
					}else{

						$sender->sendMessage(F::YELLOW."§7(§bFire§cCraft§7)§a Использование:§b /vips add §7(§eигрок§7)");
					}
				}else{
					$sender->sendMessage(F::RED."§7(§bFire§cCraft§7)§b У вас§f нет прав на использование этой комманды§c!");
				}
			}
			break;
			
			case "top":
			if(!$sender Instanceof Player){
				$sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
				break;
			}
			if(!$sender->getPlayer()->hasPermission("fapi.cmd.top")){
				$this->permsg($sender,"Креатив(а)");
				break;
			}
			$sender->teleport(new Vector3($sender->getX(), 128, $sender->getZ()));
			$sender->sendMessage(F::YELLOW."§7(§bFire§cCraft§7)§b Телепортация...");
			break;
			
			case "suicide":
			if(!$sender Instanceof Player){
				$sender->sendMessage(F::RED. "Комманда вводится только от имени игрока.");
				break;
			}
			if(!$sender->getPlayer()->hasPermission("fapi.cmd.suicide")){
				$this->permsg($sender,"Основатель(я)");
				break;
			}
			$sender->setHealth(0);
			$sender->sendMessage(F::RED."§7(§bFire§cCraft§7)§b Вы§c повесились...");
			$this->getServer()->broadcastTip(F::DARK_AQUA.$sender->getName().F::GOLD." повесился(ась)");
			break;
			
			case "clearchat":
			case "cc":
			if(!$sender->hasPermission("fapi.cmd.cc")){
				$this->permsg($sender,"Креатив(а)");
				break;
			}
			foreach($this->getServer()->getOnlinePlayers() as $p){
				$p->sendMessage(" \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n \n ");
				$p->sendMessage(F::RED."§7(§bFire§cCraft§7) ".$sender->getName().F::GOLD."§b очистил(а)§f чат§c!");
			}
			break;
			
			case "donate":
			$sender->sendMessage(F::RED. "-".F::YELLOW." §bФлай §f- ".F::GREEN."15  руб".F::WHITE."§6/навсегда");
			$sender->sendMessage(F::RED. "-".F::YELLOW." §bВип §f- ".F::GREEN."45 руб".F::WHITE."§6/навсегда");
			$sender->sendMessage(F::RED. "-".F::YELLOW." §bПремиум §f- ".F::GREEN."85 руб".F::WHITE."§6/навсегда");
			$sender->sendMessage(F::RED. "-".F::YELLOW." §bКреатив §f-  ".F::GREEN."145 руб".F::WHITE."§6/навсегда");
			$sender->sendMessage(F::RED. "-".F::YELLOW." §bЛорд §f- ".F::GREEN."265 руб".F::WHITE."§6/навсегда");
			$sender->sendMessage(F::RED. "-".F::YELLOW." §bМодератор §f- ".F::GREEN."485 руб".F::WHITE."§6/навсегда");
			$sender->sendMessage(F::RED. "-".F::YELLOW." §bСоздатель §f- ".F::GREEN."825 руб".F::WHITE."§6/навсегда");
			$sender->sendMessage(F::RED. "-".F::YELLOW." §bОснователь §f- ".F::GREEN."1655 руб".F::WHITE."§6/навсегда");
			$sender->sendMessage(F::RED. "-".F::YELLOW." §bБог §f- ".F::GREEN."2745 руб".F::WHITE."§6/навсегда");
			$sender->sendMessage(F::RED. "-".F::YELLOW." §bПокупка на сайте ".F::AQUA."§f-§a pay.fire-pe.ru");
			break;
		}
	}

	public function EntityDamageEvent(EntityDamageEvent $e)
	{
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
					
					# Не авторизирован
					if(!$this->sauth->getAPI()->isPlayerAuthenticated($d)){
						$e->setCancelled();
						return;
					}
					if(!$this->sauth->getAPI()->isPlayerAuthenticated($p)){
						$e->setCancelled();
						return;
					}
						
					# Защита от пвп на спауне
					if(($p->getPosition()->distance($v) <= $r)){
						$e->setCancelled();
						$d->sendTip(F::RED. "§bЗапрещено драться на спавне§c!");
						return;
					}
					
					# Защита от пвп в креативе
					if(($d->getGamemode() == 1) && ($p->getGamemode() == 0))
					{
						$e->setCancelled();
						$d->sendTip(F::RED."§bВы§f не можете драться в режиме Креатив§c!");
						return;
					}
					
					# Защита от пвп в флае!
					if($d->getAllowFlight(true)){
						$e->setCancelled();
						$d->sendTip(F::RED."§bВы не можете драться в режиме Полета§c!");
						return;
					}
					
					# Защита игрока с флаем
					if($p->getAllowFlight(true)){
						$e->setCancelled();
						$d->sendTip(F::RED."§bУ противника включен режим Полета§c!");
						return;
					}
				}
			}
		}
	}
	
	public function worldBorder(PlayerMoveEvent $e)
	{
		$p = $e->getPlayer();
		$pos = $p->getLevel()->getSpawnLocation();
		$vector = new Vector3($pos->getX(),$p->getPosition()->getY(),$pos->getZ());
		if(!$p->hasPermission("fapi.border"))
		{
			if(floor($p->distance($vector)) >= $this->distance)
			{
				$e->setCancelled();
				$p->sendTip(F::RED."    §bТут граница мира!\n§bЕсли застрял тут пиши".F::YELLOW." §a/spawn \n".F::GOLD." Игроки выше Випа могут\n пройти за границу мира!");
			}
		}
	}

	public function isItemBanned($item){
		return in_array($item, $this->banItems, true);
    }
	
	public function blockBreak(BlockBreakEvent $e)
	{
		$p = $e->getPlayer();
		$v = new Vector3($p->getLevel()->getSpawnLocation()->getX(),$p->getPosition()->getY(),$p->getLevel()->getSpawnLocation()->getZ());
		$r = $this->getServer()->getSpawnRadius();
		$block = $e->getBlock()->getId();
		$block2 = $e->getBlock()->getName();
		
		if(!$this->sauth->getAPI()->isPlayerAuthenticated($p)){
			$e->setCancelled();
			return;
		}
		
		if(($p->getPosition()->distance($v) <= $r) && (!$p->hasPermission("fapi.spawn"))){
			$e->setCancelled();
			$p->sendTip(F::RED. "§bЗапрещено ломать блоки на спавне§c!");
			return;
		}
		
		if($this->isItemBanned($block) && !$p->hasPermission("fapi.break")){
			$e->setCancelled();
			$p->sendTip($this->message("§bВы не можете сломать блок: ".F::GOLD.$block2.F::RED."!"));
			return;
		}
		
		if($this->guard == null) return;
		foreach($this->guard->areas->getAll() as $name => $info){
			if($this->guard->checkCoordinates($info, $e->getBlock()->getX(),$e->getBlock()->getY(),$e->getBlock()->getZ())) return;
		}
		$rand = mt_rand(1,20);
		if(($rand == 7) && !($p->getPosition()->distance($v) <= $r)){
			if($p->getGamemode() != 0) return; 
			$x = $e->getBlock()->getX();
			$y = $e->getBlock()->getY();
			$z = $e->getBlock()->getZ();
			$e->getPlayer()->getLevel()->dropItem(new Vector3($x,$y,$z), Item::get(175,0,1));
			$p->sendTip(F::YELLOW."§bВам§a выпал ".F::GOLD."один".F::YELLOW." коинс§c!");
			return;
		}
	}

	public function itemHeld(PlayerItemHeldEvent $e)
	{
		$hand = $e->getPlayer()->getInventory()->getItemInHand()->getId();
		
		if($hand == 175){
			$e->getPlayer()->sendPopup(F::YELLOW."§rКоинс");
			return;
		}
		
		if($hand == 280){
			$e->getPlayer()->sendPopup(F::YELLOW."§rПроверка региона");
			return;
		}
	}

	public function PlayerInteractEvent(PlayerInteractEvent $e)
	{
		$p = $e->getPlayer();
		$block = $e->getBlock()->getId();
		$inv = $p->getInventory()->getItemInHand()->getId();
		$inv2 = $p->getInventory()->getItemInHand()->getName();
		
		if(!$this->sauth->getAPI()->isPlayerAuthenticated($p)){
			$e->setCancelled();
			return;
		}
		
		if($this->isItemBanned($inv) && (!$p->hasPermission("fapi.place"))){
			$e->setCancelled();
			$p->sendTip(F::RED."§bЭтот предмет ".F::GOLD.$inv2.F::RED." - заблокирован§c!");
			return;
		}
		
		$x = $e->getBlock()->getX();
		$y = $e->getBlock()->getY();
		$z = $e->getBlock()->getZ();
		
		if($inv == 325){
			$e->setCancelled(true);
			$p->sendTip(F::RED."§bНельзя использовать§c!");
			return;
		}

		if($x == 122 && $y == 67 && $z == 123){
			if($this->heal[$p->getName()] == 1) return $p->sendTip(F::RED."Можно использовать раз в".F::YELLOW." Минуту§c!");
			$p->setHealth(20);
			$p->sendMessage(F::YELLOW."§7(§bFire§cCraft§7)§b Вы§a успешно§f пополнил свои ".F::GREEN."§eЖизни§c!");
			$this->heal[$p->getName()] = 1;
			return;
		}
		
		if($x == 122 && $y == 67 && $z == 115){
			if($this->hunger[$p->getName()] == 1) return $p->sendTip(F::RED."Можно использовать раз в".F::YELLOW." Минуту§c!");
			$p->setHealth(20);
			$p->sendMessage(F::YELLOW."§7(§bFire§cCraft§7)§b Вы§a успешно§f пополнил свой ".F::GREEN."§eГолод§c!");
			$this->hunger[$p->getName()] = 1;
			return;
		}

		if($inv == 378){
			if(!$p->hasPermission("fapi.click")){
				return;
			}
			$name = $e->getBlock()->getName();
			$damg = $e->getBlock()->getDamage();
			$id = $e->getBlock()->getId();
			$p->sendMessage(F::GREEN."$name ".F::GRAY."| ".F::RED."x: $x ".F::GRAY."| ".F::GOLD."y: $y ".F::GRAY."| ".F::YELLOW."z: $z".F::GRAY." | ".F::AQUA.$id.":".$damg);
		}
		
		if(($x == 132 && $y == 66 && $z == 114) && ($inv != 175)){
			$p->sendTip(F::RED."§aМожно нажать только с помощью ".F::YELLOW."§bКоинса§c!");
			$e->setCancelled();
			return;
		}
		
		if(($x != 132 && $y != 66 && $z != 114) && ($inv == 175)){
			$p->sendTip(F::WHITE."§bНапишите: ".F::GOLD."§a/coins".F::WHITE.", чтобы телепортироваться к обменнику§c!");
			$e->setCancelled();
			return;
		}
		
		if(($x == 132 && $y == 66 && $z == 114) && ($inv == 175)){
			$e->setCancelled();
			$this->SoundPop($p);
			$this->ParticleBlock($e->getPlayer(),$e);
			if(!$p->getInventory()->contains(\pocketmine\item\Item::get(175,0,1))){
				$p->sendTip(F::RED."  У вас нет ".F::YELLOW."Коинсов".F::RED.", чтобы\n".F::RED."обменять их на игровую ".F::GREEN."Валюту".F::RED."!");
				return;
			}
			$rand = mt_rand(5,20);
			$this->EconomyS->addMoney($p->getName(), $rand);
			$p->getInventory()->removeItem(Item::get(175,0,1));
			$p->sendTip(F::WHITE."§bВы обменяли".F::YELLOW." коинс".F::WHITE." и получили ".F::GOLD.$rand.F::WHITE." ₱§c!");
			return;
		}
		
		if($x == 132 && $y == 66 && $z == 124){
			$e->setCancelled();
			$exp = $p->getExpLevel();
			if($exp <= 10){
				$p->sendTip(F::RED."Вам нужно минимум".F::YELLOW." 10 единиц".F::RED." опыта§c!");
				return;
			}
			$rand = mt_rand(11,16);
			$this->EconomyS->addMoney($p->getName(), $rand*$exp);
			$p->sendTip(F::WHITE."§bВы обменяли ".F::YELLOW.$exp." единиц(ы) опыта".F::WHITE." и получили ".F::GOLD.$rand*$exp.F::WHITE." ₱§c!");
			$p->setExperience(0);
			$p->setExpLevel(0);
			$this->SoundPop($p);
			$this->ParticleBlock($e->getPlayer(),$e);
			return;
		}
	}

	public function onExplode(EntityExplodeEvent $e){
		$e->setCancelled();
	}

	public function onPlayerPreLogin(PlayerPreLoginEvent $e){
		$p = $e->getPlayer();
		
		if(!$this->players->exists(strtolower($e->getPlayer()->getName())) && (count($this->getServer()->getOnlinePlayers()) >= 97))
		{
			$e->setKickMessage(F::RED."HA CEPBEPE HET MECTA!\n".F::GREEN."Купите ".F::YELLOW."Вип".F::GREEN." на сайте\n".F::GOLD."»".F::AQUA." pay.fire-pe.ru ".F::GOLD."«");
			$e->setCancelled(true);
		}
	}
	
	public function timer(){
		foreach($this->getServer()->getOnlinePlayers() as $p)
		{
			$this->t[$p->getName()] = 0;
		}
	}
	
	public function heal(){
		foreach($this->getServer()->getOnlinePlayers() as $p)
		{
			$this->heal[$p->getName()] = 0;
		}
	}

	public function hunger(){
		foreach($this->getServer()->getOnlinePlayers() as $p)
		{
			$this->hunger[$p->getName()] = 0;
		}
	}

	public function mJoin(PlayerJoinEvent $e){
		$e->setJoinMessage(null);
		foreach($this->getServer()->getOnlinePlayers() as $p)
		{
			$p->sendTip(F::DARK_AQUA. $e->getPlayer()->getName(). F::GOLD. " зашёл на сервер§c!");
			if($e->getPlayer()->hasPermission("fapi.join"))
			{	
				$p->sendMessage(F::DARK_GRAY."§7(§bFire§cCraft§7)".F::GREEN."".F::DARK_GRAY."".F::GRAY." На сервер зашёл: ".F::GREEN.$e->getPlayer()->getName());
			}
		}
	}
	
	public function mQuit(PlayerQuitEvent $e)
	{
		$e->setQuitMessage(null);
	}

    public function onPlayerDeath(PlayerDeathEvent $e)
	{
		$e->setDeathMessage(null);
		$p = $e->getEntity();
		
		$name = strtolower($p->getName());
		if($p instanceof Player)
		{
			$c = $p->getLastDamageCause();
			if($c instanceof EntityDamageByEntityEvent)
			{
				$d = $c->getDamager();
				if($d instanceof Player)
				{
					$this->getServer()->broadcastTip(F::RED. $p->getName(). F::YELLOW. " §bбыл убит§6 игроком: ".F::GREEN.$d->getName());
					$rand = mt_rand(0,1);
					$p->getLevel()->dropItem(
						new Position(
							$p->getFloorX(),
							$p->getFloorY(),
							$p->getFloorZ()
						),
						Item::get(397,3,$rand)
					);
				}
			}
			else
			{
				$this->getServer()->broadcastTip(F::RED.$p->getName().F::YELLOW."§b умер");
			}
		}
		
		if($p->hasPermission("fapi.save"))
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
			$p->sendMessage(F::YELLOW. "§7(§bFire§cCraft§7)" .F::GOLD. "§aИспользуйте: " .F::YELLOW. "§b/back" .F::GOLD. "§f, чтобы вернуться на место смерти§c!");
		}
		else
		{
			$p->sendMessage(F::YELLOW. "§7(§bFire§cCraft§7)" .F::GOLD. " §bУ Вип§f игроков при смерти вещи§a сохраняются§c!");
			$p->sendMessage(F::YELLOW. "§7(§bFire§cCraft§7)" .F::GOLD. " §aКупить " .F::GREEN. "§bВип " .F::GOLD. "§fможно на сайте: " .F::YELLOW. "§6pay.fire-pe.ru");
		}
	}

	public function SoundPop(Player $p)
	{
		$sounds = "pocketmine\\level\\sound\\LaunchSound";
		$p->getLevel()->addSound(new $sounds($p));
	}

	public function ParticleBlock(Player $p,$e)
	{
		$level = $this->getServer()->getDefaultLevel();
		$partic = "pocketmine\\level\\particle\\FlameParticle";
		for($x=0;$x<=100;$x++)
		{
			$pos = new Vector3($e->getBlock()->getX()+mt_rand(0,1),$e->getBlock()->getY(),$e->getBlock()->getZ()+mt_rand(0,1));
			$particle = new $partic($pos);
			$level->addParticle($particle);
			$pos = new Vector3($e->getBlock()->getX()+mt_rand(0,1),$e->getBlock()->getY()+1,$e->getBlock()->getZ()+mt_rand(0,1));
			$particle = new $partic($pos);
			$level->addParticle($particle);
		}
	}

	public function PlayerRespawn(PlayerRespawnEvent $e)
	{
		$p = $e->getPlayer();
		$name = $p->getName();
		if($p->getPlayer()->hasPermission("fapi.save"))
		{
			if(isset($this->drops[$p->getName()]))
			{
				$p->getInventory()->setContents($this->drops[$p->getName()][0]);
				$p->getInventory()->setArmorContents($this->drops[$p->getName()][1]);
				unset($this->drops[$p->getName()]);
				$p->sendMessage(F::YELLOW. "§7(§bFire§cCraft§7)" .F::GOLD. "§b Вы§c погибли,§b ваш§f инвентарь был§a сохранен§c!");
			}
		}
	}
##########################################################################################################################################
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