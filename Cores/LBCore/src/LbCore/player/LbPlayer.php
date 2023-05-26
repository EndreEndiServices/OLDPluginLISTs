<?php

namespace LbCore\player;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;
use pocketmine\network\SourceInterface;
use pocketmine\Player;
use pocketmine\Server;
use Kits\Kit;
use Kits\exceptions\KitBaseException;
use LbCore\language\Translate;
use LbCore\player\exceptions\KitAddDataException;
use LbCore\player\exceptions\KitDuplicationException;
use LbCore\player\exceptions\WrongPassException;
use LbCore\task\ChangePassword;
use LbCore\task\RegisterRequest;
use Pets\Pets;
use Pets\PetsManager;
use pocketmine\entity\Effect;
use LbCore\task\AuthRequest;

/**
 * Custom player to extend inside all game plugins
 * override player inside your plugin inside event onPlayerCreation
 */
class LbPlayer extends Player {

	// player state for alert system
	const IN_LOBBY = 0;
	const IN_COUNTDOWN = 1;
	const IN_GAME = 2;
	const NOT_IN_ARENA = -1;
	const VIP = 'vip';
	const VIP_PLUS = 'vip+';
	const DEFAULT_NAME_COLOR = TextFormat::WHITE;
	// modes for special work with chat
	const CHAT_MODE_NORMAL = 0;
	const CHAT_MODE_PWD_CHANGE = 1;
	const CHAT_MODE_REGISTRATION = 2;
	// TH consts
	const FOUND_TREASURE = "Congratulations, you have won a free tee-shirt.\n".
		"  You must be first to it claim at ".TextFormat::RED."lbsg.net/contest".TextFormat::GREEN."   \n".
		"           Subject to rules on page.           ";
	const TH_SHOW_MESSAGE_DURATION = 20;	// in seconds

	/**@var string*/
	protected $passHash = '';
	/** @var string */
	protected $bcryptPassHash = '';
	/**@var string*/
	public $language = 'English';
	/**@var string*/
	public $countryIsoCode = '';
	/**@var string*/
	public $idAddress = '';
	/**@var bool*/
	protected $isVipEnabled = true;
	/**@var string*/
	public $vipStatus = '';
	/**@var string*/
	protected $namePrefix = '';
	/**@var string*/
	protected $namePrefixWithRanks = '';
	/**@var bool*/
	protected $showRanksInPrefix = true;
	/**@var bool*/
	protected $isAuthenticated = false;
	/**@var bool*/
	protected $isRegistered = false;
	/**@var bool*/
	protected $isMuted = false;
	/**@var bool*/
	protected $isInvincible = false;
	/**@var bool*/
	protected $isInvisible = false;
	/**@var bool*/
	protected $isInvoulnerable = false;
	/**@var bool*/
	protected $isLocked = false;
	/**@var string*/
	protected $lockReason = '';
	// for statistics
	public $coinsNum = 0;
	public $killsNum = 0;
	public $deathNum = 0;
	public $goldNum = 0;
	public $hackingFlag = false;
	/**@var array*/
	public $ignoreList = array();
	
	// fields for alert system
	/**@var int*/
	protected $stateForAlertSystem = self::IN_LOBBY;
	/**@var int*/
	protected $currentArenaId = self::NOT_IN_ARENA;
	
	// for pass change and registraion processes
	/**@var int*/
	protected $chatMode = self::CHAT_MODE_NORMAL;
	/**@var array*/
	protected $passChangeData = array();
	/**@var array*/
	protected $registrationData = array();
	/** @var bool */
	protected $isNeedsNewPassHash = false;	// necessary to change the password
	
	// for kits component
	/**@var array*/
//	protected $kits = array();
	/**@var int - only one kit allowed*/
	protected $currentKit = 0;
	/**@var array*/
	protected $kitsAdditionalData = array();
	/**@var int - contains kit id on sign with that player interacted*/
	public $kitSignLastTapped = 0;

