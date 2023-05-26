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
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat;
use pocketmine\utils\TextFormat as F;
use pocketmine\block\Block;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\level\particle\ItemBreakParticle;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerPreLoginEvent;

class owchat extends PluginBase implements Listener {
	public $oldmsg;
	public $timer;
	public $ts;
	public $mysqli;
	public $playerPrefix;
	
	public function onEnable() {
		$this->owp = $this->getServer()->getPluginManager()->getPlugin("owperms");
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new task($this), 15);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->mysqli = new \mysqli("", "", "", "");
	}
	
	public function massives(PlayerPreLoginEvent $e) {
		$player = $e->getPlayer();
		$this->oldmsg[$player->getName()] = null;
		$this->timer[$player->getName()] = 0;
		$this->ts[$player->getName()] = false;
	}
	
	public function onJoin(PlayerJoinEvent $e) {
		$player = $e->getPlayer();
		if($this->owp->getGroup($player->getName()) != "user") {
		    $this->updatePrefix($player->getName());
		}
	}
	
	public function checkAcc($username) {
		$result = $this->mysqli->query("SELECT * FROM `chat` WHERE `nickname` = '".$username."'");
        $user = $result->fetch_assoc();
		$result->free();
        if($user) {
	        return true;
        } else {
	        return false;
        }
	}
	
	public function updatePrefix($username) {
	    if($this->checkAcc($username)) {
	        $this->playerPrefix[$username] = $this->checkPrefix($username);
		} else {
		    $this->playerPrefix[$username] = null;
		    $this->createData($username);
		}
	}
	
	public function createData($username) {
        if(!$this->checkAcc($username)) {
            $this->mysqli->query("INSERT INTO `chat` (`id`, `nickname`, `prefix`) VALUES (NULL, '".$username."', '')");
			$this->updatePrefix($username);
		}
	}
	
	public function checkPrefix($username) {
        $result = $this->mysqli->query("SELECT * FROM `chat` WHERE `nickname` = '".$username."'");
		if($this->checkAcc($username)) {
			$data = $result->fetch_assoc();
			$result->free();
			if(isset($data["prefix"])){
			    return $data["prefix"];
			}
		} else {
		    $this->createData($username);
		}
	}
	
	public function getPrefix($username) {
	    if(isset($this->playerPrefix[$username])) {
	        return $this->playerPrefix[$username];
		} else {
		    $this->updatePrefix($username);
		}
	}
	
	public function setPrefix($username, $prefix) {
        if($this->checkAcc($username)) {
            $this->mysqli->query("UPDATE `chat` SET `prefix` = '".$prefix."' WHERE `nickname` = '".$username."'");
			$this->updatePrefix($username);
		}
	}
	
	public function prefixManager($player) {
		$pref = $this->getPrefix($player->getName());
		if($pref != null) {
			return F::DARK_GREEN. $this->getPrefix($player->getName()). " ";
		} else {
			return "";
		}
	}
	
	public function groupManager($player) {
		$group = $this->owp->getGroup($player->getName());
		switch($group) {
			case "user":
			return F::GRAY. "игрок";
			break;
			case "vip":
			return F::BLUE. "вип";
			break;
			case "premium":
			return F::LIGHT_PURPLE. "премиум";
			break;
			case "helper":
			return F::DARK_GREEN. "хелпер";
			break;
			case "admin":
			return F::RED. "админ";
			break;
			case "youtube":
			return F::WHITE. "You" .F::RED. "Tube";
		}
	}
	
	public function format($player, $message) {
		$group = $this->owp->getGroup($player->getName());
		$prefix = $this->prefixManager($player);
		$gp = $this->groupManager($player);
		switch($group) {
			case "user":
			return F::GRAY. "[" .$gp. F::GRAY. "] " .F::DARK_AQUA. $player->getName(). F::GRAY. " ✒ " .F::GRAY. $message;
			break;
			case "vip":
			return $prefix. F::GRAY. "[" .$gp. F::GRAY. "] " .F::DARK_AQUA. $player->getName(). F::GRAY. " ✒ " .F::GOLD. $message;
			break;
			case "premium":
			return $prefix. F::GRAY. "[" .$gp. F::GRAY. "] " .F::DARK_AQUA. $player->getName(). F::GRAY. " ✒ " .F::GOLD. $message;
			break;
			case "helper":
			return $prefix. F::GRAY. "[" .$gp. F::GRAY. "] " .F::DARK_AQUA. $player->getName(). F::GRAY. " ✒ " .F::GOLD. $message;
			break;
			case "admin":
			return $prefix. F::GRAY. "[" .$gp. F::GRAY. "] " .F::DARK_AQUA. $player->getName(). F::GRAY. " ✒ " .F::GOLD. $message;
			break;
			case "youtube":
			return $prefix. F::GRAY. "[" .$gp. F::GRAY. "] " .F::DARK_AQUA. $player->getName(). F::GRAY. " ✒ " .F::GOLD. $message;
			break;
		}
	}
	
	public function chat(PlayerChatEvent $e) {
		$player = $e->getPlayer();
		$message = $e->getMessage();
		if($this->owp->getGroup($player->getName()) == "user") {
			if($this->oldmsg[$player->getName()] != $message) {
				if(!($this->ts[$player->getName()])) {
					$e->setFormat($this->format($player, $message));
					$this->oldmsg[$player->getName()] = $message;
					$this->timer[$player->getName()] = 1;
					$this->ts[$player->getName()] = true;
				} else {
					$e->setCancelled();
					$player->sendMessage(F::YELLOW. "[OWChat]" .F::GOLD. " Подождите " .F::GREEN. $this->timerManager(). F::GOLD. " сек. прежде, чем сможете писать в чат.");
				}
			} else {
				$e->setCancelled();
				$player->sendMessage(F::YELLOW. "[OWChat]" .F::GOLD. " Нельзя дублировать сообщения.");
			}
		} else {
			$e->setFormat($this->format($player, $message));
		}
	}
	
	public function timerManager() {
		foreach($this->getServer()->getOnlinePlayers() as $p){
			$player = $p->getPlayer()->getName();
			$timer = $this->timer[$player];
			switch($timer) {
				case 11:
				return 1;
				break;
				case 10:
				return 1;
				break;
				case 9:
				return 2;
				break;
				case 8:
				return 3;
				break;
				case 7:
				return 4;
				break;
				case 6:
				return 5;
				break;
				case 5:
				return 6;
				break;
				case 4:
				return 7;
				break;
				case 3:
				return 8;
				break;
				case 2:
				return 8;
				break;
				case 1:
				return 9;
				break;
				case 0:
				return 9;
				break;
			}
		}
	}
	
	public function timer() {
		foreach($this->getServer()->getOnlinePlayers() as $p){
			$player = $p->getPlayer()->getName();
			if($this->ts[$player]) {
				if($this->timer[$player] != 0) {
					$this->timer[$player]++;
					switch($this->timer[$player]) {
						case 1:
						break;
						case 2:
						break;
						case 3:
						break;
						case 4:
						break;
						case 5:
						break;
						case 6:
						break;
						case 7:
						break;
						case 8:
						break;
						case 9:
						break;
						case 10:
						break;
						case 11:
						$this->timer[$player] = 0;
						$this->ts[$player] = false;
						break;
					}
				}
			}
		}
	}
	
}