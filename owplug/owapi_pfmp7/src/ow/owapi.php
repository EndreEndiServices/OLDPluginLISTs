<?php

namespace ow;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Sign;
use pocketmine\tile\Chest;
use pocketmine\utils\TextFormat;
use pocketmine\utils\TextFormat as F;
use pocketmine\block\Block;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\level\particle\ItemBreakParticle;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\utils\Utils;


class owapi extends PluginBase implements Listener {
	public $drops = array();
	public $goto;
	public $timeToShotdown;
	
	public function onEnable() {
		$this->owp = $this->getServer()->getPluginManager()->getPlugin("owperms");
		$this->owm = $this->getServer()->getPluginManager()->getPlugin("owmoney");
		$this->ows = $this->getServer()->getPluginManager()->getPlugin("owshop");
		$this->owc = $this->getServer()->getPluginManager()->getPlugin("owchat");
		$this->owk = $this->getServer()->getPluginManager()->getPlugin("owkit");
		$this->owa = $this->getServer()->getPluginManager()->getPlugin("owadmin");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new apitask($this), 2600);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new reloadtask($this), 1 * 60 * 20);
		$this->distance = 400;
		$this->makeMOTD();
		$this->owtime = 60;
		$this->timeToShotdown = 120 * 60;
	}
	
	public function reloadTime() {
		$this->timeToShotdown -= 60;
		foreach ($this->getServer()->getOnlinePlayers() as $p) {
			$p->sendMessage(F::YELLOW. "[OW]". F::GOLD. " Сервер будет перезагружен через " .F::GREEN. $this->timeToShotdown / 60 . F::GOLD. " мин.");
		}
		$this->getServer()->getLogger()->info(F::YELLOW. "[OW]" .F::GOLD. " До перезагрузки сервера осталось " .F::GREEN. $this->timeToShotdown / 60 . F::GOLD. " мин.");
		
		if($this->timeToShotdown <= 1) {
			$this->getServer()->shutdown();
		}
	}
	
	public function updateMOTD() {
	    $online = count($this->getServer()->getOnlinePlayers());
        $razn = 3;// для Москвы зимой
        $time = gmdate("H:i", time() + ($razn*3600));

        $this->setStatus("&#128314; play.octopus-w.ru:19132 &#128312; " .$online. "/". $this->getServer()->getMaxPlayers() ." &#128312; " .$time. " msk &#128314;");	
	}
	
	public function setStatus($text) {
		$token = ""; //тут токен вписать нужно
        $curlObject = curl_init("https://api.vk.com/method/status.set?access_token=" .$token. "&group_id=73298513&text=" .rawurlencode($text));
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curlObject);
        @curl_close($curlObject);
	}
	
	public function joinMsg(PlayerJoinEvent $e) {
		$e->setJoinMessage(null);
		foreach ($this->getServer()->getOnlinePlayers() as $p) {
			$p->sendTip(F::DARK_AQUA. $e->getPlayer()->getName(). F::GOLD. " зашел на сервер");
		}
	}
	
	public function quitMsg(PlayerQuitEvent $e) {
		$e->setQuitMessage(null);
		foreach ($this->getServer()->getOnlinePlayers() as $p) {
			$p->sendTip(F::DARK_AQUA. $e->getPlayer()->getName(). F::GOLD. " вышел с сервера");
		}
	}
	
	public function BreakMoney(BlockBreakEvent $e) {
		$player = $e->getPlayer();
		$this->owm->addMoney($player->getName(), 3);
		$player->sendTip(F::YELLOW. "+1 монета");
	}
	
	public function broadcastMsg() {
		$rand = mt_rand(1, 5);
		switch($rand) {
			case 1:
			$msg = F::GOLD. " Любой донат продается в нашем магазине: " .F::AQUA. "pay.octopus-w.ru";
			break;
			case 2:
			$msg = F::GOLD. " Возможность летать? Дроп в двойном объеме? Огромный инвентарь? Это и многое другое только в привилегии vip!\n" .F::GREEN. "Более подробно в нашем магазине: " .F::AQUA. "pay.octopus-w.ru";
		    break;
			case 3:
			$msg = F::GOLD. " Хотите перейти в креатив? Получить доступ к неограниченному количеству блоков? Иметь в тройне огромный инвентарь, дроп в пятерном объеме и уйму других возможностей?\n" .F::GOLD. "Тогда премиум - именно то, что вам нужно!\n" .F::GREEN. "Более подробно в нашем магазине: " .F::AQUA. "pay.octopus-w.ru";
		    break;
			case 4:
			$msg = F::GOLD. " Всегда хотели попробовать себя в роли грозного управляющего? Следить за порядком, иметь огромные возможности, кикать и банить игроков?\n" .F::GOLD. "Тогда helper - именно то, что вам нужно!\n" .F::GREEN. "Более подробно в нашем магазине: " .F::AQUA. "pay.octopus-w.ru";
		    break;
			case 5:
			$msg = F::GOLD. " Мечтаешь стать чем-то большим, чем просто игрок? Купи донат, помоги нашему проекту в развитии и получи безграничные возможности по низкой цене!\n" .F::GREEN. "Покупка привилегий в нашем магазине: " .F::AQUA. "pay.octopus-w.ru";
		    break;
		}
		
		foreach ($this->getServer()->getOnlinePlayers() as $p) {
			$p->sendMessage(F::YELLOW. "[OW]". F::RESET. $msg);
		}
		$this->getServer()->getLogger()->info(F::YELLOW. "[OW]". F::RESET. $msg);
	}
	
    public function makeMOTD(){
        $string = F::BLUE. F::BOLD. $this->getNamePublic(). " " .F::WHITE. $this->getSubs(). F::DARK_GREEN. " subs";
		if($this->getServer()->getNetwork()->getName() != $string) {
            $this->getServer()->getNetwork()->setName($string);
            $this->getLogger()->info($string);
		}
    }

    public function getNamePublic() {
        $curlObject = curl_init("http://api.vk.com/method/groups.getById?group_ids=octopus_world&fields=status");
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
        $data = @curl_exec($curlObject);
        @curl_close($curlObject);
        if ($data) {
            $data = json_decode($data, true);
			return $data["response"][0]["name"];
		}
    }
	
	public function getSubs() {
        $curlObject = curl_init("http://api.vk.com/method/groups.getById?group_ids=octopus_world&fields=members_count");
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
        $data = @curl_exec($curlObject);
        @curl_close($curlObject);
        if ($data) {
            $data = json_decode($data, true);
			return $data["response"][0]["members_count"];
		}
	}
	
	public function setPrefixTag($player) {
		$group = $this->owp->getGroup($player->getName());
		if($group == "user") {
			$player->setNameTag(F::GRAY. "user " .F::DARK_AQUA. $player->getName());
		}
		if($group == "vip") {
			$player->setNameTag(F::BLUE. F::BOLD. "vip " .F::DARK_AQUA. $player->getName());
		}
		if($group == "premium") {
			$player->setNameTag(F::LIGHT_PURPLE. F::BOLD. "premium " .F::DARK_AQUA. $player->getName());
		}
		if($group == "helper") {
			$player->setNameTag(F::DARK_GREEN. F::BOLD. "helper " .F::DARK_AQUA. $player->getName());
		}
		if($group == "admin") {
			$player->setNameTag(F::RED. F::BOLD. "admin " .F::DARK_AQUA. $player->getName());
		}
		if($group == "youtube") {
			$player->setNameTag(F::BOLD. F::WHITE. "You" .F::RED. "Tube " .F::DARK_AQUA. $player->getName());
		}
	}
	
	public function why($entityName){
 		if(!is_file($this->getDataFolder()."players/".$entityName.".yml")){
			$this->createData($entityName);
		}
	}
	
	public function createData($entityName) {
        if(!is_file($this->getDataFolder()."players/".$entityName.".yml")){
            @mkdir($this->getDataFolder() . "players/");
		    $data = new Config($this->getDataFolder() . "players/".$entityName.".yml", Config::YAML);
		    $data->set("x", null);
			$data->set("y", null);
			$data->set("z", null);
		    $data->save();
		}
	}
	
	public function getHomeX($entityName) {
		$this->why($entityName);
        $sFile = (new Config($this->getDataFolder() . "players/".$entityName.".yml", Config::YAML))->getAll();
        return $sFile["x"];
	}
	
	public function getHomeY($entityName) {
		$this->why($entityName);
        $sFile = (new Config($this->getDataFolder() . "players/".$entityName.".yml", Config::YAML))->getAll();
        return $sFile["y"];
	}
	
	public function getHomeZ($entityName) {
		$this->why($entityName);
        $sFile = (new Config($this->getDataFolder() . "players/".$entityName.".yml", Config::YAML))->getAll();
        return $sFile["z"];
	}
	
	public function setHome($entityName, $x, $y, $z) {
		$this->why($entityName);
        $sFile = (new Config($this->getDataFolder() . "players/".$entityName.".yml", Config::YAML))->getAll();
        $sFile["x"] = (int) $x;
		$sFile["y"] = (int) $y;
		$sFile["z"] = (int) $z;
      	$fFile = new Config($this->getDataFolder() . "players/".$entityName.".yml", Config::YAML);
	    $fFile->setAll($sFile);
        $fFile->save();
	}
	
	public function JoinDonat(PlayerJoinEvent $e) {
		$entity = $e->getPlayer();
		$this->why($entity->getName());
		$this->gotoname[$entity->getName()] = null;
		$this->goto[$entity->getName()] = false;
		$this->setPrefixTag($entity);
		if($this->owp->getGroup($entity->getName()) == "vip") {
			$entity->getInventory()->setSize(70);
		} elseif($this->owp->getGroup($entity->getName()) == "premium") {
			$entity->getInventory()->setSize(120);
		}
	}
	
	public function WorldBorder(PlayerMoveEvent $e) {
		$entity = $e->getPlayer();
		$v = new Vector3($entity->getLevel()->getSpawnLocation()->getX(), $entity->getPosition()->getY(), $entity->getLevel()->getSpawnLocation()->getZ());
		if($this->owp->getGroup($entity->getName()) == "user") {
			if(floor($entity->distance($v)) >= $this->distance) {
				$e->setCancelled();
				$entity->sendTip(F::YELLOW. "[OWApi]" .F::GOLD. " Бесконечный мир? Привилегия не меньше " .F::GREEN. "vip" .F::GOLD. "!");
			}
		}
	}
	
    public function WhoDamager(EntityDamageEvent $e) {
        $entity = $e->getEntity();
		$entity = $e->getEntity();
		$level = $this->getServer()->getDefaultLevel();
        if ($entity instanceof Player) {
            if ($e instanceof EntityDamageByEntityEvent) {
                $damager = $e->getDamager()->getPlayer();
                $cause = $e->getEntity()->getPlayer()->getName();
                if ($e->getDamager() instanceof Player) {
					if($level->getTime() < 16500) {
						if(!($damager->isOp())) {
							$e->setCancelled();
							$damager->sendTip(F::RED. "запрещено драться днем!");
						}
					}
                    $v = new Vector3($entity->getLevel()->getSpawnLocation()->getX(),$entity->getPosition()->getY(),$entity->getLevel()->getSpawnLocation()->getZ());
                    $r = $this->getServer()->getSpawnRadius();
                    if(($entity instanceof Player) && ($entity->getPosition()->distance($v) <= $r)) {
						if(!($damager->isOp())) {
							$e->setCancelled();
							$damager->sendTip(F::RED. "запрещено драться на спавне!");
						}
					}
				}
			}
		}
	}
	
    public function onCommand(CommandSender $entity, Command $cmd, $label, array $args) {
		$level = $this->getServer()->getDefaultLevel();
		$x = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getX();
        $y = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getY();
        $z = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getZ();
		$group = $this->owp->getGroup($entity->getName());
        switch ($cmd->getName()) {

            case "spawn":
			if($entity Instanceof Player) {
	            $entity->teleport(new Vector3($x, $y, $z, $level));
				$entity->sendMessage(F::GOLD. "Уважаемый " .F::DARK_AQUA. $entity->getName(). F::GOLD. ", вы успешно телепортированы на спавн.");
			} else {
				$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Комманда вводится только от имени игрока.");
			}
			break;
			case "sethome":
			if($entity Instanceof Player) {
				$this->setHome($entity->getName(), $entity->getX(), $entity->getY(), $entity->getZ());
				$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Точка дома успешно установлена.");
			} else {
				$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Комманда вводится только от имени игрока.");
			}
			break;
			case "home":
			if($entity Instanceof Player) {
				if($this->getHomeX($entity->getName()) != null && $this->getHomeY($entity->getName()) != null && $this->getHomeZ($entity->getName()) != null) {
					$entity->teleport(new Vector3($this->getHomeX($entity->getName()), $this->getHomeY($entity->getName()), $this->getHomeZ($entity->getName()), $level));
					$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Дом, милый дом.");
				} else {
					$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Вы еще не ставили точку дома.");
				}
			} else {
				$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Комманда вводится только от имени игрока.");
			}
			break;
			case "tpall":
			if($entity Instanceof Player) {
				if($this->owp->getGroup($entity->getName()) == "helper" || $this->owp->getGroup($entity->getName()) == "admin") {
					foreach ($this->getServer()->getOnlinePlayers() as $p) {
						$p->teleport(new Vector3($entity->getX(), $entity->getY(), $entity->getZ()));
						$this->getServer()->getLogger()->info(F::YELLOW. "[OWApi]" .F::GREEN. $entity->getName(). F::GOLD. " телепортировал всех в одну точку.");
					}
				} else {
					$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Нужно иметь привилегию администратора/хелпера");
				}
			} else {
				$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Комманда вводится только от имени игрока.");
			}
			break;
			case "clear":
			if($entity Instanceof Player) {
				$entity->getInventory()->clearAll();
				$entity->sendMessage(F::GOLD. "Уважаемый " .F::DARK_AQUA. $entity->getName(). F::GOLD. ", ваш инвентарь полностью очищен.");
			} else {
				$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Комманда вводится только от имени игрока.");
			}
			break;
			case "gm":
			if($entity Instanceof Player) {
				if($this->owp->getGroup($entity->getName()) != "user" && $this->owp->getGroup($entity->getName()) != "vip") {
					if(isset($args[0])) {
						if(is_numeric($args[0])) {
							$entity->setGamemode($args[0]);
							$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Вы успешно сменили игровой режим.");
						} else {
							$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Режим должен быть указан в цифровом формате.");
						}
					} else {
						$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Вы должны указать режим.");
					}
				} else {
					$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Нужно иметь привилегию не ниже premium.");
				}
			} else {
				$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Комманда вводится только от имени игрока.");
			}
			break;
			case "fly":
			if($entity Instanceof Player) {
				if($this->owp->getGroup($entity->getName()) != "user") {
					if(isset($args[0])) {
						if($args[0] == "on") {
							$entity->setAllowFlight(true);
							$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Fly успешно включен.");
						}
						if($args[0] == "off") {
							$entity->setAllowFlight(false);
							$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Fly успешно отключен.");
						}
					} else {
						$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " /fly on|off.");
					}
				} else {
					$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Ваша привилегия должна быть выше простого игрока.");
				}
			}
			break;
			case "setprefix":
			if($entity Instanceof Player) {
				if($this->owp->getGroup($entity->getName()) != "user") {
					if(isset($args[0])) {
						$this->owc->setPrefix($entity->getName(), $args[0]);
						$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Вы успешно установили префикс " .F::GREEN. $args[0]) .F::GOLD. ".";
						
					} else {
						$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Укажите префикс.");
					}
				} else {
					$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Ваша привилегия должна быть выше простого игрока.");
				}
			}
			break;
			case "kit":
			if($entity Instanceof Player) {
				$this->owk->giveKit($entity);
			}
			break;
			case "sleep":
			if($group != "user") {
				$entity->sleepOn(new Vector3($entity->getX(), $entity->getY(), $entity->getZ()));
				$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Вы успешно легли спать!");
			} else {
				$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Группа не меньше вип!");
			}
			break;
			case "money":
			$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " У вас на счету: " .F::GREEN .$this->owm->getMoney($entity->getName()));
			break;
			case "goto":
			if(isset($args[0])) {
				if($this->getServer()->getPlayer($args[0]) Instanceof Player) {
					$this->gotoname[$entity->getName()] = $args[0];
					$this->goto[$entity->getName()] = true;
				} else {
					$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Этого игрока нет на сервере");
				}
			} else {
				$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Укажите никнейм игрока.");
			}
			break;
			case "shop":
			if($entity Instanceof Player) {
				if(isset($args[0])) {
					if(isset($args[1])) {
						$this->ows->shopping($entity, $args[0], $args[1]);
					} else {
						$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Укажите количество.");
					}
				} else {
					$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Укажите ID предмета.");
				}
			}
			break;
			case "ap":
			if($entity->isOp() && $entity Instanceof Player) {
				if(isset($args[0])) {
					if(isset($args[1])) {
						$this->ows->createPrice($args[0], $args[1]);
						$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Вы добавили предмету с ID" .F::DARK_GREEN. $args[0]. F::GOLD. " цену " .F::DARK_GREEN. $args[1]);
					} else {
						$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Укажите цену предмета.");
					}
				} else {
					$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Укажите ID предмета.");
				}
			}
		}
	}
	
	public function moveGoto(PlayerMoveEvent $e) {
		$entity = $e->getPlayer();
		if($this->goto[$entity->getName()]) {
			if(isset($this->gotoname[$entity->getName()])) {
				$entity->sendTip(F::YELLOW. "[OWApi]" .F::GOLD. " Дистанция до игрока " .F::DARK_GREEN. $this->gotoname[$entity->getName()]. F::GOLD. ": " .F::GREEN. floor($entity->distance(new Vector3($this->getServer()->getPlayer($this->gotoname[$entity->getName()])->getX(), $this->getServer()->getPlayer($this->gotoname[$entity->getName()])->getY(), $this->getServer()->getPlayer($this->gotoname[$entity->getName()])->getZ()))));
			}
			if(floor($entity->distance(new Vector3($this->getServer()->getPlayer($this->gotoname[$entity->getName()])->getX(), $this->getServer()->getPlayer($this->gotoname[$entity->getName()])->getY(), $this->getServer()->getPlayer($this->gotoname[$entity->getName()])->getZ()))) <= 10) {
				$this->goto[$entity->getName()] = false;
				$this->gotoname[$entity->getName()] = null;
			}
		}
		if($this->goto[$entity->getName()]) {
			if(!($this->getServer()->getPlayer($this->gotoname[$entity->getName()]) Instanceof Player)) {
				$this->goto[$entity->getName()] = false;
				$this->gotoname[$entity->getName()] = null;
			}
		}
	}
	
	public function cmd(PlayerCommandPreprocessEvent $e) {
		$entity = $e->getPlayer();
		$msg = $e->getMessage();
		$group = $this->owp->getGroup($entity->getName());
		if($msg{0} == "/" && $msg{1} == "h" && $msg{2} == "e" && $msg{3} == "l" && $msg{4} == "p") {
			$e->setCancelled();
			if($group == "user") {
				$entity->sendMessage(F::YELLOW. "-------------------");
				$entity->sendMessage(F::YELLOW. "- /spawn" .F::DARK_GREEN. " - телепортация на спавн");
				$entity->sendMessage(F::YELLOW. "- /money" .F::DARK_GREEN. " - проверить деньги");
				$entity->sendMessage(F::YELLOW. "- /sethome" .F::DARK_GREEN. " - установить точку дома");
				$entity->sendMessage(F::YELLOW. "- /home" .F::DARK_GREEN. " - телепортироваться домой");
				$entity->sendMessage(F::YELLOW. "- /kit" .F::DARK_GREEN. " - получить начальный кит");
				$entity->sendMessage(F::YELLOW. "- /owguard" .F::DARK_GREEN. " - приват");
				$entity->sendMessage(F::YELLOW. "- /goto" .F::DARK_GREEN. " - найти игрока");
				$entity->sendMessage(F::YELLOW. "- /shop" .F::DARK_GREEN. " - купить предмет");
				$entity->sendMessage(F::YELLOW. "-------------------");
			} elseif($group == "vip" || $group == "premium" || $group == "youtube") {
				$entity->sendMessage(F::YELLOW. "-------------------");
				$entity->sendMessage(F::YELLOW. "- /spawn" .F::DARK_GREEN. " - телепортация на спавн");
				$entity->sendMessage(F::YELLOW. "- /money" .F::DARK_GREEN. " - проверить деньги");
				$entity->sendMessage(F::YELLOW. "- /sethome" .F::DARK_GREEN. " - установить точку дома");
				$entity->sendMessage(F::YELLOW. "- /home" .F::DARK_GREEN. " - телепортироваться домой");
				$entity->sendMessage(F::YELLOW. "- /setprefix" .F::DARK_GREEN. " - установить префикс");
				$entity->sendMessage(F::YELLOW. "- /fly" .F::DARK_GREEN. " - включить\отключить полет");
				$entity->sendMessage(F::YELLOW. "- /gm" .F::DARK_GREEN. " - смена игрового режима");
				$entity->sendMessage(F::YELLOW. "- /sleep" .F::DARK_GREEN. " - лечь спать");
				$entity->sendMessage(F::YELLOW. "- /kit" .F::DARK_GREEN. " - получить начальный кит");
				$entity->sendMessage(F::YELLOW. "- /owguard" .F::DARK_GREEN. " - приват");
				$entity->sendMessage(F::YELLOW. "- /goto" .F::DARK_GREEN. " - найти игрока");
				$entity->sendMessage(F::YELLOW. "- /shop" .F::DARK_GREEN. " - купить предмет");
				$entity->sendMessage(F::YELLOW. "-------------------");
			} elseif($group == "helper" || $group == "admin") {
				$entity->sendMessage(F::YELLOW. "-------------------");
				$entity->sendMessage(F::YELLOW. "- /spawn" .F::DARK_GREEN. " - телепортация на спавн");
				$entity->sendMessage(F::YELLOW. "- /money" .F::DARK_GREEN. " - проверить деньги");
				$entity->sendMessage(F::YELLOW. "- /sethome" .F::DARK_GREEN. " - установить точку дома");
				$entity->sendMessage(F::YELLOW. "- /home" .F::DARK_GREEN. " - телепортироваться домой");
				$entity->sendMessage(F::YELLOW. "- /setprefix" .F::DARK_GREEN. " - установить префикс");
				$entity->sendMessage(F::YELLOW. "- /fly" .F::DARK_GREEN. " - включить\отключить полет");
				$entity->sendMessage(F::YELLOW. "- /gm" .F::DARK_GREEN. " - смена игрового режима");
				$entity->sendMessage(F::YELLOW. "- /sleep" .F::DARK_GREEN. " - лечь спать");
				$entity->sendMessage(F::YELLOW. "- /kit" .F::DARK_GREEN. " - получить начальный кит");
				$entity->sendMessage(F::YELLOW. "- /owguard" .F::DARK_GREEN. " - приват");
				$entity->sendMessage(F::YELLOW. "- /owban" .F::DARK_GREEN. " - забанить игрока");
				$entity->sendMessage(F::YELLOW. "- /owkick" .F::DARK_GREEN. " - кикнуть игрока");
				$entity->sendMessage(F::YELLOW. "- /tpall" .F::DARK_GREEN. " - телепортировать всех к себе");
				$entity->sendMessage(F::YELLOW. "- /goto" .F::DARK_GREEN. " - найти игрока");
				$entity->sendMessage(F::YELLOW. "- /shop" .F::DARK_GREEN. " - купить предмет");
				$entity->sendMessage(F::YELLOW. "-------------------");
			}
		}
		if($msg{0} == "/" && $msg{1} == "m" && $msg{2} == "e") {
			$e->setCancelled();
			$entity->sendMessage(F::YELLOW. "Тут такое не пройдет :)");
		}
	}
	
    public function PlayerDeath(PlayerDeathEvent $e){
        $entity = $e->getEntity();
		if($this->owp->getGroup($entity->getName() != "user")) {
			$this->drops[$entity->getName()][1] = $entity->getInventory()->getArmorContents();
			$this->drops[$entity->getName()][0] = $entity->getInventory()->getContents();
			$e->setDrops(array());
		}
    }
	
	public function PlayerRespawn(PlayerRespawnEvent $e){
        $entity = $e->getPlayer();
		if($this->owp->getGroup($entity->getName()) != "user") {
			if (isset($this->drops[$entity->getName()])) {
				$entity->getInventory()->setContents($this->drops[$entity->getName()][0]);
				$entity->getInventory()->setArmorContents($this->drops[$entity->getName()][1]);
				unset($this->drops[$entity->getName()]);
				$entity->sendMessage(F::YELLOW. "[OWApi]" .F::GOLD. " Вы погибли. Ваш инвентарь был сохранен.");
			}
		}
    }
	
}