	//for vip lounge
	/**@var bool*/
	protected $pushedByLoungeGuard = false;
	/**@var bool*/
	protected $gotHealEffect = false;
	
	protected $foundTreasureTimestamp = 0;
    
    protected $isInDeathmatch = false;
	
	protected $needParticles = true;
    protected $particleHotbar = [];
        
	protected $lastMove = -1;
	
	protected $pet = null;
	protected $petType = "";
	protected $petEnable = true;
	protected $petState;
    protected $muteTime = 0;
	
	protected $savePassword = "";
	/** @var string|null - used for pet messages*/
	protected $lastDamager = null;
	/** @var string|null */
	protected $joinedGameLast = null;
	/** @var bool */
	public $isPetChanging = false;
	/** @var string */
	public $wishPet = "";
	/** @var string */
	protected $lastCameInLobby = null;
	
	protected $particleEffectId = 0;
	
	protected $wonLastMacth = false;

	public function __construct(SourceInterface $interface, $clientID, $ip, $port) {
		parent::__construct($interface, $clientID, $ip, $port);
	}

	/**
	 * 
	 * @param string $password
	 * @throws WrongPassException
	 */
	public function login(string $password) {
		if ($this->isRegistered()) {
			Server::getInstance()->getScheduler()->scheduleAsyncTask(
				new AuthRequest($this->getName(), $this->getID(), $this->isNeedsNewPassHash, $this->bcryptPassHash, $this->passHash, $password)
			);
//			if ($this->checkPass($password)) {
//				$this->isAuthenticated = true;
//				$this->updateDisplayedName();
//			} else {
//				throw new WrongPassException($this);
//			}
		}
	}

	/*
	 * Changed pass hash and switched chat mode to normal.
	 * Logic of chat's mode switching placed here because all modes related with password change
	 *
	 * @param string $passHash
	 */
	public function setPassHash(string $passHash, bool $isBCryptHash = false) {
		if (!$isBCryptHash) {
			$this->passHash = $passHash;
		} else {
			$this->bcryptPassHash = $passHash;
		}

		if ($this->chatMode !== self::CHAT_MODE_NORMAL) {
			$this->changeChatModeToNormal();
		}
	}

	/**
	 * @param string $password
	 * @return bool
	 */
	public function checkPass($password) {
		if(!empty($this->savePassword) && $this->savePassword == $password) {
			return true;
		}
		return false;
//		if ($this->isNeedsNewPassHash === false) {
//			return password_verify($password, $this->bcryptPassHash);
//		} else {
//			return hash('md5', $password) === $this->passHash;
//		}
	}

	public function getPassHash() {
		return $this->passHash;
	}

	/**
	 * @return bool
	 */
	public function isAuthorized() {
		return $this->isRegistered && $this->isAuthenticated;
	}

	/**
	 * @param bool $isReg
	 */
	public function setAsRegistered(bool $isReg = true) {
		$this->isRegistered = $isReg;
	}

	public function isRegistered() {
		return $this->isRegistered;
	}

	public function isMuted() {
		return $this->isMuted;
	}

	/**
	 * @return bool
	 */
	public function isVip() {
		return $this->isVipEnabled && $this->vipStatus;
	}

	/**
	 * @param bool $value
	 */
	public function vipEnabled(bool $value = true) {
		$this->isVipEnabled = $value;
	}

	public function isShownRanksInPrefix() {
		return $this->showRanksInPrefix;
	}

	/**
	 * @param bool $status
	 */
	public function updateRanksInPrefixStatus(bool $status = true) {
		$this->showRanksInPrefix = $status;
	}

