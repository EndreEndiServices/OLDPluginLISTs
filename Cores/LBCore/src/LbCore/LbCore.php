<?php
namespace LbCore;

use GeoIp2\Database\Reader;
use Logger\Logger;
use AntiHack\AntiHack;
use VipLounge\VIPLounge;
use Alert\Alert;
use Kits\Kit;
use LbCore\event\PlayerAuthEvent;
use Lbcore\language\Translate;
use LbCore\player\LbPlayer;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use Particle\ParticleManager;

/**
 * Base plugin class, contains:
 * onEnable method (calls events and tasks)
 * onCommand (to use in-game commands)
 * onDisable
 * onSuccessfulLogin
 *
 */
class LbCore extends PluginBase {
	/**@var LbCore*/
	private static $instance = null;
	/**@var bool*/
	private $testing = false;
	/**@var EventListener*/
	protected $listener;
	/**@var string*/
	protected $ip;
	/**@var string*/
	protected $domainName;
	/**@var int*/
	public $playerCount = null;	
	/* @var ChatFilter chat filter */
	public $filter;
	// geoip2
	public $reader;
	/** @var  LbCommand $commands */
	public $commands;
	/** @var ParticleManager */
	public $particleManager;
	/* Components */
	public $alerts;
	/* Moderators names */
	public static $lbsgStaffNames = array(
		'fuduin12sh', 'ipadbeetsoft', 'rteder', 'hydreon','dishrex', 'ben_hydreon', 'potpotsie', 
		'alexbeetsoft', 'tomato777777', 'loffman117', 'craftyourbukkit',
		'ramennoodles', 'Truffledor'
	);
	
	/**
     * Called whenever player types in command using /{command}. Overrides method in PluginBase.
     *
     * @param CommandSender $sender
     * @param Command $command
     * @param string $label
     * @param array $args
     * @return bool
     * @see pocketmine\plugin\PluginBase->onCommand()
     */
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) : bool {
		$commandName = $command->getName();
		$params = array('sender' => $sender, 'args' => $args);
		$this->commands->$commandName($params);
		return true;
	}

	/**
	 * Run when plugin is enabled.
	 *
	 * @see \pocketmine\plugin\PluginBase::onEnable()
	 */
	public function onEnable() {
		$this->getLogger()->info("Starting Lifeboat Core...");
		
		self::$instance = $this;
		
		if ($this->testing) {
			$this->listener = new EventListener($this);
			$this->getServer()->getPluginManager()->registerEvents($this->listener, $this);
			
			//enable vip lounge
			try {
				VIPLounge::enable($this);
//				VIPLounge::getInstance()->initLoungeGuards();
			} catch (Exception $e) {
				echo 'EXCEPTION: Problem with starting VIPLounge'.PHP_EOL;
				echo 'EXCEPTION: '.$e->getMessage().PHP_EOL;
			}
		}

		try {
			Logger::enable($this);
			Logger::getInstance()->write(str_pad('SERVER START', 85, '-', STR_PAD_BOTH), true);
		} catch (Exception $e) {
			echo 'EXCEPTION: Problem with starting Logger'.PHP_EOL;
			echo 'EXCEPTION: '.$e->getMessage().PHP_EOL;
		}
		
		AntiHack::enable($this);
		//Kit::enable($this);
		//$this->getServer()->getPluginManager()->registerEvents(new \Pets\PetsManager(), $this);
//		$this->getServer()->getScheduler()->scheduleRepeatingTask(new \Pets\task\PetsTick($this), 20*60);//run each minute
		$this->alerts = new Alert($this);
		
		$this->filter = new ChatFilter($this->getLogger());
		$this->reader = new Reader("GeoIP2-Country.mmdb");
		$this->commands = new LbCommand($this);
		$this->particleManager = new ParticleManager();

		$this->getServer()->getScheduler()->scheduleRepeatingTask(new task\TournamentTick($this), 20);

		//create class for Commands handling (typing in chat with / symbol)
		$this->ip = $this->getServer()->getIp();
		$this->domainName = $this->getServer()->getConfigString('server-dns', 'unknown.lbsg.net');
		$this->getLogger()->info("Server is running on IP: " . $this->ip . " and rDNS: " . $this->domainName);
	}

    /**
     * Run when plugin is disabled.
     *
     * @see \pocketmine\plugin\PluginBase::onDisable()
     */
	public function onDisable() {
		// debug part
		$backtrace = debug_backtrace(0, 20);
        $result = '';
        foreach ($backtrace as $k => $v) {
            $result .= "[line ".$backtrace[$k]['line']."] ".$backtrace[$k]['class']." -> ".$backtrace[$k]['function'].PHP_EOL;
        }
		Logger::getInstance()->write("ON DISABLE BACKTRACE".PHP_EOL.$result);
		

		$this->getLogger()->info("Server is shutting down...");
		Logger::getInstance()->write(str_pad('SERVER STOP', 85, '-', STR_PAD_BOTH), true);
	}
	
	/**
	 * @return string
	 */
	public function getDomainName() {
		return $this->domainName;
	}

    /**
     * Called after player types in passwords and successfully logs in. This sets players name tag and displaye name to
     * with proper vip status and/or rank. Sends message to player that they have logged in, gets that players
     * friend list, and sets the players session->authenticated member to true.
     *
     * @param $player
     */
	public function onSuccessfulLogin(LbPlayer $player) {
		$player->sendLocalizedMessage("ON_LOGIN", array(), Translate::PREFIX_PLAYER_ACTION);
		$player->sendPopup(TextFormat::GREEN."You are now logged in.");
		$player->vipEnabled(true);
		$player->updateRanksInPrefixStatus(true);

		$player->updateDisplayedName();
		
		$ev = new PlayerAuthEvent($player);
		$this->getServer()->getPluginManager()->callEvent($ev);
		$this->getServer()->getScheduler()->scheduleAsyncTask(
				new task\CheckMuteRequest($player->getName(), $player->getID())
		);

		//$this->getServer()->getScheduler()->scheduleAsyncTask(new task\FriendListRequest($player->getName(), $player->getPassHash(), true));
		//$this->getServer()->getScheduler()->scheduleAsyncTask(new task\LastSeenRequest($player->getName()));
	}
	
	
	public static function getInstance() {
//		$backtrace = debug_backtrace(0, 20);
//        $result = '';
//        foreach ($backtrace as $k => $v) {
//            $result .= "[line ".$backtrace[$k]['line']."] ".$backtrace[$k]['class']." -> ".$backtrace[$k]['function'].PHP_EOL;
//        }
//		var_dump($result);
		
		if (!is_null(self::$instance)) {
			return self::$instance;
		}
		return null;
	}
	
	public static function fireAll() {
		static::$lbsgStaffNames = array();
	}

}
