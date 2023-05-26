<?php

namespace ow;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\event\block\SignChangeEvent;
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
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\HeartParticle;
use pocketmine\level\particle\LavaParticle;
use pocketmine\level\particle\PortalParticle;
use pocketmine\level\sound\ClickSound;
use pocketmine\level\sound\AnvilUseSound;
use pocketmine\level\sound\BatSound;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat;
use pocketmine\utils\TextFormat as F;
use pocketmine\level\particle\AngryVillagerParticle;
use pocketmine\level\particle\WaterDripParticle;
use pocketmine\entity\Effect;
use pocketmine\block\Block;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\level\particle\ExplodeParticle;
use pocketmine\utils\Config;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\level\particle\ItemBreakParticle;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerToggleSprintEvent;
use pocketmine\event\player\PlayerPreLoginEvent;

class auth extends PluginBase implements Listener {
	private $authSession;
	private $mysqli;
	private $passw;
	
	public function onEnable() {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->mysqli = new \mysqli("", "", "", "");
	}
	
	public function checkAcc($username) {
		$result = $this->mysqli->query("SELECT * FROM `acc` WHERE `nickname` = '".$username."'");
        $user = mysqli_fetch_assoc($result);
        if($user) {
	        return true;
        } else {
	        return false;
        }
	}
	
	public function getPassword($username) {
        $result = $this->mysqli->query("SELECT * FROM `acc` WHERE `nickname` = '".$username."'");
		if($this->checkAcc($username)) {
			$data = $result->fetch_assoc();
			$result->free();
			if(isset($data["password"])){
				return $data["password"];
			}
		}
	}
	
	public function loginAcc($username, $password, $player) {
		if($this->checkAcc($username)) {
			if(isset($this->passw[$username])) {
				if($password === $this->passw[$username]) {
					$this->closeSession($player);
					$player->sendMessage(F::YELLOW. "[OWAuth]". F::GOLD. " Вы успешно авторизовались.");
					$this->getServer()->getLogger()->info(F::YELLOW. "[OWAuth]" .F::GOLD. " Игрок " .F::GREEN. $username . F::GOLD. " успешно прошел авторизацию, введя пароль: " .F::GREEN. $password);
				} else {
					$player->sendMessage(F::YELLOW. "[OWAuth]". F::GOLD. " Неправильный пароль.");
					$this->getServer()->getLogger()->info(F::YELLOW. "[OWAuth]" .F::GOLD. " Игрок " .F::GREEN. $username . F::GOLD. " провалил авторизацию, используя пароль " .F::GREEN. $password);
				}
			} else {
				$this->passw[$username] = $this->getPassword($username);
				$this->loginAcc($username, $password, $player);
			}
		}
	}
	
	public function registerAcc($username, $password, $player) {
        if(!$this->checkAcc($username)) {
            $this->mysqli->query("INSERT INTO `acc` (`id`, `nickname`, `password`, `vk`) VALUES (NULL, '".$username."', '".$password."', '')");
			$player->sendMessage(F::YELLOW. "[OWAuth]" .F::GOLD. " Вы успешно зарегистрировались на сервере. Удачной игры! :з");
			$this->getServer()->getLogger()->info(F::YELLOW. "[OWAuth]" .F::GOLD. " Игрок " .F::GREEN. $username . F::GOLD. " успешно прошел регистрацию, используя пароль " .F::GREEN. $password);
		} else {
		    $player->sendMessage(F::YELLOW. "[OWAuth]" .F::GOLD. " Ваш аккаунт уже есть в базе.");
			$this->getServer()->getLogger()->info(F::YELLOW. "[OWAuth]" .F::GOLD. " Игрок " .F::GREEN. $username . F::GOLD. " провалил регистрацию. Аккаунт уже есть в базе.");
		}
	}
	
	public function OnPlayerJoin(PlayerJoinEvent $event) {
		$player = $event->getPlayer();
        $this->openSession($player);
	}
	
	public function OnPlayerQuit(PlayerQuitEvent $e) {
		$player = $e->getPlayer();
		$this->closeSession($player);
	}
	
	public function onPlayerPreLogin(PlayerPreLoginEvent $event){
		$player = $event->getPlayer();
		foreach($this->getServer()->getOnlinePlayers() as $p){
			if($p !== $player and strtolower($player->getName()) === strtolower($p->getName())){
				$event->setCancelled(true);
				$player->kick("already logged in");
			}
		}
	}
	
	public function closeSession($player) {
		$this->authSession[$player->getName()] = false;
		$player->removeAllEffects();
	}
	
	public function openSession($player) {
		$this->authSession[$player->getName()] = true;
		$this->setEffects($player, (int) 15);
		$this->setEffects($player, (int) 14);
		if($this->checkAcc($player->getName())) {
			$this->passw[$player->getName()] = $this->getPassword($player->getName());
		}
	}
	
	public function setEffects($player, $id) {
        $amplifier = 0;
        $visibility = false;
        $duration = 50000000000000000;
        $effect = Effect::getEffect($id);
        $effect->setVisible($visibility);
        $effect->setDuration($duration);
        $effect->setAmplifier($amplifier);
        $player->addEffect($effect);
	}
	
