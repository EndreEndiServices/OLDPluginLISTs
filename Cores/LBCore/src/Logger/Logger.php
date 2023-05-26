<?php

namespace Logger;

use Exception;
use LbCore\player\LbPlayer;
use Logger\LoggerEventListener;
use Logger\Slack;
use Logger\task\CheckLobbyPlayersTick;
use Logger\task\LoggerTick;
use Logger\task\ResourcesUsageTick;
use PharData;
use Phar;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;

/**
 * Base logic of Logger
 *
 */
class Logger {

	/** @var string */
	private $pathToLogs = './logs/';

	/** @var resource|boolean */
	private $logFileHandler = false;

	/** @var int */
	private $currentBufferSize = 0;

	/** @var string */
	private $serverIp;

	/** @var string */
	private $serverName;

	/** @var string */
	private $serverStartFile;

	/** @var array */
	private $kickedPlayers = array();

	/** @var array */
	private $cpuLoadStat = array();

	/** @var int */
	private $memoryWarningLastTick = 0;

	/** @var Logger */
	static private $instance;
	
	/** @var string */
	private $currentGameType = '';

	const ERROR_RIGHTS = 'You don\'t have rights to write files into ';
	const ERROR_MKDIR = 'You don\'t have rights to create dir';
	const ERROR_FWRITE = 'Problem with writing to log file';
	const LOG_NUMBER_RESTRICTION = 10;
	const LOG_BUFFER_SIZE = 60;
	const CRITICAL_SERVER_RESTARTS_COUNT = 2;
	const KICKED_PLAYERS_CRITICAL_COUNT = 30;
	const CRITICAL_CPU_LOAD_VALUE = 80; //in percents
	const CRITICAL_MEMORY_USAGE_VALUE = 1024; //in MB
	const NOTICE = 0;
	const WARNING = 1;

	private function __construct() {
		if (!file_exists($this->pathToLogs) && !mkdir($this->pathToLogs)) {
			throw new Exception(self::ERROR_MKDIR);
		}
		$this->serverIp = Server::getInstance()->getIp();
		$this->serverName = $this->getServerName();
		$this->logFileHandler = $this->getLogFileHandler();
		if ($this->logFileHandler === false) {
			throw new Exception(self::ERROR_RIGHTS . $this->pathToLogs);
		}
		$this->serverStartFile = $this->pathToLogs . 'server_start.txt';
		$this->updateServerStartLog();
	}

	/**
	 * protection
	 */
	private function __clone() {}
	private function __wakeup() {}

	public function __destruct() {
		if ($this->logFileHandler) {
			fflush($this->logFileHandler);
			fclose($this->logFileHandler);
		}
	}

	/**
	 * implementation of singleton pattern
	 * 
	 * @return Logger
	 */
	static public function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Enables logger and registers EventListener and RepeatingTask
	 * 
	 * @param Plugin $plugin
	 */
	static public function enable(Plugin $plugin) {
		self::getInstance();
		$server = Server::getInstance();
		$server->getPluginManager()->registerEvents(new LoggerEventListener(), $plugin);
		// run LoggerTick every minute
		$server->getScheduler()->scheduleRepeatingTask(new LoggerTick($plugin), 20 * 60);
		// run ResourcesUsageTick every 5 seconds
		$server->getScheduler()->scheduleRepeatingTask(new ResourcesUsageTick($plugin), 20 * 5);
		// Run CheckLobbyPlayersTick every 10 minutes
		$server->getScheduler()->scheduleRepeatingTask(new CheckLobbyPlayersTick($plugin), 20 * 60 * 30); //check every 30 minutes
	}

