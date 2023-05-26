<?php

namespace LbCore;

use Kits\Kit;
use Kits\KitData;
use Kits\task\SaveKitsTask;
use Kits\exceptions\KitBaseException;
use LbCore\language\Translate;
use LbCore\LbCore;
use LbCore\player\exceptions\PlayerBaseException;
use LbCore\player\LbPlayer;
use pocketmine\item\Item;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use Logger\Logger;
use LbCore\task\MuteRequest;
use pocketmine\math\Vector3;
use LbCore\chat\ChatClasser;

/**
 * Class Command contains methods to handle things were typed in chat
 * (like /mute, /register etc)
 *
 *
 */
class LbCommand {

	/*$blockedCommands is used by other plugins to set overridden commands*/
	private $blockedCommands = array();
    
	/*reports about hackers*/
	private $reports = array();
	
	const MIN_PASS_LENGTH = 3;
	const COMMANDS_COUNT_ON_PAGE = 6;
	//const COUNT_REPORT_TO_SEND = 3;
	
	/**
	 * @param $plugin
	 */
	public function __construct($plugin) {
		$this->plugin = $plugin;
	}

	/**
	 * base method called from LbCore,
	 * prepares command to call suitable method
	 *
	 * @param string $action
	 * @param array $params
	 */
	public function __call(string $action, $params = array()) {
		$sender = $params[0]['sender'];
		$args = $params[0]['args'];
		
		//check if player is muted
		if ($sender->isMuted() && $action !== 'unmute') {
			$msg = Translate::getInstance()->getTranslatedString($sender->language, 'COMMAND_WHEN_MUTED');
			$sender->sendImportantMessage($msg);
			return;
		}

		//check if sender is not LbPlayer - return
		if (!$sender instanceof LbPlayer) {
			$this->plugin->getLogger()->warning("This command cannot be run from the console.");
			return false;
		}
		//check if command is blocked - return
		if (in_array($action, $this->blockedCommands)) {
			return false;
		}
		//prepare suitable method
		$action =  $action;
		if (!method_exists($this, $action)) {
			// TODO exception
			$sender->sendLocalizedMessage('NOPE');
		}
		else {
			return $this->$action($sender, $args);
		}
	}
	
	/**
	 * Mark command as blocked to overwrite it inside custom plugin
	 * 
	 * @param string $commandName
	 */	
	public function setCommandAsBlocked(string $commandName = '') {
		//check if commandName is valid and command exists
		if (!$commandName) {
			return false;
		}
		$methodName = $commandName;
		if (!method_exists($this, $methodName)) {
			return false;
		}
		
		//add commandName into array blockedCommands
		$this->blockedCommands[] = $commandName;
	}
	
	/**
	 * Block (or ignore) specified player,
	 * without args try to block last private messaged player
	 * 
	 * @param LbPlayer $sender
	 * @param array $args
	 * @return boolean
	 */
	private function block(LbPlayer $sender, array $args) {
		if (!$this->isValidUsername($sender)) {
			return false;
		}
		if (!$this->isAuthenticated($sender)) {
			return false;
		}
		$targetPlayerName = "";
		if (!isset($args[0])) {
			//try to find last messaged player
			$targetPlayerName = strtolower(trim($sender->getLastMessageFrom()));
		} else {//elseif args isset 
			$targetPlayerName = strtolower(trim($args[0]));	
		}
		//if player isset 
		if (!empty($targetPlayerName)) {
			//block him
			$playerList = $this->getSimilarPlayersNames($targetPlayerName);
			foreach ($playerList as $playerName) {
				$sender->ignorePlayer(strtolower($playerName));
				$sender->sendLocalizedMessage('IGNORE_ADD', array($playerName));
			}
		} else {
			$sender->sendLocalizedMessage("BLOCK_USAGE");
		}		
	}