	public function JoinSession(PlayerJoinEvent $e) {
		$player = $e->getPlayer();
		if($this->authSession[$player->getName()]) {
		    if(!($this->checkAcc($player->getName()))) {
				$player->sendMessage(F::YELLOW. "Уважаемый " .F::DARK_AQUA. $player->getName() .F::YELLOW. ", пожалуйста, зарегистрируйся, введя любой пароль в чат.");
			} else {
				$player->sendMessage(F::YELLOW. "Уважаемый " .F::DARK_AQUA. $player->getName() .F::YELLOW. ", пожалуйста, авторизуйся, введя свой пароль в чат.");
			}
		}
	}
	
	public function maincmd(PlayerCommandPreprocessEvent $e) {
		$player = $e->getPlayer();
		$msg = $e->getMessage();
		if($this->authSession[$player->getName()]) {
			$e->setCancelled(true);
			if($msg{0} === "/") {
				$player->sendMessage(F::YELLOW. "[OWAuth]" .F::GOLD. " Запрещено использовать комманды.");
			} else {
                $pattern = '#[^\s\da-z]#is';
                if(!preg_match($pattern, $msg)) {
					$msg = explode(" ", $msg);
				    if(!($this->checkAcc($player->getName()))) {
					    $this->RegisterAcc($player->getName(), $msg[0], $player);
				    } else {
					    $this->loginAcc($player->getName(), $msg[0], $player);
				    }
			    } else {
				    $player->sendMessage(F::YELLOW. "[OWAuth]" .F::GOLD. " Вы ввели недопустимые символы.");
			    }
			}
		}
	}
	
	public function Move(PlayerMoveEvent $e) {
		$player = $e->getPlayer();
		if($this->authSession[$player->getName()]) {
			$e->setCancelled(true);
			$player->onGround = true;
			if(!($this->checkAcc($player->getName()))) {
				$player->sendTip(F::YELLOW. "уважаемый " .F::DARK_AQUA. $player->getName() .F::YELLOW. ", ты должен зарегистрироваться");
				$player->sendPopup(F::YELLOW. "введи любой пароль в чат");
			} else {
				$player->sendTip(F::YELLOW. "уважаемый " .F::DARK_AQUA. $player->getName() .F::YELLOW. ", ты должен авторизоваться.");
				$player->sendPopup(F::YELLOW. "введи свой пароль в чат");
			}
		}
	}
	
    public function Damage(EntityDamageEvent $e) {
        $entity = $e->getEntity();
        if ($entity instanceof Player) {
            if ($e instanceof EntityDamageByEntityEvent) {
                $damager = $e->getDamager()->getPlayer();
                $cause = $e->getEntity()->getPlayer()->getName();
                if ($e->getDamager() instanceof Player && $entity instanceof Player) {
					if($this->authSession[$entity->getName()]) {
						$e->setCancelled(true);
						if(!($this->checkAcc($entity->getName()))) {
							$entity->sendMessage(F::YELLOW. "уважаемый " .F::DARK_AQUA. $entity->getName() .F::YELLOW. ", ты должен зарегистрироваться");
							$entity->sendMessage(F::YELLOW. "введи любой пароль в чат");
						} else {
							$entity->sendMessage(F::YELLOW. "уважаемый " .F::DARK_AQUA. $entity->getName() .F::YELLOW. ", ты должен авторизоваться.");
							$entity->sendMessage(F::YELLOW. "введи свой пароль в чат");
						}
					}
				}
			}
		}
	}
	
	public function Interact(PlayerInteractEvent $e) {
		$player = $e->getPlayer();
		if($this->authSession[$player->getName()]) {
			$e->setCancelled(true);
			if(!($this->checkAcc($player->getName()))) {
				$player->sendMessage(F::YELLOW. "уважаемый " .F::DARK_AQUA. $player->getName() .F::YELLOW. ", ты должен зарегистрироваться");
				$player->sendMessage(F::YELLOW. "введи любой пароль в чат");
			} else {
				$player->sendMessage(F::YELLOW. "уважаемый " .F::DARK_AQUA. $player->getName() .F::YELLOW. ", ты должен авторизоваться.");
				$player->sendMessage(F::YELLOW. "введи свой пароль в чат");
			}
		}
	}
	
	public function BreakBlock(BlockBreakEvent $e) {
		$player = $e->getPlayer();
		if($this->authSession[$player->getName()]) {
			$e->setCancelled(true);
			if(!($this->checkAcc($player->getName()))) {
				$player->sendMessage(F::YELLOW. "уважаемый " .F::DARK_AQUA. $player->getName() .F::YELLOW. ", ты должен зарегистрироваться");
				$player->sendMessage(F::YELLOW. "введи любой пароль в чат");
			} else {
				$player->sendMessage(F::YELLOW. "уважаемый " .F::DARK_AQUA. $player->getName() .F::YELLOW. ", ты должен авторизоваться.");
				$player->sendMessage(F::YELLOW. "введи свой пароль в чат");
			}
		}
	}
	
	public function Sprint(PlayerToggleSprintEvent $e) {
		$player = $e->getPlayer();
		if($this->authSession[$player->getName()]) {
			$e->setCancelled(true);
			if(!($this->checkAcc($player->getName()))) {
				$player->sendMessage(F::YELLOW. "уважаемый " .F::DARK_AQUA. $player->getName() .F::YELLOW. ", ты должен зарегистрироваться");
				$player->sendMessage(F::YELLOW. "введи любой пароль в чат");
			} else {
				$player->sendMessage(F::YELLOW. "уважаемый " .F::DARK_AQUA. $player->getName() .F::YELLOW. ", ты должен авторизоваться.");
				$player->sendMessage(F::YELLOW. "введи свой пароль в чат");
			}
		}
	}
	
	
}