	/**
	 * Create name prefix if player is vip and if he has special ranks
	 * 
	 * @param string $ranks
	 */
	public function initNamePrefix(string $ranks) {
		$this->showRanksInPrefix = true;

		if ($this->isVip()) {
			switch ($this->vipStatus) {
				case self::VIP_PLUS :
					$this->namePrefix = TextFormat::DARK_GRAY . "[" . TextFormat::GOLD . "VIP" . TextFormat::GREEN . "+" . TextFormat::DARK_GRAY . "] " . TextFormat::WHITE;
					break;

				case self::VIP :
					$this->namePrefix = TextFormat::DARK_GRAY . "[" . TextFormat::GOLD . "VIP" . TextFormat::DARK_GRAY . "] " . TextFormat::WHITE;
					break;

				default:
					echo "UNKNOWN VIP STATUS" . PHP_EOL;
					break;
			}
		}

		if ($ranks) {
			$this->namePrefixWithRanks = $this->namePrefix . TextFormat::DARK_GRAY . "[" . TextFormat::GRAY . $ranks . TextFormat::DARK_GRAY . "] " . TextFormat::WHITE;
		} else {
			$this->namePrefixWithRanks = $this->namePrefix;
		}
	}

	/**
	 * Collect name with prefixes and colors if isset
	 * 
	 * @param string $nameColor - must be a const from TextFormat
	 * @return string
	 */
	public function getDisplayedName($nameColor = TextFormat::WHITE) {
		if ($this->showRanksInPrefix) {
			$prefix = $this->namePrefixWithRanks;
		} else {
			$prefix = $this->namePrefix;
		}

		if (!$this->isVipEnabled) {
			$prefix = substr($prefix, strlen($this->namePrefix));
		}

		return $prefix . $nameColor . $this->getName();
	}

	/**
	 * Change color of player name
	 * 
	 * @param string $nameColor - must be a constant from TextFormat
	 */
	public function updateDisplayedName($nameColor = self::DEFAULT_NAME_COLOR) {
		if ($this->isVip() && $nameColor === self::DEFAULT_NAME_COLOR) {
			$nameColor = TextFormat::AQUA;
		}
		$displayedName = $this->getDisplayedName($nameColor);
		$this->setDisplayName($displayedName);
		$this->setNameTag($displayedName);
	}

	/**
	 * Lock player by reason
	 * 
	 * @param string $reason
	 */
	public function lock(string $reason) {
		$this->isLocked = true;
		$this->lockReason = $reason;
	}

	/**
	 * Unlock player, clear reason
	 */
	public function unlock() {
		$this->isLocked = false;
		$this->lockReason = '';
	}

	/**
	 * Get answer to: why am I locked??
	 * @return string
	 */
	public function getLockReason() {
		return $this->lockReason;
	}

	public function isLocked() {
		return $this->isLocked;
	}

	/**
	 * Mute / unmute player chat
	 * 
	 * @param bool $value
	 */
	public function setMuteValue(bool $value = true) {
		$this->isMuted = $value;
	}

	/**
	 * Save player in ignore list by his name
	 * 
	 * @param string $playerName
	 */
	public function ignorePlayer(string $playerName) {
		$this->ignoreList[$playerName] = true;
	}

	/**
	 * Remove player from ignore list by his name
	 * 
	 * @param string $playerName
	 * @return boolean
	 */
	public function unignorePlayer(string $playerName) {
		if (isset($this->ignoreList[$playerName])) {
			unset($this->ignoreList[$playerName]);
			return true;
		}
		return false;
	}

	public function isIgnorePlayer(string $playerName) {
		return isset($this->ignoreList[$playerName]);
	}

	public function getChatMode() {
		return $this->chatMode;
	}
	
	public function isNeedNewPass() {
		return $this->isNeedsNewPassHash;
	}
	
	public function setNeedNewPass(bool $value) {
		$this->isNeedsNewPassHash = $value;
	}

	/**
	 * Set default chat mode
	 */
	public function changeChatModeToNormal() {
		$this->chatMode = self::CHAT_MODE_NORMAL;
		$this->passChangeData = array();
		$this->registrationData = array();
	}

	/**
	 * Hide player's messages to safe changing password,
	 * prepare passChangeData
	 */
	public function changeChatModeToPassChange($isForceChange = false) {
		if ($this->chatMode !== self::CHAT_MODE_NORMAL) {
			$this->changeChatModeToNormal();
		}
		
		if ($isForceChange === true) {
			$this->sendLocalizedMessage('MUST_UPDATE_PASS');
		}

		$this->chatMode = self::CHAT_MODE_PWD_CHANGE;
		$this->passChangeData = array(
			'currentPass' => '',
			'newPass' => '',
			'newPassBCrypt' => '',
			'newPassConf' => '',
			'newPassConfBCrypt' => '',
		);
	}

