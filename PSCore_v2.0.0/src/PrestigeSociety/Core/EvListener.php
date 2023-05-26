<?php

namespace PrestigeSociety\Core;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\network\mcpe\protocol\ItemFrameDropItemPacket;
use pocketmine\Player;
use PrestigeSociety\Core\Task\WelcomePlayerTask;
use PrestigeSociety\Core\Utils\RandomUtils;

class EvListener implements Listener {

	/** @var Entity[] */
	protected $entities = [];
	protected $spawnedInfoParticles = false;
	/** @var PrestigeSocietyCore */
	private $core;
	/** @var Player[] */
	private $confirmation = [];

	/**
	 *
	 * EvListener constructor.
	 *
	 * @param PrestigeSocietyCore $c
	 *
	 */
	public function __construct(PrestigeSocietyCore $c){
		$this->core = $c;
	}

	/**
	 *
	 * @param QueryRegenerateEvent $event
	 *
	 */
	public function query(QueryRegenerateEvent $event){
		$event->setPlugins([$this->core]);
		//$event->setPlayerCount(mt_rand(10, 50) + count($this->core->getServer()->getOnlinePlayers()));
	}

	/**
	 *
	 * @param PlayerJoinEvent $e
	 *
	 */
	public function onJoin(PlayerJoinEvent $e){
		$p = $e->getPlayer();
		$p->teleport($this->core->PrestigeSocietyTeleport->getSpawn());

		$p->setDisplayName($this->core->PrestigeSocietyChat()->formatDisplayName($p));
		$p->setNameTag($this->core->PrestigeSocietyChat()->formatDisplayName($p));
		$this->core->HUD->addPlayer($p);

		$this->core->setIsInLobby($p, true);

		$this->welcome($p);

		$motion = $p->getDirectionVector();
		$motion->multiply(2);
		$motion->y = 1.1;
		$p->setMotion($motion);
		$p->fallDistance = 0;

		if(!$this->core->PrestigeSocietyRanks->isPlayerRegistered($p)){
			$this->core->PrestigeSocietyRanks->registerPlayer($p);
		}

		if(!$this->core->PrestigeSocietyEconomy->playerExists($p)){
			$this->core->PrestigeSocietyEconomy->addNewPlayer($p);
		}

		if(!$this->core->PrestigeSocietyCredits->playerExists($p)){
			$this->core->PrestigeSocietyCredits->addNewPlayer($p);
		}


		$e->setJoinMessage('');
	}

	/**
	 *
	 * @param Player $p
	 *
	 */
	public function welcome(Player $p){
		if($p->getFirstPlayed() === null){
			$title = $this->core->getMessage('welcome', 'title');
			$title = str_replace('@player', $p->getName(), $title);

			$sub = $this->core->getMessage('welcome', 'subtitle');
			$sub = str_replace('@player', $p->getName(), $sub);

			$broad = $this->core->getMessage('welcome', 'broadcast');
			$broad = str_replace('@player', $p->getName(), $broad);

			$this->core->getScheduler()->scheduleDelayedTask(new WelcomePlayerTask($this->core, $title, $sub, $broad, $p), 20);
		}else{
			$ro = '&7Bun venit!';
			$en = '&7Welcome';
			$sub = 'unknown';
			$lang = $this->core->PrestigeSocietyLang->getLang($p);

			$title = $this->core->getMessage('welcome', 'title_back');
			$title = str_replace('@player', $p->getName(), $title);

			switch($lang){
				case 0:
					$sub = $en;
					break;
				case 1:
					$sub = $ro;
					break;
			}
			$sub = str_replace('@player', $p->getName(), $sub);

			$broad = $this->core->getMessage('welcome', 'broadcast_back');
			$broad = str_replace('@player', $p->getName(), $broad);

			$this->core->getScheduler()->scheduleDelayedTask(new WelcomePlayerTask($this->core, $title, $sub, $broad, $p), 20);
		}
	}