	/**
	 * 
	 * Created new log file and remove all outdated
	 * 
	 * @return resource|boolean
	 */
	private function getLogFileHandler() {
		$dir = dir($this->pathToLogs);
		$count = 0;
		$logs = array();

		// checking number of log files
		while ($fileName = $dir->read()) {
			if (!is_dir($this->pathToLogs . $fileName) &&
				strpos($fileName, '.log') !== false) {

				$logs[] = $fileName;
				$count++;
			}
		}

		// remove outdated logs
		if ($count >= self::LOG_NUMBER_RESTRICTION) {
			$count -= self::LOG_NUMBER_RESTRICTION;

			for ($i = 0; $i <= $count; $i++) {
				unlink($this->pathToLogs . $logs[$i]);
			}
		}

		$filename = $this->pathToLogs . date('Y.m.d') . '_' . $this->serverName . '_play.txt';
		if(!file_exists($filename)){
			$oldFilename = $this->pathToLogs . date('Y.m.d', time() - 86400) . '_' . $this->serverName . '_play.txt';
			if (file_exists($oldFilename)) {
				if (!is_dir($this->pathToLogs . "json/")) {
					mkdir($this->pathToLogs . "json/");
				}
				new Analyzer(file_get_contents($oldFilename), date('d.m.Y', time() - 86400), $this->serverName, $this->pathToLogs . "json/" .  date('Y_m_d', time() - 86400) . ".json");
			}
		}
		return fopen($filename, 'a');
	}

	
	public function changeLogFile() {
		$this->logFileHandler = $this->getLogFileHandler();
		if ($this->logFileHandler === false) {
			echo 'LOG FILE DOESN\'T EXIST';
		}
	}

	/**
	 * 
	 * Compresses outdated log files into archives
	 * NOT USED
	 * 
	 * @param string $fileName
	 * @throws Exception
	 */
	private function compressLogFile(string $fileName) {
		$archiveDir = $this->pathToLogs . 'archive/';

		if (!file_exists($archiveDir) || !is_dir($archiveDir)) {
			if (!mkdir($archiveDir)) {
				throw new Exception(self::ERROR_MKDIR);
			}
		}

		$archive = new PharData($archiveDir . $fileName . '.tar');
		$archive->addFile($this->pathToLogs . $fileName);
		$archive->compress(Phar::GZ);

		unlink($archiveDir . $fileName . '.tar');
	}

	/**
	 * Returns server_start log file
	 * 
	 * @return resource|boolean
	 */
	private function getServerStartLogFile() {
		$filename = $this->serverStartFile;
		return fopen($filename, 'a');
	}

	/**
	 * Updates server start log file
	 */
	private function updateServerStartLog() {
		$file = $this->getServerStartLogFile();
		fwrite($file, time() . "\r\n");
	}

	/*
	 * Checks for the server restart count in last hour.
	 * Writes new warning message to the log file
	 * if restart count >= self::CRITICAL_SERVER_RESTARTS_COUNT
	 */

	public function checkServerStartsCount() {
		$hourStart = time() - 3600;
		$hourEnd = time();

		$fileContent = file_get_contents($this->serverStartFile);
		$file = $this->getServerStartLogFile();
		$rows = explode("\r\n", $fileContent);

		if (max($rows) + 3600 <= $hourEnd) {
			ftruncate($file, 0);
		} else {
			$startCount = 0;
			foreach ($rows as $row) {
				if ($hourStart <= $row && $row <= $hourEnd) {
					$startCount++;
				}
			}
			if ($startCount > self::CRITICAL_SERVER_RESTARTS_COUNT) {
				$msg = "Server has been restarted " . ($startCount - 1) . " times in the last hour";
				$this->write($msg, true, self::WARNING);
				ftruncate($file, 0);
			}
		}
	}

	/**
	 * Adds kicked player to the $this->kickedPlayers array
	 * 
	 * @param \pocketmine\Player $player
	 */
	public function addKickedPlayer(Player $player) {
		$this->kickedPlayers[] = $player;
	}

	/*
	 * Checks for the count of kicked players since last tick.
	 * Writes new warning message to the log file
	 * if kicked players count >= self::KICKED_PLAYERS_CRITICAL_COUNT
	 */

	public function checkKickedPlayers() {
		$kickedCount = count($this->kickedPlayers);
		if ($kickedCount >= self::KICKED_PLAYERS_CRITICAL_COUNT) {
			$msg = $kickedCount . ' players were kicked in the last minute';
			$this->write($msg, true, self::WARNING);
		}

		$this->kickedPlayers = array();
	}