	/**
	 * Hide player's messages to safe register,
	 * prepare registration data
	 */
	public function changeChatModeToRegistration() {
		if ($this->isRegistered()) {			
			$this->sendLocalizedMessage("REGISTER_ALREADY_REGISTERED");
			return;
		}

		if ($this->chatMode !== self::CHAT_MODE_NORMAL) {
			$this->changeChatModeToNormal();
		}

		$this->chatMode = self::CHAT_MODE_REGISTRATION;
		$this->registrationData = array(
			'password' => '',
			'email' => '',
		);
		$this->sendLocalizedMessage("REGISTER_REGISTER_PASSWORD", array($this->getName()));
	}
	
//	protected function getStringHash(string $somePass) {
//		$passHash = '';
//		if (empty($this->bcryptPassHash)) {
//			$passHash = hash('md5', $somePass);
//		} else {
//			$passHash = password_hash($somePass, PASSWORD_BCRYPT);
//		}
//		
//		return $passHash;
//	}

	/**
	 * Change password to player after some security checks
	 * 
	 * @param string $somePass
	 */
	public function changePass($somePass) {
		if ($this->chatMode !== self::CHAT_MODE_PWD_CHANGE) {
			return;
		}
		// checking curent pass confirmation
		if (empty($this->passChangeData['currentPass'])) {
			if ($this->checkPass($somePass)) {
				$this->passChangeData['currentPass'] = $somePass;//$this->getStringHash($somePass);
				$this->sendLocalizedMessage("NEW_PASS", array(), Translate::PREFIX_PLAYER_ACTION);
			} else {
				$this->sendLocalizedMessage("INCORRECT_PASSWORD", array(), Translate::PREFIX_PLAYER_ACTION);
			}
			return;
		}
		// obtain candidate in new passwords and checking it
		if (empty($this->passChangeData['newPass'])) {
			if (strlen($somePass) > 3) {
				if($this->passChangeData['currentPass'] == $somePass){
					$this->sendLocalizedMessage("SAME_PASS");
					return;
				}
				$this->passChangeData['newPass'] = $somePass;//$this->getStringHash($somePass);
				if (empty($this->bcryptPassHash)) {
					$this->passChangeData['newPassBCrypt'] = $somePass;//password_hash($somePass, PASSWORD_BCRYPT);
				}
				$this->sendLocalizedMessage("CONFIRM_PASS", array(), Translate::PREFIX_PLAYER_ACTION);
			} else {
				$this->sendLocalizedMessage("SHORT_PASS", array(), Translate::PREFIX_PLAYER_ACTION);
			}
			return;
		}
		//Check for password length and matching
		if (empty($this->passChangeData['newPassConf'])) {
			if (strlen($somePass) <= 3) {
				$this->sendLocalizedMessage("SHORT_PASS", array(), Translate::PREFIX_PLAYER_ACTION);
				return;
			}
			//if ((empty($this->bcryptPassHash) && !password_verify($somePass, $this->passChangeData['newPassBCrypt'])) || (!empty($this->bcryptPassHash) && !password_verify($somePass, $this->passChangeData['newPass']))) {
			if ((empty($this->bcryptPassHash) && $somePass != $this->passChangeData['newPassBCrypt']) || (!empty($this->bcryptPassHash) && $somePass != $this->passChangeData['newPass'])) {
				$this->sendLocalizedMessage("PASS_NOT_MATCH", array(), Translate::PREFIX_PLAYER_ACTION);
				return;
			}
			$this->passChangeData['newPassConf'] = $somePass;//$this->getStringHash($somePass);
			if (empty($this->bcryptPassHash)) {
				$task = new ChangePassword($this->bcryptPassHash, $this->passChangeData['newPassBCrypt'], $this->getName(), $this->passChangeData['currentPass'], $this->passChangeData['newPass']);
			} else {
				$task = new ChangePassword($this->bcryptPassHash, $this->passChangeData['newPassConf'], $this->getName(), '', '');
			}
			Server::getInstance()->getScheduler()->scheduleAsyncTask($task);
			return;
		}
	}