	/**
	 *
	 * @param PlayerQuitEvent $event
	 *
	 */
	public function quit(PlayerQuitEvent $event){
		if($this->core->HUD->inPlayers($event->getPlayer())){
			$this->core->HUD->removePlayer($event->getPlayer());
		}
		if($this->core->FunBox->isLSDEnabled($event->getPlayer())){
			$this->core->FunBox->toggleLSD($event->getPlayer());
		}
		$event->setQuitMessage('');
	}


	/*
	public function onKill(EntityDamageEvent $e){
			$trg = $e->getEntity();
			$cause = $e->getEntity()->getLastDamageCause();

			if($cause instanceof EntityDamageByEntityEvent){
					$killer = $cause->getDamager();

					if($killer instanceof Player and $trg instanceof Player and $trg->getHealth() <= 0){
							$this->core->getPrestigeSocietyEconomy()->addMoney($killer, $this->core->getConfig()->get('kill_money_amount'));
							$killer->sendMessage(RandomUtils::colorMessage(
								str_replace('@player', $trg->getName(), $this->core->getMessage('kill_money', 'pay_message'))));

					}elseif
					($killer instanceof Projectile){
							$killer = $killer->shootingEntity;

							if($trg instanceof Player and $killer instanceof Player and $trg->getHealth() <= 0){
									$this->core->getPrestigeSocietyEconomy()->addMoney($killer, $this->core->getConfig()->get('kill_money_amount'));
									$killer->sendMessage(RandomUtils::colorMessage(
										str_replace('@player', $trg->getName(), $this->core->getMessage('kill_money', 'pay_message'))));
							}
					}
			}
	}*/

	/**
	 *
	 * @param EntityLevelChangeEvent $event
	 *
	 */
	public function changeLevel(EntityLevelChangeEvent $event){
		$p = $event->getEntity();
		if($p instanceof Player){
			if($event->getTarget()->getId() !== $this->core->PrestigeSocietyTeleport->getSpawn()->getLevel()->getId()){
				$this->core->setIsInLobby($p, false);
			}else{
				$this->core->setIsInLobby($p, true);
			}
		}
	}

	/**
	 *
	 * @param DataPacketReceiveEvent $e
	 *
	 * @throws \InvalidStateException
	 *
	 */
	public function dataPacket(DataPacketReceiveEvent $e){
		$pk = $e->getPacket();

		if($pk instanceof ItemFrameDropItemPacket){
			if(!$e->getPlayer()->hasPermission('pl.framedrop')){
				$e->setCancelled();
			}
		}
	}

	/**
	 *
	 * @param PlayerKickEvent $e
	 *
	 * @throws \InvalidStateException
	 *
	 */
	public function kick(PlayerKickEvent $e){
		if($e->getReason() === 'disconnectionScreen.serverFull'){
			if($e->getPlayer()->hasPermission('pl.vip.join')){
				$e->setCancelled();
			}
		}
	}

	/**
	 *
	 * @param PlayerMoveEvent $e
	 *
	 * @throws \InvalidStateException
	 *
	 */
	public function onMove(PlayerMoveEvent $e){
		$p = $e->getPlayer();

		if($p->y <= 0 and isset($this->core->getConfig()->get('void_checker')[$p->level->getName()])){
			$p->teleport($this->core->getServer()->getDefaultLevel()->getSpawnLocation());
			$e->setCancelled();
		}

		if(!$p->hasPermission('pl.vip.zone') and
			($p->getLevel()->getBlock($p->subtract(0, 1))->getId() == (int)$this->core->getConfig()->get('vip_zone_block_id'))){
			$e->setCancelled();
		}
		if(!$p->hasPermission('pl.vip.iron') and
			($p->getLevel()->getBlock($p->subtract(0, 1))->getId() == (int)$this->core->getConfig()->get('vip_iron_block_id'))){
			$e->setCancelled();
		}
		if(!$p->hasPermission('pl.vip.gold') and
			($p->getLevel()->getBlock($p->subtract(0, 1))->getId() == (int)$this->core->getConfig()->get('vip_gold_block_id'))){
			$e->setCancelled();
		}
		if(!$p->hasPermission('pl.vip.diamond') and
			($p->getLevel()->getBlock($p->subtract(0, 1))->getId() == (int)$this->core->getConfig()->get('vip_diamond_block_id'))){
			$e->setCancelled();
		}
	}

