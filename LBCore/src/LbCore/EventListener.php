<?php

/**
 * WARNING: This file is only for tests, use LbEventListener for actual info
 */

namespace LbCore;

use LbCore\data\PluginData;
use LbCore\language\Translate;
use LbCore\player\exceptions\PlayerBaseException;
use LbCore\player\LbPlayer;
use LbCore\task\LoginRequest;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class EventListener implements Listener {

	private $plugin;
	private $gameType;

	public function __construct(LbCore $plugin) {
		$this->plugin = $plugin;
		$this->gameType = PluginData::getGameType();
	}

	/*
	 * 	Method replaced default Player class on custom class for whole project.
	 * 	It method should be called in each plugin which going to use not default player class.
	 * 	Also it should be overridden if your plugin using own player class inherited from LbPlayer.
	 */

	public function onPlayerCreation(PlayerCreationEvent $event) {
		$event->setPlayerClass(LbPlayer::class);
	}

	/**
	 * Ran when player joins a server. Instantiates the PlayerData object for that player and runs the first LoginRequest.
	 * Additional gametype specific logic can be added within the gametype plugins onPlayerLogin event.
	 *
	 * @param PlayerLoginEvent $event
	 */
	public function onPlayerLogin(PlayerLoginEvent $event) {
		$player = $event->getPlayer();
		$this->plugin->getServer()->getScheduler()->scheduleAsyncTask(
				new LoginRequest($player->getName(), $player->getID())
		);
	}

	/**
	 *
	 *
	 * @param PlayerDeathEvent $event
	 * @return bool
	 */
	public function onPlayerDeath(PlayerDeathEvent $event) {
		$player = $event->getEntity();

		if (!$player instanceof Player) {
			return false;
		}
		$damageEvent = $player->getLastDamageCause();
		if ($damageEvent instanceof EntityDamageByEntityEvent) {
			$attacker = $damageEvent->getDamager();
			if ($attacker instanceof Player) {
				//$this->plugin->getServer()->getScheduler()->scheduleAsyncTask(new task\KillRequest($player, $attacker));
			}
		}
	}

	/**
	 * When a player types a message this is called. Notes are within code. The order within the checks are:
	 * 1) If players account is locked, send message and return false
	 * 2) If they need authorization then then input is taken as password and checked
	 * 3) Change Password
	 * 4) Player Registration
	 * 5) Login request waiting
	 * 6) Chat Processor (filter)
	 * 7) Is chat muted
	 * Ater these checks are made it will see what gametype is being played and call a method in the Chatter class to
	 * complete the game specific logic. i.e. some gametypes have teams which only the same team can see the chat and
	 * some gametypes have tournaments where only people in the same tourny can see the chat
	 *
	 * @param PlayerChatEvent $event
	 * @return bool
	 */
	public function onPlayerChat(PlayerChatEvent $event) {
		$event->setCancelled(true);
		$player = $event->getPlayer();
		$message = $event->getMessage();
		
		if (!($player instanceof LbPlayer)) {
			return;
		}
		
        if($player->isLocked()) {
			$player->sendLocalizedMessage("ACCOUNT_LOCKED", array(), '// ', ' //');
			$player->sendMessage($player->getLockReason());
			return;
		}
		
		if (!$player->isRegistered()) {
			$event->setCancelled();
			$player->sendLocalizedMessage('NOT_REGISTERED');
			return;
		}
		
		/* Player Registration */
		if (!$player->isRegistered()) {
			if ($player->getChatMode() === LbPlayer::CHAT_MODE_REGISTRATION) {
				$player->registration($message);
				return;
			}
		}
		/* Checks password and try login */
		if ($player->isRegistered() && !$player->isAuthorized()) {
			try {
				$player->login($message);
				$this->plugin->onSuccessfulLogin($player);
			} catch (PlayerBaseException $e) {
				$player->sendLocalizedMessage($e->getMessage());
			}
			return;
		}
		/* Change Password */
		if ($player->getChatMode() === LbPlayer::CHAT_MODE_PWD_CHANGE) {
			$player->changePass($message);
			return;
		}

		if ($player->isMuted()) {
			$msg = Translate::getInstance()->getTranslatedString($player->language, "CHAT_WHEN_MUTED");
			$player->sendImportantMessage($msg);
			return;
		}

		/*
		 * Checking ordinary messages
		 */

		/* Chat filter */
		if ($this->plugin->filter->check($player, $message)) {
			/*
			 * remove all unusual for human eyes symbols from message
			 * (all symbols whose ASCII code (in hexadecimal) less then 0x20)
			 */
			$message = preg_replace('/[^\x20-\x7e]/', '', $message);
			$this->changeRecipientForMessage($event);
			$event->setCancelled(false);
		}
	}

	/*
	 * @param PlayerPreLoginEvent $event
	 * @return bool
	 */

	public function onPlayerPreLogin(PlayerPreLoginEvent $event) {
		$newPlayer = $event->getPlayer();

		if ($newPlayer->isAuthorized()) {
			$kickReason = Translate::getInstance()->getTranslatedString($newPlayer->language, "ALREADY_AUTHENTICATED");
			$newPlayer->kick($kickReason);
			$event->setCancelled(true);
			return false;
		}

		$players = $this->getPlayersFromSameIp($newPlayer->getAddress());
		$playersCount = count($players);

		if ($playersCount > 0) {
			$registered = 0;
			foreach ($players as $player) {
				if ($player->isAuthorized()) {
					$registered++;
				}
			}
		
			if ($playersCount > 3) {
				if ($playersCount > 10 || $playersCount - $registered > 3) {
					$kickReason = Translate::getInstance()->getTranslatedString($newPlayer->language, "IP_LIMITED");
					$newPlayer->kick($kickReason);
					$event->setCancelled(true);
					return false;
				}
			}
		}
	}

	/**
	 * This event triggered before each processing on player's message
	 *
	 * @param PlayerCommandPreprocessEvent $event
	 */
	public function onPlayerPrePreprocess(PlayerCommandPreprocessEvent $event) {
		$player = $event->getPlayer();
		$msg = $event->getMessage();

		// неведомая фигня
		if (strtolower(substr($msg, 0, strlen("/pocketmine:"))) == "/pocketmine:") {
			$event->setCancelled(true);
			$player->sendMessage(TextFormat::RED . "I'm afraid I can't let you do that, " . $player->getName() . ".");
			return;
		}
	}

	private function changeRecipientForMessage(PlayerChatEvent $event) {
		$recipients = $event->getRecipients();
		$sender = $event->getPlayer();

		if ($sender instanceof LbPlayer) {
			foreach ($recipients as $id => $player) {
				if ($player instanceof LbPlayer) {
					if ($player->isIgnorePlayer(strtolower($sender->getName()))) {
						unset($recipients[$id]);
					}
				}
			}
			$event->setRecipients($recipients);
		}
	}

	private function getPlayersFromSameIp($ip) {
		$result = array();
		$players = $this->plugin->getServer()->getOnlinePlayers();
		foreach ($players as $player) {
			if ($player->getAddress() === $ip) {
				$result[] = $player;
			}
		}

		return $result;
	}

}