	/**
	 * Registration process
	 * 
	 * @param string $someRegData
	 */
	public function registration($someRegData) {
		if ($this->chatMode === self::CHAT_MODE_REGISTRATION) {
			//check for valid password
			if (!$this->registrationData['password']) {
				$password = $someRegData;
				if (strlen($password) > 3) {
					$this->registrationData['password'] = $password;
					$this->sendLocalizedMessage("FINISH_REGISTRATION", array($this->getName(), $password));
					return;
				} else {
					$this->sendLocalizedMessage("SHORT_PASS", array($this->getName(), $password));
					return;
				}
			}
			//check for valid email
			if (!$this->registrationData['email']) {
				$email = $someRegData;
				if (strlen($email) > 5 && stristr($email, '@') && stristr($email, '.')) {
					$this->registrationData['email'] = $email;
					Server::getInstance()->getScheduler()->scheduleAsyncTask(
							new RegisterRequest($this->getName(), $email, $this->registrationData['password'])
					);
					return;
				} else {
					$this->sendLocalizedMessage("INVALID_EMAIL", array(), Translate::PREFIX_PLAYER_ACTION);
					return;
				}
			}
		}
	}

	/*
	 * Methods for kits component
	 */
	public function haveKit($kitId) {
//		return in_array(strtolower($kitName), $this->kits);
		return $kitId == $this->currentKit;
	}

	public function addKit($kitId) {
		if ($this->haveKit($kitId)) {
			throw new KitDuplicationException($this, $kitId);
		}
//		$this->kits[] = strtolower($kitName);
		$this->currentKit = $kitId;
	}

	public function setKitAdditionalData(string $name, $value) {
		if (isset($this->kitsAdditionalData[$name]) &&
				is_array($this->kitsAdditionalData[$name]) &&
				!is_array($value)) {

			$this->kitsAdditionalData[$name][] = $value;
		} else {
			$this->kitsAdditionalData[$name] = $value;
		}
	}

	public function getKitAdditionalData(string $name) {
		if (isset($this->kitsAdditionalData[$name])) {
			return $this->kitsAdditionalData[$name];
		} else {
			throw new KitAddDataException($this);
		}
	}
	
	public function unsetKitAdditionalData() {
		$this->kitsAdditionalData = array();
	}

	public function getKits() {
//		return $this->kits;
		return $this->currentKit;
	}

	/*
	 * 	Invincible logic
	 */
	public function setInvincible(bool $value = true) {
		$this->isInvincible = $value;
	}

	public function isInvincible() {
		return $this->isInvincible;
	}

	/*
	 * Alert and kits system logic 
	 */
	public function getState() {
		return $this->stateForAlertSystem;
	}

	public function setStateInLobby() {
		$this->getInventory()->sendContents($this);
		$this->getInventory()->sendArmorContents($this);
		$this->stateForAlertSystem = self::IN_LOBBY;
		$this->currentArenaId = self::NOT_IN_ARENA;
		if (Kit::isEnable()) {
			Kit::deactivateKitsForPlayer($this);
		}
		if($this->needParticles){
			$particle = [
				0 => Item::get(Item::BUCKET, 0, 1),
				1 => Item::get(Item::REDSTONE, 0, 1),
				2 => Item::get(120, 0, 1),
				3 => Item::get(378, 0, 1)
			];
			$this->getInventory()->setItem(23, $particle[0]);
			$this->getInventory()->setItem(24, $particle[1]);
			$this->getInventory()->setItem(25, $particle[2]);
			$this->getInventory()->setItem(26, $particle[3]);
			$i = count($particle) - 1;
			foreach($this->particleHotbar as $key => $slot){
				$this->getInventory()->setHotbarSlotIndex($slot, 26 - $i);
				$i--;
			}
			$this->getInventory()->sendContents($this);
		}
		if ($this->isVip() && $this->petEnable && !isset($this->pet)){
			$this->lastCameInLobby = date('Y-m-d H:i:s');
			$this->setPetState('create', '', 5);
		} else {
			$this->wonLastMacth = false;
		}
	}
	