		/**
	 * Mute all chat messages for sender or mute chat for specifically player
	 *
	 * @param lbPlayer $sender
     * @target LbPlayer $target
	 */
	private function mute(LbPlayer $sender, $args = null) {
        if($args == null){
            $sender->sendLocalizedMessage('MUTE_NO_MORE_CHAT');
            $sender->setMuteValue(true);
        }else{
            if (!$sender->isAuthorized() || !in_array(strtolower($sender->getName()), LbCore::$lbsgStaffNames)) {
                $sender->sendMessage(TextFormat::GRAY . 'For lbsg staff only.');
                return true;
            }
            //get player name from $args and look for it among online players
            $player = null;
            foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
                if(strtolower($onlinePlayer->getName()) == strtolower($args[0])){
                    $player = $onlinePlayer;
                    break;
                }
            }
            if ($player != null) {
                $this->plugin->getServer()->getScheduler()->scheduleAsyncTask(
                    new MuteRequest($player->getName(), $sender->getName())
                );
            }
        }
	}


	/**
	 * Toggle to show all messages in chat for sender
	 *
	 * @param lbPlayer $sender
	 */
	private function unmute(LbPlayer $sender) {
		$sender->setMuteValue(false);
		$sender->sendLocalizedMessage('UNMUTE_RECEIVE_ALL_CHAT');
	}


	/**
	 * Show current coordinates of sender on map
	 *
	 * @param lbPlayer $sender
	 */
	private function getpos(LbPlayer $sender) {
		$sender->sendMessage(Translate::PREFIX_PLAYER_ACTION."X: ".
		TextFormat::AQUA.round($sender->getX(), 0).TextFormat::YELLOW." Y: ".
		TextFormat::AQUA.round($sender->getY(), 0).TextFormat::YELLOW." Z: ".
		TextFormat::AQUA.round($sender->getZ(), 0).TextFormat::YELLOW."Level: ".
		TextFormat::AQUA.$sender->getLevel()->getName());
	}
	
	/**
	 * Performs the specified action in chat
	 * THIS COMMAND IS DISABLED NOW IN plugin.yml
	 * 
	 * @param LbPlayer $sender
	 * @param array $args
	 * @return boolean
	 */
	private function me(LbPlayer $sender, array $args) {
		if (!$this->isValidUsername($sender)) {
			return false;
		}
		if (!$this->isAuthenticated($sender)) {
			return false;
		}
		//check for message
		if (!isset($args[0])) {
			$sender->sendLocalizedMessage('ME_USAGE');
			return false;
		}
		
		$prefix = "* ";
		if ($sender instanceof LbPlayer) {
			$prefix .= $sender->getDisplayName();
		} else {
			$prefix .= $sender->getName();
		}
		//also check with chatfilter
		$lbcore = LbCore::getInstance();
		$message = "";
		foreach ($args as $arg) {
			if (trim($arg)) {
				if (!is_null($lbcore) && $lbcore->filter->check($sender, trim($arg))) {
					$message .= " " . preg_replace('/[^\x20-\x7e]/', '', $arg);				
				} else {
					$message = "";
					break;
				}
			}
		}
		if ($message) {
			Server::getInstance()->broadcastMessage($prefix . $message);
		}
	}

	/**
	 * Registration process
	 *
	 * @param lbPlayer $sender
	 * @param array $args
	 * @return boolean
	 */
	private function register(LbPlayer $sender, array $args) {
		if (!$this->isValidUsername($sender)) {
			return false;
		}
		$chat = new ChatClasser();
		if ($chat->check($sender->getName()) && $chat->getIsProfane()) {
			$sender->sendLocalizedMessage("BAD_USERNAME");
			return;
		}
		$sender->changeChatModeToRegistration();
	}


	/**
	 * Login process
	 *
	 * @param LbPlayer $sender
	 * @param array $args
	 */
	private function login(LbPlayer $sender, array $args) {
		if (!$this->isValidUsername($sender)) {
			return false;
		}
		if (!$sender->isRegistered()) {
			$sender->sendLocalizedMessage('CMD_REQUIRE_LOGIN');
			return false;
		}

		//look for valid password
		if(isset($args[0])) {
			$password = $args[0];
			if(!$sender->isAuthorized()) {
//				try {
				$sender->login($password);
//					$this->plugin->onSuccessfulLogin($sender);
//				} catch(PlayerBaseException $e) {
//					$e->getPlayer()->sendLocalizedMessage($e->getMessage());
//				}
			} else {
				$sender->sendLocalizedMessage('LOGIN_ALREADY_LOGGED_IN');
			}
		} else {
			$sender->sendLocalizedMessage('LOGIN_USAGE');
		}
	}


	/**
	 * Ignore messages from some player
	 *
	 * @param lbPlayer $sender
	 * @param array $args
	 * @return boolean
	 */
	private function ignore(LbPlayer $sender, array $args) {
		if (!$this->isValidUsername($sender)) {
			return false;
		}
		if (!$this->isAuthenticated($sender)) {
			return false;
		}
		//check for ignored player name
		if (!isset($args[0])) {
			$sender->sendLocalizedMessage('IGNORE_USAGE');
			return false;
		}
		$playerList = $this->getSimilarPlayersNames(strtolower($args[0]));
		foreach ($playerList as $playerName) {
			$sender->ignorePlayer(strtolower($playerName));
		}
		$sender->sendLocalizedMessage('IGNORE_ADD', array($args[0]));
	}

	/**
	 * Unignore messages from some player
	 *
	 * @param lbPlayer $sender
	 * @param array $args
	 */
	private function unignore(LbPlayer $sender, array $args) {
		if (!$this->isValidUsername($sender)) {
			return false;
		}
		if (!$this->isAuthenticated($sender)) {
			return false;
		}
		//check for ignored player name
		if (!isset($args[0])) {
			$sender->sendLocalizedMessage('UNIGNORE_USAGE');
			return false;
		}
		if ($sender->unignorePlayer(strtolower($args[0]))) {
			$sender->sendLocalizedMessage('UNIGNORE_REMOVE', array($args[0]));
		} else {
			$sender->sendLocalizedMessage('UNIGNORE_ERROR', array($args[0]));
		}
	}


	/**
	 * Check a player's coin balance
	 *
	 * @param lbPlayer $sender
	 * @return boolean
	 */
	private function coins(LbPlayer $sender, array $args) {
		if (!$this->isValidUsername($sender)) {
			return false;
		}
		if (!$this->isAuthenticated($sender)) {
			return false;
		}
		//get coin owner
		$coinsOwner = $sender->getName();
		if(isset($args[0])) {
			$coinsOwner = $args[0];
		}
		//get balance
		$this->plugin->getServer()->getScheduler()->scheduleAsyncTask(new task\CoinsRequest($coinsOwner, $sender->getName()));
	}


	/**
	 * Pay a player
	 *
	 * @param lbPlayer $sender
	 * @param array $args
	 * @return boolean
	 */
	private function pay(LbPlayer $sender, array $args) {
		if (!$this->isValidUsername($sender)) {
			return false;
		}
		if (!$this->isAuthenticated($sender)) {
			return false;
		}

		//check coins value
		if(count($args) === 2) {
			$coinsReceiver = $args[0];
			$coins = intval($args[1]);
			if($coins === false or $coins < 1) {
				$sender->sendLocalizedMessage('PAY_INVALID');
			} else {
				$this->plugin->getServer()->getScheduler()->scheduleAsyncTask(new task\CoinsTransfer($sender->getName(), $coinsReceiver, $coins));
			}
		} else {
			$sender->sendLocalizedMessage('PAY_USAGE');
		}
	}


	/**
	 * Manage player's friend list
	 *
	 * @param lbPlayer $sender
	 * @param array $args
	 */
	private function friend(LbPlayer $sender, array $args) {
		//create subcommand - by default help or args if args[0] isset
		$subCommand = 'help';
		if (isset($args[0])) {
			$subCommand = strtolower($args[0]);
		}
		if ($subCommand == 'help') {
			//show help message and return
			$sender->sendLocalizedMessage('FRIEND_HELP');
			return;
		}
		//if sender is authorized
		if ($sender->isAuthorized()) {
			//$sender->sendMessage(TextFormat::RED."Friends temporarily disabled");
			//switch by subcommand and call tasks
			if (isset($args[1])) {
				switch ($subCommand) {
					case "remove":
						$this->plugin->getServer()->getScheduler()->scheduleAsyncTask(new task\RemoveFriendRequest($sender->getName(), $args[1], $sender->getPassHash()));
						return;
					case "accept":
						$this->plugin->getServer()->getScheduler()->scheduleAsyncTask(new task\AcceptFriendRequest($args[1], $sender->getName(), $sender->getPassHash()));
						return;
					case "deny":
						$this->plugin->getServer()->getScheduler()->scheduleAsyncTask(new task\DenyFriendRequest($args[1], $sender->getName(), $sender->getPassHash()));
						return;
					default:
						// to much args for command
						$sender->sendLocalizedMessage('FRIEND_HELP');
						return;
				}
			}
			
			switch ($subCommand) {
				case "list":
					$this->plugin->getServer()->getScheduler()->scheduleAsyncTask(new task\FriendListRequest($sender->getName(), $sender->getPassHash()));
					return;
				default: //send friend request
					$this->plugin->getServer()->getScheduler()->scheduleAsyncTask(new task\SendFriendRequest($sender->getName(), $subCommand, $sender->getPassHash()));
					return;
			}
		} else {//else show error
			$sender->sendLocalizedMessage('CMD_REQUIRE_LOGIN');
		}

	}

	/**
	 * Change password for player
	 *
	 * @param LbPlayer $sender
	 * @return boolean
	 */
	private function changepw(LbPlayer $sender) {
		if (!$this->isValidUsername($sender)) {
			return false;
		}
		if (!$this->isAuthenticated($sender)) {
			return false;
		}
		
		$sender->sendLocalizedMessage('CHANGEPW_CHANGE', array($sender->getName()));
		$sender->changeChatModeToPassChange();
	}

	/**
	 * Enable/disable VIP advantages
	 * Doesn't work. Request always return false.
	 *
	 * @param LbPlayer $sender
	 * @return boolean
	 */
	private function vip(LbPlayer $sender) {
		if (!$this->isValidUsername($sender)) {
			return false;
		}
		if (!$this->isAuthenticated($sender)) {
			return false;
		}

		$this->plugin->getServer()->getScheduler()->scheduleAsyncTask(new task\VIPRequest($sender->getName()));
	}


	/**
	 * Show total player statistics (from DB)
	 *
	 * @param LbPlayer $sender
	 * @param array $args
	 */
	private function stats(LbPlayer $sender, array $args) {
		if (!$this->isValidUsername($sender)) {
			return false;
		}
		if (!$this->isAuthenticated($sender)) {
			return false;
		}
		
		//get player to show statistics
		$playerToShowStat = $sender->getName();
		if(isset($args[0])) {
			$playerToShowStat = $args[0];
		}
		$this->plugin->getServer()->getScheduler()->scheduleAsyncTask(new task\StatsRequest($playerToShowStat, $sender->getName()));
	}


	/**
	 * Change player's language
	 * (now it works only for English, Spanish, Dutch, German)
	 *
	 * @param LbPlayer $sender
	 * @param array $args
	 */
	private function lang(LbPlayer $sender, array $args) {
		//check if language request is valid
		if (!isset($args[0])) {
			$sender->sendLocalizedMessage("LANG_USAGE");
			return false;
		}
		//set language
		$sender->language = Translate::getInstance()->getAllowedLanguage($args[0]);		
		$sender->sendLocalizedMessage("LANG_CHANGE");
	}
	
	/**
	 * Send private message to specified player
	 * 
	 * @param LbPlayer $sender
	 * @param array $args
	 * @return boolean
	 */
	private function tell(LbPlayer $sender, array $args) {
		if (!$this->isValidUsername($sender)) {
			return false;
		}
		if (!$this->isAuthenticated($sender)) {
			return false;
		}
		if($sender->isChatBanned()){
			return false;
		}
		//check if receiver and message exists
		if (count($args) < 2) {
			$sender->sendLocalizedMessage("TELL_USAGE");
			return false;
		}		
		//check if receiver is online
		$receiver = Server::getInstance()->getPlayer($args[0]);
		if(!$receiver instanceof LbPlayer){
			$sender->sendLocalizedMessage("PLAYER_NOT_ONLINE");
			return;
		}
		//check for messaging oneself
		$senderName = $sender->getName();
		$receiverName = $receiver->getName();
		if (strtolower($senderName) === strtolower($receiverName)){
			$sender->sendLocalizedMessage("TELL_ONESELF");
			return;
		}
		//check if sender is not ignored
		if ($receiver->isIgnorePlayer(strtolower($senderName))) {
			$sender->sendLocalizedMessage("YOU_IGNORED", array($receiver->getName()));
			return;
		}
		//also check with chatfilter
		$lbcore = LbCore::getInstance();
		$message = "";
		unset($args[0]);
		foreach ($args as $key => $arg) {
            $arg = trim($arg);
            if(strlen($arg) > 0){
                if (!is_null($lbcore) && $lbcore->filter->check($sender, $arg, false)) {
                    $message .= " " . preg_replace('/[^\x20-\x7e]/', '', $arg);				
                } else {
                    $message = "";
                    break;
                } 
            }
		}
		if ($message != "") {
            $message = substr($message, 1);
			$receiver->setLastMessageFrom($senderName);
			$sender->sendMessage("[me -> " . $receiver->getName() . "] " . $message);
			$receiver->sendMessage("[" . $senderName . " -> me] " . $message);
		}
	}
	
	/**
	 * Answer to a player who messaged you last
	 * 
	 * @param LbPlayer $sender
	 * @param array $args
	 * @return boolean
	 */
	private function reply(LbPlayer $sender, array $args) {
		if (!$this->isValidUsername($sender)) {
			return false;
		}
		if (!$this->isAuthenticated($sender)) {
			return false;
		}
		if($sender->isChatBanned()){
			return;
		}
		//check if message exists
		if (!isset($args[0])) {
			$sender->sendLocalizedMessage("REPLY_USAGE");
			return false;
		}
		//check if sender have saved receiver name
		$receiverName = $sender->getLastMessageFrom();
		if (empty($receiverName)) {
			$sender->sendLocalizedMessage("REPLY_TO_NOBODY");
			return false;
		}
		//check if receiver is online
		$receiver = Server::getInstance()->getPlayer($receiverName);
		if(!$receiver instanceof LbPlayer){
			$sender->sendLocalizedMessage("PLAYER_NOT_ONLINE");
			return;
		}
		//no need to check for messaging oneself, he can't even message himself once
		//check if sender is not ignored
		$senderName = $sender->getName();
		if ($receiver->isIgnorePlayer(strtolower($senderName))) {
			$sender->sendLocalizedMessage("YOU_IGNORED", array($receiver->getName()));
			return;
		}
		//also check with chatfilter
		$lbcore = LbCore::getInstance();
		$message = "";
		foreach ($args as $arg) {
			if (trim($arg)) {
				if (!is_null($lbcore) && $lbcore->filter->check($sender, trim($arg), false)) {
					$message .= " " . preg_replace('/[^\x20-\x7e]/', '', $arg);				
				} else {
					$message = "";
					break;
				}
			}
		}
		if ($message) {
			$receiver->setLastMessageFrom($senderName);
			$sender->sendMessage("[me -> " . $receiver->getName() . "] " . $message);
			$receiver->sendMessage("[" . $senderName . " -> me] " . $message);
		}

	}


	/**
	 * Toggle tag function (hide or show name prefix for player in chat)
	 *
	 * @param lbPlayer $sender
	 */
	private function tag(LbPlayer $sender){
		if (!$this->isValidUsername($sender)) {
			return false;
		}
		if (!$this->isAuthenticated($sender)) {
			return false;
		}
		$rankStatus = !($sender->isShownRanksInPrefix());
		$sender->updateRanksInPrefixStatus($rankStatus);
		$sender->updateDisplayedName();
		
		if($rankStatus){
			$sender->sendLocalizedMessage('RANK_SHOW');
		} else{
			$sender->sendLocalizedMessage('RANK_HIDE');
		}

	}
	
	/**
	 * Send server dns name (like sg13.lbsg.net)
	 *
	 * @param lbPlayer $sender
	 */
	private function server(LbPlayer $sender) {
		$sender->sendMessage($this->plugin->getDomainName());
	}

	/**
	 * system command - set some kit for player
	 * 
	 * it doesn't save kit into player object, 
	 * so if you die you lose all kits obtained by this command
	 *
	 * @param lbPlayer $sender
	 * @param array $args
	 * @return boolean
	 */
