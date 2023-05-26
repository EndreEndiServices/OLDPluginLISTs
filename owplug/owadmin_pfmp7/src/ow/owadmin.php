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

class owadmin extends PluginBase implements Listener {
	public $token;
	
	public function onEnable() {
		$this->owp = $this->getServer()->getPluginManager()->getPlugin("owperms");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->mysqli = new \mysqli("", "", "", "");
	}
	
	public function onPlayerPreLogin(PlayerPreLoginEvent $event){
		$player = $event->getPlayer();
		if($this->getBanned($player->getName())) {
			$event->setCancelled(true);
			$player->close("", F::GOLD. "Вы забанены на сервере.");
		}
	}
	
	public function kick($player, $kicker, $reason) {
		if($kicker Instanceof Player) {
			if($this->getServer()->getPlayer($player) Instanceof Player) {
				foreach ($this->getServer()->getOnlinePlayers() as $p) {
					$p->sendMessage(F::YELLOW. "[OWAdmin] " .F::GREEN. $kicker->getName(). F::GOLD. " кикнул игрока " .F::GREEN. $player. F::GOLD. " за " .F::DARK_GREEN. $reason);
				}
				$this->getLogger()->info(F::YELLOW. "[OWAdmin] " .F::GREEN. $kicker->getName(). F::GOLD. " кикнул игрока " .F::GREEN. $player. F::GOLD. " за " .F::DARK_GREEN. $reason);
				$this->getServer()->getPlayer($player)->kick(F::GOLD. $reason);
			} else {
				$kicker->sendMessage(F::YELLOW. "[OWAdmin]" .F::GOLD. " Игрока нет на сервере.");
			}
		} else {
			if($this->getServer()->getPlayer($player) Instanceof Player) {
				foreach ($this->getServer()->getOnlinePlayers() as $p) {
					$p->sendMessage(F::YELLOW. "[OWAdmin]" .F::GREEN. " CONSOLE". F::GOLD. " кикнул игрока " .F::GREEN. $player. F::GOLD. " за " .F::DARK_GREEN. $reason);
				}
				$this->getLogger()->info(F::YELLOW. "[OWAdmin]" .F::GREEN. " CONSOLE". F::GOLD. " кикнул игрока " .F::GREEN. $player. F::GOLD. " за " .F::DARK_GREEN. $reason);
				$this->getServer()->getPlayer($player)->kick(F::GOLD. $reason);
			} else {
				$kicker->sendMessage(F::YELLOW. "[OWAdmin]" .F::GOLD. " Игрока нет на сервере.");
			}
		}
	}
	
	public function addban($username, $reason, $banner) {
		$username = strtolower($username);
		if($this->getAcc($username)) {
			if(!($this->getBanned($username))) {
				$this->mysqli->query('SET CHARACTER SET utf8');
				$this->mysqli->query("INSERT INTO `ban` (`id`, `nickname`, `reason`) VALUES (NULL, '".$username."', '".$reason."')");
				$this->banvk($username, $reason);
				if($this->getServer()->getPlayer($username) Instanceof Player) {
					$this->getServer()->getPlayer($username)->close("", F::GOLD. "Вы забанены на сервере.");
				}
				$banner->sendMessage(F::YELLOW. "[OWAdmin]" .F::GOLD. " Игрок " .F::GREEN. $username .F::GOLD. " успешно забанен за " .F::GREEN. $reason. F::GOLD. ".");
				$this->sendUsers(F::YELLOW. "[OWAdmin] " .F::GREEN. $banner->getName() .F::GOLD. " Забанил " .F::GREEN. $username. F::GOLD. " на сервере за " .F::GREEN. $reason. F::GOLD. ".");
			} else {
				$banner->sendMessage(F::YELLOW. "[OWAdmin]" .F::GOLD. " Игрок уже забанен.");
			}
		} else {
			$banner->sendMessage(F::YELLOW. "[OWAdmin]" .F::GOLD. " Такого игрока нет в базе.");
		}
	}
	
	public function sendUsers($text) {
		foreach ($this->getServer()->getOnlinePlayers() as $p) {
			$p->sendMessage($text);
		}
	}
	
	public function removeBan($username, $banner) {
		$username = strtolower($username);
		if($this->getBanned($username)) {
			$this->mysqli->query("DELETE FROM `ban` WHERE `nickname` = '".$username."'");
			$this->unbanVk($username);
			$banner->sendMessage(F::YELLOW. "[OWAdmin]" .F::GOLD. " Игрок " .F::GREEN. $username. F::GOLD. " успешно разбанен на сервере.");
		} else {
			$banner->sendMessage(F::YELLOW. "[OWAdmin]" .F::GOLD. " Указанного игрока нет в бан листе.");
		}
	}
	