	public function createPet() {
		if ($this->stateForAlertSystem == self::IN_LOBBY && $this->petEnable && !isset($this->pet)) {
			PetsManager::createPet($this, $this->petType);
			Pets::sendPetMessage($this, Pets::OWNER_IS_BACK);
		}
	}

	public function setStateCountdown($arenaId) {
		$this->stateForAlertSystem = self::IN_COUNTDOWN;
		$this->currentArenaId = $arenaId;
        if($this->needParticles){
			$this->getInventory()->remove(Item::get(Item::BUCKET));
			$this->getInventory()->remove(Item::get(Item::REDSTONE));
			$this->getInventory()->remove(Item::get(120));
			$this->getInventory()->remove(Item::get(378));
			$this->getInventory()->sendContents($this);
		}
		if (isset($this->pet) && $this->pet instanceof Pets) {
			$this->pet->close();
			$this->pet = null;
			$this->joinedGameLast = date('Y-m-d H:i:s');
		}
	}

	public function setStateInGame($arenaId = null) {
		$this->stateForAlertSystem = self::IN_GAME;
		if (Kit::isEnable()) {
			//logic to give kits
			try {
				Kit::activateKitsForPlayer($this, $this->currentKit);
			} catch (KitBaseException $e) {
				Server::getInstance()->getLogger()->warning($e->getMessage());
				return;
			}
		}
		if($this->needParticles){
			$this->particleEffectId = 0;
			$this->getInventory()->remove(Item::get(Item::BUCKET));
			$this->getInventory()->remove(Item::get(Item::REDSTONE));
			$this->getInventory()->remove(Item::get(120));
			$this->getInventory()->remove(Item::get(378));
			$this->getInventory()->sendContents($this);
		}
		if (isset($this->pet) && $this->pet instanceof Pets) {
			$this->pet->close();
			$this->pet = null;
			$this->joinedGameLast = date('Y-m-d H:i:s');
		}
	}

	public function getCurrentArenaId() {
		return $this->currentArenaId;
	}

	/**
	 * @param string $message
	 */
	public function sendMessage($message) {
		if (!$this->isMuted()) {
			if (is_array($message)) {
				$message = implode("\n", $message);
			}
			parent::sendMessage($message);
		}
	}

	/*
	 * Send message to player ignoring mute state
	 */
	public function sendImportantMessage($message) {
		parent::sendMessage($message);
	}
	
	/**
	 * Translation logic
	 */
	
	/**
	 * Send message to player on his current language
	 * 
	 * @param string $message
	 * @param array $args
	 * @param string $prefix
	 * @param string $suffix
	 */
	public function sendLocalizedMessage(
			string $message, 
			array $args = [], 
			string $prefix = TextFormat::GRAY, 
			string $suffix = "") {
		if (!$this->isMuted()) {
			Translate::getInstance()->sendLocalizedMessage($this, $message, $args, $prefix, $suffix);
		}
	}
	
	/**
	 * Show popup to player based on his language
	 * 
	 * @param string $message
	 * @param array $args
	 * @param string $prefix
	 * @param string $suffix
	 */
	public function sendLocalizedPopup(
			string $message, 
			array $args = [], 
			string $prefix = TextFormat::GRAY, 
			string $suffix = "") {
		Translate::getInstance()->sendLocalizedPopup($this, $message, $args, $prefix, $suffix);
	}
	