	/**
	 * Add CPU usage in % to the cpuLoadStat array
	 */
	public function trackCpuUsage() {
		$cpuUsage = Server::getInstance()->getTickUsage();
		if ($cpuUsage < 1000) {
			$this->cpuLoadStat[] = $cpuUsage;
		}
	}

	/**
	 * Checks average CPU usage and writes a warning message
	 * if CPU load has reached the critical value
	 */
	public function checkCPUUsage() {
		if (!count($this->cpuLoadStat))
			return false;

		$avgCpuLoad = array_sum($this->cpuLoadStat) / count($this->cpuLoadStat);
		if ($avgCpuLoad >= self::CRITICAL_CPU_LOAD_VALUE) {
			$playersCount = count(Server::getInstance()->getOnlinePlayers());
			$msg = 'high CPU load [CPU load: ' . round($avgCpuLoad, 2) . '% | Players online: ' . $playersCount . ']';
			$this->write($msg, true, self::WARNING);
		}
	}

	/**
	 * Checks memory usage and writes a warning message
	 * if it has reached the critical value
	 */
	public function checkMemoryUsage($currentTick) {
		//check if last memory warning was shown at least 1 hour ago
		if (($this->memoryWarningLastTick + 20 * 60 * 60) > $currentTick)
			return false;

		$memoryUsage = memory_get_usage(true) / 1024 / 1024; //converted in MB
		if ($memoryUsage >= self::CRITICAL_MEMORY_USAGE_VALUE) {
			$playersCount = count(Server::getInstance()->getOnlinePlayers());
			$msg = 'high memory usage [RAM usage: ' . round($memoryUsage, 2) . 'MB | Players online: ' . $playersCount . ']';
			$this->write($msg, true, self::WARNING);
			$this->memoryWarningLastTick = $currentTick;
		}
	}

	/**
	 * 
	 * Writes message to file
	 * 
	 * @param string $msg
	 * @param boolean $withDate
	 */
	public function write(string $msg, $withDate = false, $status = self::NOTICE) {

		if ($status == self::WARNING) {
			$this->sendToSlack($msg);
			$msg = 'WARNING: ' . $msg;
		}

		if ($withDate) {
			$msg = str_pad(date('H:i:s'), 85, '*') . "\r\n" . $msg;
		}

		if (fwrite($this->logFileHandler, $msg . "\r\n") !== false) {
			$this->currentBufferSize++;
			if ($this->currentBufferSize >= self::LOG_BUFFER_SIZE) {
				$this->currentBufferSize = 0;
				fflush($this->logFileHandler);
			}
		} else {
			echo self::ERROR_FWRITE;
		}
	}

	protected function getServerName() {
		return Server::getInstance()->getConfigString('server-dns', 'unknown.lbsg.net');
	}

	protected function sendToSlack($msg) {
		if ($this->serverIp == '0.0.0.0') {
			return false;
		}
		Server::getInstance()->getScheduler()->scheduleAsyncTask(new Slack($this->serverName, $msg));
	}
	
	/**
	 * Warning!!!
	 * Player must be at least instance of LbPlayer
	 * 
	 * Writes a warning message to the log file if count of players in lobby >
	 * than count of players in game
	 */
	public function checkPlayersInLobbyCount() {
		$players = Server::getInstance()->getOnlinePlayers();
		$playersInLobbyCount = 0;
		$playersInGameCount = 0;
		foreach ($players as $player) {
			if ($player->getState() == LbPlayer::IN_LOBBY) {
				$playersInLobbyCount++;
			} else {
				$playersInGameCount++;
			}
		}
		
		if ((count($players) >= 20) &&
				$playersInGameCount >=1 && 
				$playersInLobbyCount > ($playersInGameCount*2)) {
			$msg = 'More than 2/3 of players in lobby ';
			$msg .= '['.$playersInLobbyCount.'/'.count($players).']';
			$this->write($msg, true, Logger::WARNING);
		}
	}
	
	public function initCurrentGameType(string $gameType) {
		$this->currentGameType = $gameType;
	}
	
	public function checkGameType(string $gameType) {
		return $this->currentGameType == $gameType;
	}
}
