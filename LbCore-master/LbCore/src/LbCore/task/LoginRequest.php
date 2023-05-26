<?php
namespace LbCore\task;

use LbCore\data\PluginData;
use LbCore\language\Translate;
use LbCore\player\LbPlayer;
use LbCore\task\LbAsyncTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;

/**
 * Get data for player when he is logged in
 */
class LoginRequest extends LbAsyncTask {
	const UBER_VIP_ID = '1';

	/**@var int*/
	protected $playerId;
	/**@var string*/
    protected $playerName;
	/**@var bool*/
    protected $registered = false;
	/**@var string*/
    protected $playerPassHash = "";
	/** @var string */
	protected $playerBCryptPassHash = '';
	/**@var bool*/
    protected $isPlayerLocked = false;
	/**@var bool*/
	protected $isVipEnabled = true;
	/**@var int*/
    protected $coins = 0;
	/**@var int*/
    protected $kills = 0;
	/**@var int*/
    protected $deaths = 0;
	/**@var string*/
    protected $lockReason = "";
	/**@var string*/
    protected $productsBought = "";
	/**@var bool*/
	protected $isAchievedPlayerData = false;
	/**@var bool*/
    protected $databaseError = false;
	/**@var string*/
	protected $rankStr = "";	
	/**@var bool*/
    protected $hackingFlag = false;

	/**
	 * @param string $playerName
	 * @param int $playerId
	 */
    public function __construct($playerName, $playerId) {
        $this->playerName = $playerName;
        $this->playerId = $playerId;
    }

	/**
	 * Send request for player saved data
	 */
    public function onRun() {
		$url = 'http://data.lbsg.net/apiv3/database.php';
		$playerName = str_replace('_','\_',$this->playerName);
		$dbQuery = 'SELECT coins,productsBought,login.username,hash,isLocked,lockReason,kills_total,deaths_total,vipenabled,p_hash,ranks.rank,hacking_flag '
			. 'FROM login LEFT JOIN ranks ON (login.username = ranks.username)'
			. 'WHERE login.username = \'' . $this->playerName . '\'';
		$postParam = array(
            'auth' => self::AUTH_STRING,
            'return' => true,
            'cmd' => $dbQuery,
        );

		// curl timeout
        $result = Utils::postURL($url, $postParam, 10);
		
		if (!stristr($result, "fail") && $result !== false) {
			$playerRawData = json_decode($result, true);
			
			$this->playerName = $playerRawData['username'];
			$this->playerPassHash = $playerRawData['hash'];
			$this->playerBCryptPassHash = $playerRawData['p_hash'];
			$this->isPlayerLocked = $playerRawData['isLocked'];
			$this->lockReason = $playerRawData['lockReason'];
			$this->productsBought = $playerRawData['productsBought'];
			$this->isVipEnabled = (bool)$playerRawData['vipenabled'];
			// some statistics
			$this->coins = $playerRawData['coins'];
			$this->kills = $playerRawData['kills_total'];
			$this->deaths = $playerRawData['deaths_total'];
			$this->rankStr = !empty($playerRawData['rank']) ? str_replace("&", "§", $playerRawData['rank']) : "";
			$this->isAchievedPlayerData = true;
			$this->hackingFlag = (bool)$playerRawData['hacking_flag'];
		} else {
			if (!stristr($result, "username")) {
				$this->databaseError = true;
			}
		}
    }