//	private function kits(LbPlayer $sender, array $args) {
//		if (!Kit::isEnable()) {
//			return;
//		}
//		if (!isset($args[0]) || !isset($args[1])) {
//			return false;
//		}
//		//get player by name
//		$player = $this->plugin->getServer()->getPlayer($args[0]);
//		if ($player instanceof LbPlayer) {
//			try {
//				Kit::activateKitsForPlayer($player, array_slice($args, 1));
//			} catch (\Kits\exceptions\KitBaseException $e) {
//				// do nothing, only for development
//			}
//		}
//	}
	
	/**
	 * Get kit list description or change kit (for vip players)
	 * 
	 * @param LbPlayer $sender
	 * @param array $args
	 */
	private function kits(LbPlayer $sender, array $args) {
		if (!Kit::isEnable()) {
			return;
		}
		$subCommand = isset($args[0]) ? strtolower(trim($args[0])) : 'help';
		//show command usage info
		if ($subCommand == 'help') {
			$sender->sendLocalizedMessage("KITS_HELP");
			return;
		}		
		//show paged list of available kits
		if ($subCommand == 'list') {
			$kits = KitData::getKits();
			if (!$kits) {
				$sender->sendLocalizedMessage("NO_KITS_FOUND");
				return;
			}
			//page logic
			$page = 1;
			if (isset($args[1]) && intval($args[1]) > 0) {
				$page = intval($args[1]);
			}
			$lastPage = ceil(count($kits) / self::COMMANDS_COUNT_ON_PAGE);
			if ($page > $lastPage) {
				$sender->sendLocalizedMessage('HELP_TOO_FAR', array(), Translate::PREFIX_ACTION_FAILED);
				return false;
			}
			$sender->sendLocalizedMessage("KITS_HEADER", array($page, $lastPage));
			$prevKitsCount = ($page - 1) * self::COMMANDS_COUNT_ON_PAGE;
			for ($i = $prevKitsCount; $i < ($prevKitsCount + self::COMMANDS_COUNT_ON_PAGE); $i++) {
				if(isset($kits[$i])) {
					$kit = $kits[$i];
					$sender->sendLocalizedMessage($kit->description, [], TextFormat::DARK_PURPLE . $kit->name . ": " . TextFormat::YELLOW);
				}
			}
			return;
		}
		//show info about specified kit
		if ($subCommand == 'info') {
			if (!isset($args[1])) {
				$sender->sendLocalizedMessage("CHOOSE_KIT");
				return;
			}
			//look for valid kit name
			$infoKitName = strtolower(trim($args[1]));
			$infoKitId = KitData::getKitIdByName($infoKitName);
			if (!$infoKitId) {
				$sender->sendLocalizedMessage("UNKNOWN_KIT");
				return;
			}
			//get info about chosen kit
			$infoKitDesc = KitData::getKitDesc($infoKitId);
			$sender->sendLocalizedMessage($infoKitDesc, [], TextFormat::DARK_PURPLE . ucfirst($infoKitName) . ": " . TextFormat::YELLOW );
			return;
		}

		//some checks before change kit
		if (!$this->isAuthenticated($sender)) {
			return;
		}
		if (!$sender->isVip()) {
			$sender->sendLocalizedMessage("ONLY_FOR_VIP");
			return;
		}
		//look for valid kit name
		$targetKitId = KitData::getKitIdByName($subCommand);
		if (!$targetKitId) {
			$sender->sendLocalizedMessage("UNKNOWN_KIT");
			return;
		}
		//look if player already have that kit
		$currentKitId = $sender->getKits();
		if ($currentKitId && ($currentKitId == $targetKitId)) {
			$sender->sendLocalizedMessage("HAVE_KIT");
			return;
		}		
		//give kit to player and save it to db
		Kit::deactivateKitsForPlayer($sender);//clear previous kit options if isset
		$sender->addKit($targetKitId);
        $this->plugin->getServer()->getScheduler()->scheduleAsyncTask(
				new SaveKitsTask($sender->getName(), $sender->getKits())
		);
//		$task = new SaveKitsTask($sender->getName(), $sender->getKits());
//		Server::getInstance()->getScheduler()->scheduleAsyncTask($task);
		$sender->sendLocalizedMessage("VIP_SELECT_KIT", array(ucfirst($subCommand)));
		$sender->sendLocalizedMessage(KitData::getKitDesc($targetKitId), [], TextFormat::YELLOW);
		//activate kit if player is in game
		if ($sender->getState() === LbPlayer::IN_GAME) {
			try {
				Kit::activateKitsForPlayer($sender, $sender->getKits());
			} catch (KitBaseException $e) {
				Server::getInstance()->getLogger()->warning($e->getMessage());
				return;
			}
		}			
		
	}
	
	private function shield(LbPlayer $sender, array $args) {
		$playerName = strtolower($sender->getName());
		if (!$sender->isAuthorized() || !in_array(strtolower($playerName), LbCore::$lbsgStaffNames)) {
			$sender->sendMessage('For lbsg staff only.');
			return true;
		}
		
		if (!$sender->isInvoulnerable()) {
			$sender->setInvoulnerable();
			$sender->sendMessage('Shield is enabled.');
		} else {
			$sender->setInvoulnerable(false);
			$sender->sendMessage('Shield is disabled.');
		}
		
		return true;
	}
	
	private function invisible(LbPlayer $sender, array $args) {
		$playerName = strtolower($sender->getName());
		if (!$sender->isAuthorized() || !in_array(strtolower($playerName), LbCore::$lbsgStaffNames)) {
			$sender->sendMessage(TextFormat::GRAY . 'For lbsg staff only.');
			return true;
		}
		
		if (!$sender->isInvisible()) {
			$sender->despawnFromAll();
			$sender->setInvisible();
			$sender->sendMessage(TextFormat::GRAY . 'Invisibility is enabled.');
		} else {
			$sender->spawnToAll();
			$sender->setInvisible(false);
			$sender->sendMessage(TextFormat::GRAY . 'Invisibility is disabled.');
		}
		
		return true;
	}
	
	private function lbgive(LbPlayer $sender, array $args) {
		if (!$sender->isAuthorized() || !in_array(strtolower($sender->getName()), LbCore::$lbsgStaffNames)) {
			$sender->sendMessage(TextFormat::GRAY . 'For lbsg staff only.');
			return true;
		}
			
		if(count($args) < 1){
			$sender->sendLocalizedMessage('GIVE_USAGE');
			return false;
		}

		$item = Item::fromString($args[0]);

		if(!isset($args[1])){
			$item->setCount($item->getMaxStackSize());
		}else{
			$item->setCount((int) $args[1]);
		}

		if($item->getId() == 0){
			$sender->sendMessage(TextFormat::RED . "There is no item called " . $args[0] . ".");
			return true;
		}

//		$sender->getInventory()->addItem(clone $item);
		$itemIndex = $sender->getInventory()->firstEmpty();
		$sender->setHotbarItem($itemIndex, clone $item);
		$sender->sendMessage(TextFormat::GRAY . "The " . $item->getCount() . " of " . $item->getName() . " is given");
		return true;
	}
	
	/**
	 * hacker report 
	 *
	 * @param lbPlayer $sender
	 * @param array $args
	 * @return boolean
	 */
	private function hacker(LbPlayer $sender, array $args) {
		if (!$this->isValidUsername($sender)) {
			return false;
		}
		if (!$this->isAuthenticated($sender)) {
			return false;
		}

		if (!isset($args[0])) {
			$sender->sendLocalizedMessage('HACKER_USAGE');
			return false;
		}
		$path = Server::getInstance()->getDataPath() . "logs/";
		$filename = $path . date('Y.m.d') . '_' . $this->getServerName() . '_hack.txt';
		if(!file_exists($filename)) {
			$title = "#Hacking Log File\n#HINT = Hacking Integration\n#SCORE = Hacking score\n#SUS = How much they are supected of hacking.\n"
					. str_pad("Time (UTC)", 12) . str_pad("Player Name", 20) . str_pad("Hacking Score", 20) . str_pad("Hacking Integration", 20) . str_pad("Suspicion Count", 20) ."Reason\n";
			@file_put_contents($filename, $title, FILE_APPEND | LOCK_EX);
		}
		$reason = implode(" ", $args);
		if (empty(trim($reason))) {
			$sender->sendLocalizedMessage('HACKER_USAGE');
			return false;
		}
		$msg = str_pad(date("G:i"), 12) . str_pad("HACKING_REPORT", 20) . "\"" . $reason . "\"\n";
		$slackMsg = "*" . date("d.m.Y G:i") . "* HACKING_REPORT \"" . $reason . "\"";
		@file_put_contents($filename, $msg, FILE_APPEND | LOCK_EX);
		Logger::getInstance()->write($slackMsg, false, Logger::WARNING);
//		$hackerPlayer = strtolower($args[0]);
//		$playerList = $this->getSimilarPlayersNames($hackerPlayer);
//		if(count($playerList) == 0){
//			$sender->sendLocalizedMessage('HACKER_USAGE');
//			return false;
//		}
//		$reasons = $args;
//		unset($reasons[0]);
//		$reason = implode(" ", $reasons);
//		foreach ($playerList as $playerName) {
//			$this->reportHacker($sender, $playerName, $reason);
//		}

		$sender->sendLocalizedMessage('REPORT_SEND');
		return true;
	}
	
	/**
	 * Send warning for player about spamming, used by lbsg whitelist,
	 * target player must be an arg
	 * 
	 * @param LbPlayer $sender
	 * @param array $args
	 * @return boolean
	 */
	private function warn(LbPlayer $sender, array $args) {
		if (!$sender->isAuthorized() || !in_array(strtolower($sender->getName()), LbCore::$lbsgStaffNames)) {
			$sender->sendMessage(TextFormat::GRAY . 'For lbsg staff only.');
			return true;
		}
		if(count($args) < 1){
			$sender->sendLocalizedMessage('WARN_USAGE');
			return false;
		}
		//get player name from $args1 and look for it among online players
		$target = trim(strtolower($args[0]));
		$players = $this->getSimilarPlayersNames($target);
		//inform sender about no target player
		if (count($players) == 0) {
			$sender->sendLocalizedMessage("WARN_NO_TARGET", array($target));
			return false;
		}
		//if player is found send him warning
		foreach ($players as $playerName) {
			$player = Server::getInstance()->getPlayer($playerName);
			$player->sendLocalizedPopup("WARNING_BEFORE_MUTE");
			$player->sendLocalizedMessage("WARNING_BEFORE_MUTE");
		}
		return true;
	}
	
	/**
	 * Move modetator to point
	 *
	 * @param lbPlayer $sender
	 * @param array $args
	 * @return boolean
	 */
	private function move(LbPlayer $sender, array $args) {
		if (!($sender instanceof LbPlayer)) {
			return true;
		}

		// check player name
		$playerName = strtolower($sender->getName());
		if (!$sender->isAuthorized() || !in_array(strtolower($playerName), LbCore::$lbsgStaffNames)) {
			$sender->sendMessage('For lbsg staff only.');
			return true;
		}
		
		if(count($args) !== 3 || $sender->getLevel() === NULL){
			$sender->sendMessage(TextFormat::RED . "Usage: /move <x> <y> <z>");
			return true;
		}
		
		$pos = new Vector3($args[0], $args[1], $args[2]);
		$sender->teleport($pos);
		return true;
	}
	
	/**
	 * Allow player to fly (now only admin feature)
	 * 
	 * @param LbPlayer $sender
	 * @param array $args
	 * @return boolean
	 */
	private function fly(LbPlayer $sender, array $args) {
		if (!($sender instanceof LbPlayer)) {
			return true;
		}

		// check player name
		$playerName = strtolower($sender->getName());
		if (!$sender->isAuthorized() || !in_array(strtolower($playerName), LbCore::$lbsgStaffNames)) {
			$sender->sendMessage('For lbsg staff only.');
			return true;
		}
		//toggle flying mode
//		if ($sender->getGamemode() !== 1) {
//			$sender->setGamemode(\pocketmine\Player::CREATIVE);
		if (!$sender->getAllowFlight()) {
			$sender->setAllowFlight(true);
			$sender->sendMessage(TextFormat::GREEN . "Eagle fly free");
		} else {
			$sender->setAllowFlight(false);
//			$sender->setGamemode(\pocketmine\Player::SURVIVAL);
			$sender->sendMessage(TextFormat::GREEN . "Flying mode is disabled. Soft landing!");
		}
		return true;
	}

	/**
	 * Method checks if username is valid (not default like Steve or test)
	 *
	 * @param lbPlayer $sender
	 * @return boolean
	 */
	private function isValidUsername(LbPlayer $sender) {
		$isValid = true;
		$name = strtolower($sender->getName());
		$invalidNames = array(
			'steve',
			'stevie',
			'game_difficulty'
		);

		if(in_array($name, $invalidNames)) {
			$sender->sendLocalizedMessage('CMD_DEFAULT_ACCOUNT');
			$isValid = false;
		}
		return $isValid;
	}

	/**
	 * Method checks if player is registered and have not been authenticated yet
	 *
	 * @param lbPlayer $sender
	 * @return boolean
	 */
	private function isAuthenticated(LbPlayer $sender) {
		$isAuthenticated = true;

		if (!$sender->isAuthorized()) {
			$sender->sendLocalizedMessage('CMD_REQUIRE_LOGIN');
			$isAuthenticated = false;
		}

		return $isAuthenticated;
	}
	
	private function getSimilarPlayersNames(string $playerName) {
		$maxSimilarity = 0;
		$targets = array();
		$onlinePlayers = Server::getInstance()->getOnlinePlayers();
		
		foreach ($onlinePlayers as $player) {
			similar_text(strtolower($player->getName()), $playerName, $percent);
			if ($percent < 85) {
				continue;
			}
			
			if ($percent == 100) {
				$targets = array();
				$targets[] = $player->getName();
				break;
			}
			
			if ($maxSimilarity < round($percent)) {
				$maxSimilarity = round($percent);
				$targets = array();
			}
			$targets[] = $player->getName();
		}
		
		return $targets;
	}
	
//	private function reportHacker(LbPlayer $sender, $playerName, $reason = "") {
//		if (!isset($this->reports[$playerName])) {
//			$this->reports[$playerName] = array();
//		}
//		$this->reports[$playerName][$sender->getName()] = array(
//			'reason' => $reason,
//			'time' => date("G:i")
//		);
//		if (count($this->reports[$playerName]) >= self::COUNT_REPORT_TO_SEND) {
//			$messages = array();
//			$dates = array();
//			foreach ($this->reports[$playerName] as $report){
//				if(!empty($report['reason'])) {
//					$messages[] = $report['reason'];
//				}
//				$dates[] = $report['time'];
//				
//			}
//			$msg = "Hacking Report on *" .  $playerName . "* [Date: " . date('d.m.Y') . " | Time: " . implode(", ", $dates) . " | Messages: ".  implode(", ", $messages) . "]";
//			Logger::getInstance()->write($msg, false, Logger::WARNING);
//			unset($this->reports[$playerName]);
//		}
//	}
	

	private function getServerName() {
		$serverName	= Server::getInstance()->getConfigString('server-dns', 'unknown.lbsg.net');
		$parsedServerName = explode(".", $serverName);
		return $parsedServerName[0];		
	}

}