	/**
	 *
	 * @param PlayerChatEvent $e
	 *
	 * @throws \InvalidStateException
	 *
	 */
	public function onChat(PlayerChatEvent $e){
		$p = $e->getPlayer();
		$msg = $e->getMessage();

		if($this->core->PrestigeSocietyChat->filterSpam($e->getPlayer(), intval($this->core->getConfig()->getAll()["anti_spam"]["time"])) and !$p->hasPermission("spam.bypass")){
			$this->core->getLogger()->notice("[" . date("r") . "]" . $p->getName() . " tried to spam, but don't worry, I cancelled his message.");
			$p->sendMessage(RandomUtils::colorMessage($this->core->getMessage("chat_protector", "no_spam")));
			$e->setCancelled();
		}

		if($this->core->PrestigeSocietyChat->filterBadWords($msg)){
			$this->core->getLogger()->notice("[" . date("r") . "]" . $p->getName() . " tried to swear, but don't worry, I cancelled his message.");
			$p->sendMessage(RandomUtils::colorMessage($this->core->getMessage("chat_protector", "no_swear")));
			$e->setCancelled();
		}

		$format = $this->core->PrestigeSocietyChat->formatMessage($p, $msg);
		$e->setFormat($format);
	}

	/**
	 *
	 * @priority HIGHEST
	 *
	 * @param EntityDamageEvent $e
	 *
	 * @throws \InvalidStateException
	 *
	 */
	public function onDamage(EntityDamageEvent $e){
		$p = $e->getEntity();
		if($p instanceof Player){

			if($this->core->getConfig()->getAll()["anti_cheat"]["block_one_hit_kill"] and $this->core->PrestigeSocietyAntiCheat->detectOneHitKill($e)){
				$p->kick(RandomUtils::colorMessage($this->core->getMessage("anti_cheat", "kick_one_hit_kill")));
				if($this->core->getConfig()->getAll()["log_hacking"]){
					$this->core->getLogger()->notice("[" . date("r") . "] I kicked a one hit kill hacker: " . $p->getName() . ".");
				}
			}

			if($this->core->getConfig()->getAll()["anti_cheat"]["block_kill_aura"] and $this->core->PrestigeSocietyAntiCheat->detectKillAura($p)){
				$p->kick(RandomUtils::colorMessage($this->core->getMessage("anti_cheat", "kick_kill_aura")));
				if($this->core->getConfig()->getAll()["log_hacking"]){
					$this->core->getLogger()->notice("[" . date("r") . "] I kicked a kill aura hacker: " . $p->getName() . ".");
				}
			}
		}
		if($e instanceof EntityDamageByEntityEvent){
			if($this->core->PrestigeSocietyAntiCheat->detectAntiKnockBack($e) and $this->core->getConfig()->getAll()["anti_cheat"]["block_anti_knockback"]){
				$p->kick(RandomUtils::colorMessage($this->core->getMessage("anti_cheat", "kick_anti_knock_back")));
				if($this->core->getConfig()->getAll()["log_hacking"]){
					$this->core->getLogger()->notice("[" . date("r") . "] I kicked a anti-knock-back hacker: " . $p->getName() . ".");
				}
			}
		}

		if($e->getCause() == EntityDamageEvent::CAUSE_FALL){
			if($p->getLevel()->getName() == "pvp") $e->setCancelled();
		}
	}
}