    /**
	 * Prepare options by received data
	 * 
     * @param Server $server
     * @return bool
     */
    public function onCompletion(Server $server) {
		$player = $server->getPlayer($this->playerName);
        $plugin = $server->getPluginManager()->getPlugin("LbCore");
		
        if ($player instanceof LbPlayer) {
			if ($this->databaseError) {
				$player->sendLocalizedMessage('LOGIN_DB_ERROR');
			} else {
				// if player registered
				if ($this->isAchievedPlayerData) {
					// set player language
					$player->language = $this->getLanguageByAddress($plugin->reader, $player->getAddress());
					$player->countryIsoCode = $this->getCountryIsoCodeByIP($plugin->reader, $player->getAddress());

					if ($this->isPlayerLocked) {
						$player->sendLocalizedMessage("ACCOUNT_LOCKED", array(), TextFormat::RED."// ", TextFormat::RED." //");
						$player->lock($this->lockReason);
						$player->sendMessage($this->lockReason);
					} else {
						// send greeting
						$player->sendMessage(TextFormat::BOLD.TextFormat::DARK_GRAY.".");
						$player->sendLocalizedMessage("WELCOME_MESSAGE_REGISTERED");
						$player->sendPopup(TextFormat::GREEN."Welcome! ".TextFormat::GRAY."Please login.");
						$player->sendMessage(TextFormat::BOLD.TextFormat::DARK_GRAY.".");

						$player->setAsRegistered();
						$player->setPassHash($this->playerPassHash);
						if (is_null($this->playerBCryptPassHash)) {
							$player->setNeedNewPass(true);
						} else {
							$player->setPassHash($this->playerBCryptPassHash, true);
						}

						// set statistics
						$player->coinsNum = $this->coins;
						$player->killsNum = $this->kills;
						$player->deathNum = $this->deaths;
						$player->hackingFlag = $this->hackingFlag;

						$this->setPlayerVipStatus($server, $player);

						$rank_str = $this->rankStr;
						
						$player->initNamePrefix($rank_str);
						
						$player->vipEnabled(false);
						$player->updateRanksInPrefixStatus(false);

						$player->updateDisplayedName();
					}
				} else {
					$player->sendMessage(TextFormat::BOLD.TextFormat::DARK_GRAY.".");
					$player->sendLocalizedMessage("WELCOME_MESSAGE_UNREGISTERED");
					$player->sendPopup(TextFormat::GREEN."Welcome ".TextFormat::GRAY."to ".TextFormat::AQUA."Life".TextFormat::RED."Boat ".TextFormat::GOLD."Survival Games".TextFormat::GRAY."!");
					$player->sendMessage(TextFormat::BOLD.TextFormat::DARK_GRAY.".");
				}
            }
        }
    }

	/**
	 * Set language by player's country
	 * 
	 * @param $dbReader
	 * @param $address
	 * @return string
	 */
	private function getLanguageByAddress($dbReader, $address) {
		$defaultLanguage = 'English';
		$language = $defaultLanguage;

		$spanishCountries = array("Mexico","Chile","Argentina","Spain","Panama","Bolivia","Colombia","Costa Rica","Cuba","Dominican Republic","Ecuador","El Salvador","Guatemala","Honduras","Peru","Nicaragua","Mexico","Chile","Argentina","Spain","Panama","Bolivia","Colombia","Costa Rica","Cuba","Dominican Republic","Ecuador","El Salvador","Guatemala","Honduras","Peru","Nicaragua","Uruguay","Venezuela");
		$dutchCountries = array("Netherlands","Belgium");
		$germanCountries = array("Germany","Austria","Switzerland","Luxembourg","Liechtenstein");

		try {
			var_dump($dbReader->country($address)->country->isoCode);
			$country = $dbReader->country($address)->country->names['en'];
			if(in_array($country, $spanishCountries)) {
				$language = 'Spanish';
			}
			if(in_array($country, $dutchCountries)) {
				$language = 'Dutch';
			}
			if(in_array($country, $germanCountries)) {
				$language = 'German';
			}
		} catch (\GeoIp2\Exception\AddressNotFoundException $e) {
			return $defaultLanguage;
		}

		return $language;
	}
	
	private function getCountryIsoCodeByIP($dbReader, $ip) {
		$isoCode = '';
		
		try {
			$isoCode = $dbReader->country($ip)->country->isoCode;
		} catch (\GeoIp2\Exception\AddressNotFoundException $e) {
			echo "EXCEPTION : ".$e->getMessage();
		} finally {
			return $isoCode;
		}
	}

	/**
	 * Set player status as vip or vip+
	 * Also make lottery for non-vip players
	 * 
	 * @param Server $server
	 * @param Player $player
	 */
	private function setPlayerVipStatus($server, $player) {
		$vipIdForCurrentGametype = PluginData::getVipIds();
		if (!$vipIdForCurrentGametype) {
			return;
		}

		$vipId = (string) $vipIdForCurrentGametype[0];
		$vipPlusId = (string) $vipIdForCurrentGametype[1];

		if($this->productsBought != "") {
			$purchasedProducts = explode(',', $this->productsBought);
			if (in_array(self::UBER_VIP_ID, $purchasedProducts) ||
				in_array($vipPlusId, $purchasedProducts)) {

				$player->vipStatus = LbPlayer::VIP_PLUS;
			} elseif (in_array($vipId, $purchasedProducts)) {
				$player->vipStatus = LbPlayer::VIP;
			}
		}

		
		// give VipPlus status to player with 1/12 chance
		if ($player->isRegistered() && !$player->isVip()) {
			if (rand(1, 12) === 1) {
				$player->vipStatus = LbPlayer::VIP_PLUS;
				$player->sendLocalizedMessage("VIP_GIVAWAY_WINNER");
			}
		}
		//if vip disabled
		if ($player->vipStatus && !$this->isVipEnabled) {
			$player->vipEnabled(false);
			$player->sendLocalizedMessage("VIP_DISABLED");
			$player->sendLocalizedMessage("VIP_USAGE");
		}
	}
}
