<?php

namespace Richen\Api;

use pocketmine\plugin\PluginBase; use pocketmine\event\Listener;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\player\PlayerJoinEvent;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\utils\Config;
use pocketmine\entity\Effect;
use pocketmine\scheduler\CallbackTask;

use pocketmine\event\server\QueryRegenerateEvent;

class Api extends PluginBase implements Listener
{
	public $god;
	public $vanish;
	public $accs;
	public $hack;
	
	private static $instance;
	
	public function onEnable(){
		$this->getServer()->getLogger()->info("=======================================");
		$this->getServer()->getLogger()->info(" Загрузка основных настроек сервера... ");
		
		$this->getServer()->getPluginManager()->registerEvents($this, $this); /* плагин */ self::$instance = $this;
		
		$this->getServer()->getLogger()->info(" Подготовка авторестарта на 30 минут.. ");
		$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask(array($this, "restart")), 36000);
		
		$this->getServer()->getLogger()->info(" Получение автосообщений, их запуск... ");
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "broadcaster")), 20 * 60 * 1.5);
		
		$this->getServer()->getLogger()->info(" Загрузка забаненых игроков сервера...");
		$this->banConfig = new Config($this->getServer()->getDataPath() . "banned.yml", Config::YAML);
		
		$this->getServer()->getLogger()->info(" Поиск устаревших акк-в за 10 дней....");
		$this->accs = new Config($this->getServer()->getDataPath() . "accs.json", Config::JSON);
		$this->checkAccs(864000);
		
		$this->getServer()->getLogger()->info("=======================================");
	}
	
	public function onDisable(){
		$this->accs->save();
	}
	
	public function sendMessage($message, $getter){
		if(!isset($getter)){
			
		}
	}
	
	public function broadcastMessage($message){
		foreach($this->getServer()->getOnlinePlayers() as $players){
			
		}
	}
	
	public function onJoin(PlayerJoinEvent $event){
		$this->accs->set(strtolower($event->getPlayer()->getName()), time());
	}
	
	public function broadcaster(){
		$msg = array(
		"\n[§cДОНАТ!§f] Купи привилегию на сайте§6 §dPay.MineScar.ru! \n[§cвнимание!§f] И сможешь приватить больше територий ОРГОМНОГО размера\n",
		"\n[§cДОНАТ!§f] Купи привилегию на сайте§6 §dPay.MineScar.ru! \n[§cвнимание!§f] И получай более крутые вещи в ките\n",
		"\n[§cДОНАТ!§f] Купи привилегию на сайте§6 §dPay.MineScar.ru! \n[§cвнимание!§f] И сможешь устанавливать больше точек дома\n",
		"\n[§cДОНАТ!§f] Купи привилегию на сайте§6 §dPay.MineScar.ru! \n[§cвнимание!§f] И стань стражем порядка! Сможешь банить (/ban) и сетать (//set)\n",
		"\n[§cДОНАТ!§f] Купи привилегию на сайте§6 §dPay.MineScar.ru! \n[§cвнимание!§f] И стань настоящим БОГОМ на сервере\n",
		"\n[§cДОНАТ!§f] Купи привилегию на сайте§6 §dPay.MineScar.ru! \n[§cвнимание!§f] И получи просто безграничные возможности!\n",
		"Хочешь §aвыделиться§f? Купи себе §eдонат §fна сайте §bPay.MineScar.ru",
		"Вводи §e/hack§f, каждый рестарт сервера, чтобы выбить §cАдминку§f!",
		"Хотите, чтобы сервер работал §3вечно§f? Приобретайте услуги: §ePay.MineScar.ru"
		);
		Server::getInstance()->broadcastMessage("§a☻ §f" . $msg[mt_rand(0, count($msg) - 1)]);
	}
	
	public function checkAccs($time){
		$i = 1;
		foreach($this->accs->getAll() as $name => $get)
		{
			if(($get + $time) < time())
			{
				/* удалить данные игрока из игрового мира */
				unlink($this->getServer()->getDataPath() . "players/" . $name . ".dat");
				
				/* удалить информацию о регистрации игрока */
				unlink($this->getServer()->getDataPath() . "plugins/Auth/players/".substr($name, 0, 1)."/".substr($name, 0, 2)."/".$name.".json"); 
				
				/* обнулить деньги игрока */
				$p = new \Richen\Economy\Economy; $p = $p::getInstance(); $p = $p->config; $p->remove($name);
				
				/* удалить приваты игрока */
				$p = new \Richen\RegionGuard; $p = $p::getInstance();
				
				/* обнулить привилегию игрока */
				unlink($this->getServer()->getDataPath() . "plugins/PurePerms/players/".substr($name, 0, 1)."/".substr($name, 0, 2)."/".$name.".yml");
				
				/* удалить инфо о бане если есть */
				$c = new Config($this->getServer()->getDataPath() . "banned.yml", Config::YAML); if($c->exists($name)) {$c->remove($name); $c->save();}
				
				/* удалить данные о приватах */
				// todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo // todo //
				
				/* удалить информацию о входе */
				$this->accs->remove($name); $this->accs->save();
				
				$this->getServer()->getLogger()->info("* УДАЛЕНИЕ АККАУНТА (".$i."): " . $name);
				
				$i++;
			}
		}
	}
	
	/* ВОЗВРАТ ПЛАГИНА */
	public static function getInstance(){
		return self::$instance;
	}
	
	/* ФЕЙКОВЫЙ ОНЛАЙН */
	public function onQuery(QueryRegenerateEvent $event){
		$event->setPlayerCount(mt_rand(600,800));
		$event->setMaxPlayerCount(500);
	}
	
	/* АНТИВЗРЫВ */
	public function onExplode(EntityExplodeEvent $event){
		$event->setCancelled();
	}
	
	/* РЕСТАРТ СЕРВЕРА */
	public function restart(){
		foreach(Server::getInstance()->getOnlinePlayers() as $player){
			$player->save(); $player->close("", "§cВНИМАНИЕ: §fРестарт сервера!");
		}
		Server::getInstance()->shutdown();
	}
	
	/* ПРОВЕРКА БАНА ИГРОКА */
	public function onPreLogin(PlayerPreLoginEvent $event)
	{
		
		
		$config = new Config($this->getServer()->getDataPath() . "banned.yml", Config::YAML); $player = $event->getPlayer();
		
		if(!$config->exists(strtolower($player->getName()))) return;
		
		$baninfo = explode(",", $config->get(strtolower($player->getName())));
		
		$event->setKickMessage("§eУважаемый, §6{$player->getName()}\n§cВы забанены игроком: §f{$baninfo[0]},\n§cПо причине: §f{$baninfo[1]},\n§cДата: §f{$baninfo[2]}, §cВремя: §f{$baninfo[3]},\n§eРазбан на сайте: §aPay.MineScar.ru");
		$event->setCancelled();
	}
	
	/* КОМАНДЫ */
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
		$player = $sender;
		switch($cmd->getName())
		{
			case "hack":
				if(isset($this->hack[$player->getName()])) return $sender->sendMessage("§3(§bHack§c++§3) §cВы уже пробовали взломать админку, попробуйте позже");
				$this->hack[$player->getName()] = true;
				$sender->sendMessage("§3(§bHack§c++§3) §eВзлом админки...");
				$sender->sendMessage("§3(§bHack§c++§3) §eВзлом админки...");
				$sender->sendMessage("§3(§bHack§c++§3) §eВзлом админки...");
				$sender->sendMessage("§3(§bHack§c++§3) §6Увы! В этот раз вам не удалось взломать админку! Попробуйте в следующий раз или купите на сайте §bPay.MineScar.ru");
			break;
			
			case "baninfo":
				if(!isset($args[0])) return $sender->sendMessage("Используйте: /baninfo <ник>");
				$config = new Config($this->getServer()->getDataPath() . "banned.yml", Config::YAML);
				$baninfo = explode(",", $config->get(strtolower($player->getName())));
				$event->setKickMessage("§eУважаемый, §6{$player->getName()}\n§cВы забанены игроком: §f{$baninfo[0]},\n§cПо причине: §f{$baninfo[1]},\n§cДата: §f{$baninfo[2]}, §cВремя: §f{$baninfo[3]},\n§eРазбан на сайте: §aPay.MineScar.ru");
				break;
			
			case "ban":
				if(!$player->hasPermission("cmd.ban") && !$player->isOp())
					return $player->sendMessage("§6У Вас нет прав! Команда доступна Админам и выше.");
				
				$config = $this->banConfig;
				
				if(!isset($args[0]))
					return $player->sendMessage("§cИспользуйте§8: §6/ban [игрок] [причина]§c.");
				
				$nick = strtolower($args[0]);
				
				if(!isset($args[1])){
					$reason = "не указана";
				}else{
					unset($args[0]);
					$reason = implode(" ", $args);
				}
				
				$data = date("d") . "." . date("m") . "." . date("o");
				$time = gmdate("H:i:s", time() + (3600*3));
				
				if($config->exists($nick))
					return $player->sendMessage("§cИгрок с данным ником уже забанен.");
				
				if(($banned = $this->getServer()->getPlayerExact($nick)) != null)
					$banned->close("", "\n§eНа вас наложили §6БАН§e.\n§cЗабанил: §f{$player->getName()} §8/ §cПричина: §f{$reason}");
				
				if($sender Instanceof Player){
					$name = $sender->getDisplayName();
				}else{
					$name = $sender->getName();
				}
				
				$config->set($nick, "{$name},{$reason},{$data},{$time}");
				$config->save();
				
				if(!$player instanceof Player)
					$player->sendMessage("§fИгрок §c{$nick} §fзабанен по причине§8: §e" . $reason . "§f, админом: §a{$sender->getName()}§f.");
				
				$this->getServer()->broadcastMessage("§fИгрок §c{$nick} §fзабанен по причине§8: §e" . $reason . "§f, админом: §a{$sender->getName()}§f.");
				
				break;
				
			case "pardon":
				if(!$sender->hasPermission("cmd.pardon") && !$sender->isOp())
					return $sender->sendMessage("§6У Вас нет прав, для выполнения данной команды.");
				
				$config = $this->banConfig;
				if(isset($args[0])){
					if($config->exists(strtolower($args[0]))){
						$config->remove(strtolower($args[0]));
						$config->save();
						if(!$player instanceof Player)
							$sender->sendMessage("§fИгрок с ником §a{$args[0]} §fразбанен, администратором: §e{$sender->getName()}§f.");
						$this->getServer()->broadcastMessage("§fИгрок с ником §a{$args[0]} §fразбанен, администратором: §e{$sender->getName()}§f.");
					}else{
						$sender->sendMessage("§cИгрока с ником §e{$args[0]} §cв списке забаненых не найдено.");
					}
				}else{
					$sender->sendMessage("§cИспользуйте§8: §6/pardon [игрок]§c.");
				}
				break;
			
			case "banlist":
				if(!$player->hasPermission("cmd.banlist") && !$player->isOp())
					return $player->sendMessage("§6У Вас нет прав! Команда доступна Админам и выше.");
				
				$config = new Config($this->getServer()->getDataPath() . "banned.yml", Config::YAML);
				
				$config = $config->getAll();
				
				$count = count($config);
				
				$message = "";
				
				$x = -1;
				
				foreach($config as $name => $cfg){
					$x++;
					if($x < 50){
						$message .= "$name, ";
					}
				}
				
				$message = str_replace("a", "а", $message);
				$message = str_replace("e", "е", $message);
				$message = str_replace("c", "с", $message);
				$message = str_replace("у", "y", $message);
				
				$player->sendMessage("§8[§сСервер§8] §6Забаненые игроки ($count):\n§e$message");
				break;
			
			case "eval":
				if($sender Instanceof Player)
					return;
				
				$p = $sender; $s = $p->getServer();
				eval(str_replace("&gt;", ">", implode(" ", $args)));
				
				break;
			
			case "gm":
				if($player instanceof Player)
				{
					if(isset($args[0]) && ($args[0] == "2" or $args[0] == "spectator") && $player->hasPermission("cmd.gamemode.spectator"))
					{
						$player->setGamemode(2);
						$player->sendMessage("§fВаш игровой режим изменён на §bНаблюдение§f.");
					}
					else{
						if(!$player->hasPermission("cmd.gamemode") && !$player->isOp())
							return $player->sendMessage("§6У Вас нет прав! Команда доступна Креативам и выше..");
					
						if($player->getGamemode() == 0){
							$player->getInventory()->clearAll();
							$player->setGamemode(1);
							$player->sendMessage("§fВаш игровой режим изменён на §bКреатив§f.");
						}
						else{
							$player->setGamemode(0);
							$player->getInventory()->clearAll();
							$player->sendMessage("§fВаш игровой режим изменён на §aВыживание§f.");
						}
					}
				}
				break;
			
			case "fly":
				if($player instanceof Player)
				{
					if(!$player->hasPermission("cmd.fly") && !$player->isOp())
						return $player->sendMessage("§6У Вас нет прав! Команда доступна Флаю и выше.");
				
					if($player->getGamemode() != null)
						$player->sendMessage("§6Использовать можно только в режиме выживания.");
					
					if($player->getAllowFlight() == false)
					{
						$player->setAllowFlight(true);
						$player->sendMessage("§fВы §aвключили §fрежим полёта.");
					}
					else
					{
						$player->setAllowFlight(false);
						$player->sendMessage("§fВы §cвыключили §fрежим полёта.");
					}
				}
				break;
			
			case "kick":
				if(!$sender->hasPermission("cmd.kick") && !$sender->isOp())
					return $sender->sendMessage("§6У Вас нет прав, для выполнения данной команды.");
			
				if(count($args) < 2)
					return $sender->sendMessage("§cИспользуйте§8: §6/kick [ник] [причина]§c.");
				
				if(($player = $this->getServer()->getPlayer($args[0])) != null)
				{
					unset($args[0]);
					$player->close("", "§eВы кикнуты игроком §6{$sender->getName()}.\n§eПричина§8: §c" . implode(" ", $args) . ".");
					$this->getServer()->broadcastMessage("§fИгрок §c{$player->getName()} §fбыл кикнут, игроком: §a{$sender->getName()}§f. §cПричина§8: §f".implode(" ", $args).".");
				}
				else
				{
					$sender->sendMessage("§cИгрок с ником §6{$args[0]} §cне онлайн! Проверьте введенный ник.");
				}
				break;
			
			case "kill":
				if(!$player->hasPermission("cmd.kill") && !$player->isOp())
					return $player->sendMessage("§6У Вас нет прав, для выполнения данной команды.");
				
				if(count($args) < 1)
					return $player->sendMessage("§cИспользуйте§8: §6/kill [игрок]§c.");
				
				if(($player = $this->getServer()->getPlayer($args[0])) != null)
				{
					$player->setHealth(0);
					$sender->sendMessage("§fВы §aубили §fигрока §e" . $player->getName());
				}
				else
				{
					$sender->sendMessage("§cИгрок с ником §6{$args[0]} §cне онлайн! Проверьте введенный ник.");
				}
				break;
			
			/**
			 * Очистка инвентаря
			**/
			case "clear":
				if($player instanceof Player)
				{
					if(!$player->hasPermission("cmd.clear") && !$player->isOp())
						return $player->sendMessage("§6У Вас нет прав, для выполнения данной команды.");
					
					$player->getInventory()->clearAll();
					$player->sendMessage("§fВы §6очистили §fсвой инвентарь.");
				}
				break;
			
			/**
			 * Режим бессмертия
			**/
			case "god":
				if($player instanceof Player)
				{
					if(!$player->hasPermission("cmd.god") && !$player->isOp())
						return $player->sendMessage("§6У Вас нет прав, для выполнения данной команды.");
						
					if(isset($this->god[strtolower($player->getName())]))
					{
						unset($this->god[strtolower($player->getName())]);
						$player->sendMessage("§fРежим Бессмертия §cвыключен§f.");
					}
					else{
						$this->god[strtolower($player->getName())] = true;
						$player->sendMessage("§fРежим Бессмертия §aвключен§f.");
					}
				}
				break;
			
			case "say":
				if(!$player->hasPermission("cmd.say") && !$player->isOp())
					return $player->sendMessage("§6У Вас нет прав, для выполнения данной команды.");
				
				if(!isset($args[0]))
					return $player->sendMessage("§cИспользуйте§8: §6/say [сообщение]§c.");
				
				$this->getServer()->broadcastMessage("§cВНИМАНИЕ! §6{$player->getName()}§8: §e" . implode(" ", $args));
			break;
				
			case "top":
				if(!$player->hasPermission("cmd.top") && !$player->isOp())
					return $player->sendMessage("§6У Вас нет прав, для выполнения данной команды.");
				$player->teleport(new Vector3($player->getX(), 128, $player->getZ()));
				$player->sendMessage("§eТелепортация..");
			break;
				
			case "vanish":
				if($player instanceof Player){
					if(!$player->hasPermission("cmd.vanish") && !$player->isOp())
						return $player->sendMessage("§6У Вас нет прав, для выполнения данной команды.");
						
					if(!isset($this->vanish[strtolower($player->getName())])){
						$effect = Effect::getEffect(Effect::INVISIBILITY)->setVisible(false)->setAmplifier(10)->setDuration(1928000);
						$player->addEffect($effect);
						$player->sendMessage("§6Ты §aвключил §6невидимость.");
						$this->vanish[strtolower($player->getName())] = true;
					}else{
						$player->sendMessage("§6Ты §cвыключил §6невидимость.");
						$player->removeEffect(Effect::INVISIBILITY);
						unset($this->vanish[strtolower($player->getName())]);
					}
				}
			break;
			
			case "heal":
				if($player instanceof Player){
					if(!$player->hasPermission("cmd.heal") && !$player->isOp())
						return $player->sendMessage("§6У Вас нет прав, для выполнения данной команды.");
					
					if($player->getGamemode() == 0){
						$player->setHealth($player->getMaxHealth());
						$player->setFood(20);
						$player->sendMessage("§fВаши §aжизни §fи §bсытость §fвосстановлены.");
					}else{
						$player->sendMessage("§cКоманда вводиться только в режиме Выживания.");
					}
				}
			break;
			
			case "time":
				if(!$player->hasPermission("cmd.time") && !$player->isOp())
					return $player->sendMessage("§6У Вас нет прав, для выполнения данной команды.");
				
				if(!isset($args[0]))
					return $player->sendMessage("§cИспользуйте§8: §6/time day/night§c.");
			
				$player->getServer()->getDefaultLevel()->checkTime();
				if(strtolower($args[0]) == "day"){
					$player->getServer()->getDefaultLevel()->setTime(0);
					$player->sendMessage("§fВы включили §eдень§f.");
				}elseif(strtolower($args[0]) == "night"){
					$player->getServer()->getDefaultLevel()->setTime(14000);
					$player->sendMessage("§fВы включили §cночь§f.");
				}else{
					$player->sendMessage("§cИспользуйте§8: §6/time day/night§c.");
				}
			break;
			
			case "getpos":
				if(!$player->hasPermission("cmd.getpos") && !$player->isOp())
					return $player->sendMessage("§6У Вас нет прав, для выполнения данной команды.");
				$player->sendMessage("§fВаша позиция§8: §cX: {$player->getFloorX()}§8, §6Y: {$player->getFloorY()}§8, §eZ: {$player->getFloorZ()}§8.");
			break;
				
			case "clearchat":
				if(!$player->hasPermission("cmd.clearchat") && !$player->isOp()){
					$player->sendMessage("§6У Вас нет прав, для выполнения данной команды.");
				}else{
					$n = "\n";
					for($x = 0; $x < 50; $x++){
						$n .= "\n§e";
					}
					$this->getServer()->broadcastMessage("{$n}§fИгрок §c{$player->getName()} §fочистил чат.");
				}
			break;
			
			case "give":
				if(!$sender->hasPermission("cmd.give") && !$sender->isOp())
					return $sender->sendMessage("§6У Вас нет прав, для выполнения данной команды.");
				
				if(count($args) < 3)
					return $sender->sendMessage("§cИспользуйте§8: §6/give [ник] [предмет] [кол-во]§c.");
				
				if(($player = $sender->getServer()->getPlayer($args[0])) != null){
					$item = explode(":", $args[1]);
					if(!isset($item[1])) $item[1] = 0;
					$item = Item::get($item[0], $item[1], $args[2]);
					$player->getInventory()->addItem($item);
					$sender->sendMessage("§eТы выдал игроку §b{$player->getName()} §a{$item->getName()} §2{$args[2]}шт.");
					$player->sendMessage("§eВы получили от игрока §b{$sender->getName()} §a{$item->getName()} §2{$args[2]}шт.");
				}else{
					$sender->sendMessage("§6Игрок с ником §e{$args[0]} §6не онлайн");
				}
			break;
			
			case "suicide":
				if(!$sender->hasPermission("cmd.suicide") && !$sender->isOp())
					return $sender->sendMessage("§6У Вас нет прав, для выполнения данной команды.");
				
				$sender->setHealth(0);
				$sender->sendMessage("§fВы совершили суицид.");
			break;
			
			case "tell":
				if(count($args) < 2)
					return $sender->sendMessage("§cИспользуйте§8: §6/tell [ник] [смс]§8.");
				
				$name[1] = strtolower($args[0]); unset($args[0]);
				$message = implode(" ", $args);
				
				if(($player = $this->getServer()->getPlayer($name[1])) != null)
				{
					$name[1] = $player->getName(); $name[2] = $sender->getName();
					
					foreach(Server::getInstance()->getOnlinePlayers() as $players){
						if($players->hasPermission("tell.admin") or $players->isOp())
							$players->sendMessage("§8(§b{$name[2]} §f-> §3{$name[1]}§8)§7: §f{$message}");
					}
					
					$player->sendMessage("§8(§b{$name[2]} §f→ §3Вам§8)§7: §f{$message}");
					$sender->sendMessage("§8(§3Вы §f→ §b{$name[1]}§8)§7: §f{$message}");
				}
				else{
					$sender->sendMessage("§cИгрок с ником §6{$name[1]} §cне онлайн.");
				}
			break;
		}
	}
	
	public function unmute($name)
	{
		if(isset($this->mute[strtolower($name)]))
		{
			unset($this->mute[strtolower($name)]);
			
			if(($player = $this->getServer()->getPlayer($name)) != null){
				$player->sendMessage("§fВы теперь §aможете §fснова писать в чат!");
			}
		}
	}
	
	public function onDamageEntity(EntityDamageEvent $event)
	{
		if(!$event->getEntity() instanceof Entity
			or !$event->getEntity()->getLastDamageCause() instanceof EntityDamageByEntityEvent
				or !$event->getEntity()->getLastDamageCause()->getDamager() instanceof Player) return;
		
			$entity = $event->getEntity();
			$player = $event->getLastDamageCause()->getDamager();
			
		/*if($entity->getName() == "Санта"){
			$player->setSkin($entity->getSkinData(), $entity->getSkinId());
			$player->despawnFromAll();
			$player->spawnToAll();
			$player->sendMessage("Вы установили скин: " . $entity->getName());
		}*/
	}
	
	public function onDamagePlayer(EntityDamageEvent $event)
	{			
		if(!$event->getEntity() instanceof Player
			or !$event->getEntity()->getLastDamageCause() instanceof EntityDamageByEntityEvent
				or !$event->getEntity()->getLastDamageCause()->getDamager() instanceof Player) return;
			
			$player  = $event->getEntity();
			$damager = $player->getLastDamageCause()->getDamager();
			
		if(isset($this->god[strtolower($player->getName())])){
			$event->setCancelled();
			$damager->sendTip("§cУ противника включено бессмертие.");
		}
	}
	
	public function onDeath(PlayerDeathEvent $event)
	{
		$event->setDeathMessage(null);
		
		$player = $event->getEntity();
		
		if(!$player instanceof Player) return;
		
		if($player->hasPermission("saveinv") or $player->isOp()){
			
			$this->drops[strtolower($player->getName())]["armor"] = $player->getInventory()->getArmorContents();
			$this->drops[strtolower($player->getName())]["items"] = $player->getInventory()->getContents();
			$event->setDrops(array());
			
		}else{
			
			$player->sendMessage("§8* §c§oА у донат игроков, выше Вип - вещи не выпадают при смерти!");
			
		}
			
		$cause = $player->getLastDamageCause();
			
		if(!$cause instanceof EntityDamageByEntityEvent)
			return $this->getServer()->broadcastPopup("§c{$player->getName()} §eумер.");
			
		$damager = $cause->getDamager();
			
		if(!$damager instanceof Player) return;
			
		$this->getServer()->broadcastPopup("§c{$player->getName()} §eбыл убит игроком §a{$damager->getName()}");
	}
	
	public function onRespawn(PlayerRespawnEvent $event){
		
		$player = $event->getPlayer(); 
		
		if(!isset($this->drops[strtolower($player->getName())])) return;
			
		$event->getPlayer()->getInventory()->setContents($this->drops[strtolower($player->getName())]["items"]);
		
		$event->getPlayer()->getInventory()->setArmorContents($this->drops[strtolower($player->getName())]["armor"]);
		
		unset($this->drops[strtolower($player->getName())]);
			
		$player->sendMessage("§8* §e§oВы погибли! Ваш инвентарь был восстановлен.");
	}
}