	public function getBanned($username) {
	    $username = strtolower($username);
		$result = $this->mysqli->query("SELECT * FROM `ban` WHERE `nickname` = '".$username."'");
        $user = mysqli_fetch_assoc($result);
        if($user) {
	        return true;
        } else {
	        return false;
        }
	}
	
	public function getAcc($username) {
	    $username = strtolower($username);
		$result = $this->mysqli->query("SELECT * FROM `acc` WHERE `nickname` = '".$username."'");
        $user = mysqli_fetch_assoc($result);
        if($user) {
	        return true;
        } else {
	        return false;
        }
	}
	
	public function banvk($username, $reason) {
		$username = strtolower($username);
        $result = $this->mysqli->query("SELECT * FROM `acc` WHERE `nickname` = '".$username."'");
		$data = $result->fetch_assoc();
		$result->free();
		$vk = $data["vk"];
	    $token = "9cbda38e2110324e7f94c1b7720be6b9f5588492a0fbc839276faaaa47d1c478f99d49fdc1ab811e11c54";
        $curlObject = curl_init("https://api.vk.com/method/groups.banUser?access_token=" .$token. "&user_id=" .$vk. "&group_id=73298513&comment=" .rawurlencode($reason));
       	curl_setopt($curlObject, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
        @curl_exec($curlObject);
        @curl_close($curlObject);
	}
	
	public function unbanVk($username) {
		$username = strtolower($username);
        $result = $this->mysqli->query("SELECT * FROM `acc` WHERE `nickname` = '".$username."'");
		$data = $result->fetch_assoc();
		$result->free();
		$vk = $data["vk"];
		$token = "9cbda38e2110324e7f94c1b7720be6b9f5588492a0fbc839276faaaa47d1c478f99d49fdc1ab811e11c54";
        $curlObject = curl_init("https://api.vk.com/method/groups.unbanUser?access_token=" .$token. "&user_id=" .$vk. "&group_id=73298513");
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
        @curl_exec($curlObject);
        @curl_close($curlObject);
	}
	
    public function onCommand(CommandSender $entity, Command $cmd, $label, array $args) {
		$group = $this->owp->getGroup($entity->getName());
        switch ($cmd->getName()) {
			case "owkick":
			if($entity Instanceof Player) {
				if(isset($args[0])) {
					if(isset($args[1])) {
						if($group == "helper" || $group == "admin") {
							$this->kick($args[0], $entity, $args[1]);
						}
					} else {
						$entity->sendMessage(F::YELLOW. "[OWApi]". F::GOLD. " Укажите причину кика.");
					}
				} else {
					$entity->sendMessage(F::YELLOW. "[OWApi]". F::GOLD. " Укажите никнейм игрока.");
				}
			} else {
				if(isset($args[0])) {
					if(isset($args[1])) {
						$this->owa->kick($args[0], $entity, $args[1]);
					} else {
						$entity->sendMessage(F::YELLOW. "[OWApi]". F::GOLD. " Укажите причину кика.");
					}
				} else {
					$entity->sendMessage(F::YELLOW. "[OWApi]". F::GOLD. " Укажите никнейм игрока.");
				}
			}
			break;
			case "owban":
			if($entity Instanceof Player) {
				if(isset($args[0])) {
					if(isset($args[1])) {
						if($group == "helper" || $group == "admin") {
							$this->addban($args[0], $args[1], $entity);
						}
					} else {
						$entity->sendMessage(F::YELLOW. "[OWApi]". F::GOLD. " Укажите причину бана.");
					}
				} else {
					$entity->sendMessage(F::YELLOW. "[OWApi]". F::GOLD. " Укажите никнейм игрока.");
				}
			} else {
				if(isset($args[0])) {
					if(isset($args[1])) {
						$this->addban($args[0], $args[1], $entity);
					} else {
						$entity->sendMessage(F::YELLOW. "[OWApi]". F::GOLD. " Укажите причину бана.");
					}
				} else {
					$entity->sendMessage(F::YELLOW. "[OWApi]". F::GOLD. " Укажите никнейм игрока.");
				}
			}
			break;
			case "owpardon":
			if($entity Instanceof Player) {
				if(isset($args[0])) {
					if($group == "helper" || $group == "admin") {
					    $this->removeBan($args[0], $entity);
					}
				} else {
					$entity->sendMessage(F::YELLOW. "[OWApi]". F::GOLD. " Укажите никнейм игрока.");
				}
			} else {
				if(isset($args[0])) {
					$this->removeBan($args[0], $entity);
				} else {
					$entity->sendMessage(F::YELLOW. "[OWApi]". F::GOLD. " Укажите никнейм игрока.");
				}
			}
			break;
		}
	}
	
}