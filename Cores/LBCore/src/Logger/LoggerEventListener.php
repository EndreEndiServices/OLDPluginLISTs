<?php

namespace Logger;

use Logger\Logger;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\entity\Effect;

/**
 * Listen events important to logger
 */
class LoggerEventListener implements Listener {
	/** @var Logger */
	private $logger;
	/** @var int */
	private $logoutNumber = 0;
	/**@var date*/
	private $logoutTime;
	/**@var array*/
	private $logoutReason = array();
	/**@var array*/
	private $livingDead = array();
	/**@var array*/
	private $playersLoginTime = array();
	
	public function __construct() {
		$this->logger = Logger::getInstance();
		$this->logoutTime = time();
	}
	
	public function onPlayerLogin(PlayerLoginEvent $event) {
		$player = $event->getPlayer();
		$this->playersLoginTime[$player->getId()] = date('Y-m-d H:i:s');
		
		$msg = 'LOG IN: '.$player->getName().' was joined';
		$this->logger->write($msg);
	}
	
	public function onPlayerDeath(PlayerDeathEvent $event) {
		$player = $event->getEntity();

		if (!$player instanceof Player) {
			return false;
		}

		// if was killed by player or another creature
		$damageCause = $player->getLastDamageCause();
		if($damageCause instanceof EntityDamageByEntityEvent) {
			$attacker = $damageCause->getDamager();

			if ($attacker instanceof Player) {
				$attacker = $attacker->getName();
			} else {
				$attacker = get_class($attacker);
			}

			$msg = 'DEATH: '.$player->getName().' was killed by '.$attacker;
		} else {
			// Death by obstacle
			$msg = 'DEATH: '.$event->getDeathMessage();
		}

		$this->logger->write($msg);
	}
	
	public function onPlayerQuit(PlayerQuitEvent $event) {
		$player = $event->getPlayer();
		$msg = 'LOG OUT: '.$player->getName().' was disconnected';
		
		if (isset($this->playersLoginTime[$player->getId()])) {
			$loginDate = date_create($this->playersLoginTime[$player->getId()]);
			$logoutDate = date_create(date('Y-m-d H:i:s'));
			$sessionLength = date_diff($loginDate, $logoutDate);
			
			$msg .= " [ SESSION LENGTH: {$sessionLength->format('%dD %hH %iM %sS')} ]";
			$msg .= " [ REASON: {$event->getQuitReason()} ]";
			
			unset($this->playersLoginTime[$player->getId()]);
			
			if (time() - $this->logoutTime <= 1) {
				$this->logoutNumber++;
			} else {
			    if ($this->logoutNumber > 5) {
				$mainReason = "Unknown";
				$reasonsCount = array_count_values($this->logoutReason);
				if ($reasonsCount) {
					$mainReason = array_search(max($reasonsCount), $reasonsCount);
				}
				$playersCount = count(Server::getInstance()->getOnlinePlayers());
				$disconnectMessage = "{$this->logoutNumber} disconnects in a row [ Main reason: {$mainReason} | Players online: {$playersCount} ] ";
				$this->logger->write($disconnectMessage, false, Logger::WARNING);
			    }
			    $this->logoutNumber = 0;
			    $this->logoutReason = array();
			}
			$this->logoutTime = time();
		} else {
			$reason = $event->getQuitReason();
			if (!$reason || $reason == "") {
				$reason = "Can't join the game";
			}
			$msg .= " [ REASON: {$reason} ]";
		}
		$this->logoutReason[] = $event->getQuitReason();
		$this->logger->write($msg);
		$log = $event->getQuitLog();
		if(!empty($log)){
			$this->logger->write($log);
		}
		$event->setQuitMessage("");
	}
	
	public function onPlayerKick(PlayerKickEvent $event) {
		$player = $event->getPlayer();
		$reason = $event->getReason();
		$msg = 'KICK: [ PLAYER: '.$player->getName().' | REASON: '.$reason.' ]';
		$this->logger->write($msg);
		$this->logger->addKickedPlayer($player);
	}
	
	public function onPlayerMove(PlayerMoveEvent $event) {
		$player = $event->getPlayer();
		
		// living dead
		if ($player->dead) {
			if(!isset($this->livingDead[$player->getId()])){
				$this->livingDead[$player->getId()] = 1;
			} else{
				$this->livingDead[$player->getId()]++;
			}
			if($this->livingDead[$player->getId()] > 1){
				$msg = $player->getName()." - living dead";
				$this->logger->write($msg, true);
			}
		} elseif(isset($this->livingDead[$player->getId()])){
			unset($this->livingDead[$player->getId()]);
		}
	}
	
	public function onEntityDamage(EntityDamageEvent $event) {
		if ($this->logger->checkGameType('sp')) {
			return;
		}
		
		if ($event instanceof EntityDamageByEntityEvent) {
			$damager = $event->getDamager();
			if ($damager instanceof Player && $damager->hasEffect(Effect::INVISIBILITY)) {
				$damagerName = $damager->getName();
				$message = "Player '{$damagerName}' invisible and kills people.";
				$this->logger->write($message, false, Logger::WARNING);
			}
		}
	}
}