	/**
	 * Create translated string (for specific plugins)
	 * 
	 * @param string $string
	 * @param string $prefix
	 * @param array $args
	 * @param string $suffix
	 * @return string
	 */
	public function getTranslatedString(
			string $string, 
			string $prefix = TextFormat::GRAY, 
			array $args = array(), 
			string $suffix = ""){
		return Translate::getInstance()->getTranslatedString($this->language, $string, $prefix, $args, $suffix);
	}

	
	public function attack($damage, EntityDamageEvent $source){
		if($this->isInvincible() || $this->isInvoulnerable()){
			return;
		}
		parent::attack($damage, $source);
	}
	
	/**
	 * VIP Lounge logic
	 */	
	public function isPushed() {
		return $this->pushedByLoungeGuard;
	}
	
	public function updatePushStatus(bool $value) {
		$this->pushedByLoungeGuard = $value;
	}
	
	public function isHealed() {
		return $this->gotHealEffect;
	}

	/**
	 * Save plsyer as healed, cause only once in session allowed
	 * 
	 * @param bool $value
	 */
	public function setAsHealed($value = true) {
		$this->gotHealEffect = $value;
	}
	
	/**
	 * Spectators logic
	 */
	public function showToAll(){
		foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
			if (!$onlinePlayer->canSee($this)) {
				$onlinePlayer->showPlayer($this);
			}
		}
	}
	
	public function hideFromAll(){
		foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
			if ($onlinePlayer->canSee($this)) {
				$onlinePlayer->hidePlayer($this);
			}
		}
	}
	
	/**
	 * Set item to player's inventory, give it to hand,
	 * show it inside inventory immediately
	 * 
	 * @param int $index
	 * @param Item $item
	 */
	public function setHotbarItem($index, Item $item) {
		$inventory = $this->getInventory();
		$inventory->clear($index);
		$inventory->setItem($index, $item);
		$inventory->setHotbarSlotIndex($index, $index);
		$inventory->sendContents($this);
	}
	
	// Treasure hunt part
	
	public function setFindTreasure() {
		$this->foundTreasureTimestamp = time();
	}

	// use it for align multiline text on center
	private function alignStringCenter(string $string) {
		$strlen = function_exists("mb_strlen") ? "mb_strlen" : "strlen";
		$lines = explode("\n", $string);
		$maxLength = max(array_map($strlen, $lines));
		foreach($lines as &$line) {
		  $line = str_pad($line, $maxLength, " ", STR_PAD_BOTH);
		}
		return implode("\n", $lines);
	}
	
	public function sendPopup($message) {
		if ($this->foundTreasureTimestamp !== 0) {
			$currentTime = time();
			$timeDiff = $currentTime - $this->foundTreasureTimestamp;
			if ($timeDiff < self::TH_SHOW_MESSAGE_DURATION) {
				$message = self::FOUND_TREASURE;
			}
			
		}
		parent::sendPopup($message);
	}
    
    public function isInDeathmatch(){
        return $this->isInDeathmatch;
    }
    
    public function setInDeathmatch($inDeathmatch){
		if ($inDeathmatch) {
			if (Kit::isEnable() && $this->hasEffect(Effect::JUMP)) {
				$this->removeEffect(Effect::JUMP);
			}
		}
		$this->isInDeathmatch = $inDeathmatch;
    }
	
	public function isInvisible() {
		return $this->isInvisible;
	}
	
	public function setInvisible(bool $invisible = true) {
		$this->isInvisible = $invisible;
	}
	
	public function spawnTo(Player $player) {
		if (!$this->isInvisible()) {
			parent::spawnTo($player);
		}
	}
	
	public function isInvoulnerable() {
		return $this->isInvoulnerable;
	}
	
	public function setInvoulnerable(bool $invoulnerable = true) {
		$this->isInvoulnerable = $invoulnerable;
	}
	
	public function getLastMove(){
		return $this->lastMove;
	}
	
	public function setLastMove($lastMove){
		$this->lastMove = $lastMove;
	}
	
	public function addPet($pet) {
		$this->pet = $pet;
		$this->petType = $pet->getName();
	}

	public function getPet() {
		return isset($this->pet) ? $this->pet : null;
	}

	public function togglePetEnable() {
		if ($this->stateForAlertSystem == self::IN_LOBBY && !$this->isPetChanging) {
			if (isset($this->pet)) {
				$this->pet->close();
				$this->pet = null;
				$this->isPetChanging = true;
			} else {
				$this->enablePet($this);
			}
		}
	}
	
	public function hidePet() {
		$this->petEnable = false;
		if (isset($this->pet)) {
			$this->pet->close();
			$this->pet = null;
			//send random bye message from pet
			Pets::sendPetMessage($this, Pets::PET_IS_GONE);
		}
	}
	
	public function showPet($type = "") {
		if ($this->stateForAlertSystem == self::IN_LOBBY && !$this->isPetChanging) {
			$this->wishPet = !empty($type) ? $type : $this->petType;
			if (isset($this->pet)) {
				$this->pet->close();
				$this->pet = null;
				$this->isPetChanging = true;
			} else {
				$this->enablePet($this, $this->wishPet);
			}
		}
	}
	
	/**
	 * Enable pet depending on params - have or not wishPet, 
	 * also: toggle or the same pet we need
	 * 
	 * @param LbPlayer $player
	 * @param string $wishPet
	 */
	public function enablePet($player, $wishPet = "") {
		$player->petEnable = true;
		if ($this->stateForAlertSystem == self::IN_LOBBY) {			
			$type = "";
			$holdType = "";
			if (empty($wishPet)) {
				$holdType = $this->petType;
			} else {
				$type = $this->wishPet;
				$this->wishPet = "";
			}
			PetsManager::createPet($player, $type, $holdType);			
			Pets::sendPetMessage($player, Pets::PET_SUMMONING);
		}
	}
	
	public function isChatBanned(){
		if($this->getMuteTime() >= time()){
			$remain = $this->getMuteTime() - time();
			$time = "";
			if($remain >= 3600){
				$hours = floor($remain / 3600);
				$minutes = floor(($remain - ($hours * 3600)) / 60);
				$time = $hours."h ".$minutes."min";
			}elseif($remain >= 60){
				$minutes = floor($remain / 60);
				$time = $minutes."min";
			}else{
				$time = "1min";
			}
			$this->sendImportantMessage(TextFormat::GRAY."You're banned from chatting for ".$time);
			return true;
		}else{
			return false;
		}
	}

	/* saved lastDamager name for pets messages*/	
	public function getLastDamager() {
		return $this->lastDamager;
	}
	
	public function setLastDamager($name = null) {
		$this->lastDamager = $name;
	}

	public function getJoinGameLastTime() {
		return $this->joinedGameLast;
	}
	
	public function getLobbyTime() {
		return $this->lastCameInLobby;
	}
	
	public function setLobbyTime($value = null) {
		$this->lastCameInLobby = $value;
	}

	public function getMuteTime(){
        return $this->muteTime;
    }
    
    public function setMuteTime($muteTime){
        $this->muteTime = $muteTime;
    }
	
	public function setAuthenticated($val) {
		$this->isAuthenticated = $val;
	}

	public function savePassword($password) {
		$this->savePassword = $password;
	}
	
	public function getAirTick() {
		return $this->inAirTicks;
	}
	
	public function setPetState($state, $petType = "", $delay = 2) {
		$this->petState = array(
			'state' => $state,
			'petType' => $petType,
			'delay' => $delay
		);
	}
	
	public function getPetState(){
		if(isset($this->petState['state'])) {
			if($this->petState['delay'] > 0){
				$this->petState['delay']--;
				return false;
			}
			return $this->petState;
		}
		return false;
	}
	
	public function clearPetState(){
		unset($this->petState);
	}	

	public function setParticleEffectId($id){
		$this->particleEffectId = $id;
	}
	
	public function getParticleEffectId(){
		return $this->particleEffectId;
	}
	
	public function setWonLastMacth($val) {
		$this->wonLastMacth = $val;
	}
	
	public function getWonLastMacth() {
		return $this->wonLastMacth;
	}

}
