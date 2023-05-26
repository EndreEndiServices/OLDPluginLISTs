<?php

namespace TreasureHunt\command;

use TreasureHunt\Slack;
use TreasureHunt\TreasureHunt;
use LbCore\player\LbPlayer;
use LbCore\LbCore;
use pocketmine\Server;
use pocketmine\command\defaults\VanillaCommand;
use pocketmine\math\Vector3;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

/**
 * Logic for command /contest which allows to handle Treasure hunt manually
 */
class ContestCommand extends VanillaCommand {
	/*area radius value where to look for chest around specified coords*/
	const CHEST_AREA_RADIUS = 5;
	/*DB CONNECTION DATA*/
	/** @var string */
	private $DBServer = "accessory.lbsg.net";
	/** @var string */
	private $DBName = "ingamekits";
	/** @var string */
	private $DBUser = "ingamekits";
	/** @var string */
	private $DBPass = "jdyhu7c7olaP3";
	
	/**
	 * Base command class constructor
	 * 
	 * @param string $name
	 */
	public function __construct($name) {
		
		parent::__construct(
			$name,
			"Start/stop treasure hunt, show status",
			"/contest <status|claim|start|stop> <x> <y> <z>"
		);
		$this->setPermission("lbcore.command");
	}

	/**
	 * Base handling function for player's input
	 * 
	 * @param CommandSender $sender
	 * @param string $currentAlias
	 * @param array $args
	 * @return boolean
	 */
	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}		
		if (!($sender instanceof LbPlayer)) {
			return true;
		}
		// check player name
		$playerName = strtolower($sender->getName());
		if (!$sender->isAuthorized() || !in_array($playerName, LbCore::$lbsgStaffNames)) {
			$sender->sendMessage(TextFormat::GRAY . 'For lbsg staff only.');
			return true;
		}
		//check for valid args
		$allowedArgs = ['start', 'stop', 'status', 'claim'];
		$subCommand = $args ? trim(strtolower($args[0])) : '';
		if(!$args || !in_array($subCommand, $allowedArgs)){
			$sender->sendMessage(TextFormat::RED . "Usage: " . $this->usageMessage);
			return true;
		}
		//find method by first arg (start, stop, status, claim)
		$methodName = strtolower($subCommand);
		if (method_exists(__CLASS__, $methodName)) {
			try {
				self::$methodName($sender, $args);
			} catch (\Exception $e) {
				$sender->sendMessage(TextFormat::RED . $e->getMessage());
				echo 'EXCEPTION: '.$e->getMessage().PHP_EOL;
			}
		}
		
		//if start: play stop, then start if needs
//		if ($subCommand == 'start') {
//			try {
//				$this->stop();
//				$sender->sendMessage(TextFormat::GREEN . 'Current treasure hunt has been stopped');
//			} catch (\Exception $e) {
//				echo 'EXCEPTION: '.$e->getMessage().PHP_EOL;
//			}	
//			try {
//				$this->start($sender, $args);
//			} catch (\Exception $e) {
//				$sender->sendMessage(TextFormat::RED . $e->getMessage());
//				echo 'EXCEPTION: '.$e->getMessage().PHP_EOL;
//			}
//			return true;
//		}
//		//if stop 
//		if ($subCommand == 'stop') {
//			try {
//				$this->stop();
//				$sender->sendMessage(TextFormat::GREEN . 'The hunt has been stopped');
//			} catch (\Exception $e) {
//				$sender->sendMessage(TextFormat::RED . $e->getMessage());
//				echo 'EXCEPTION: '.$e->getMessage().PHP_EOL;
//			}
//			return true;
//		}
//		if ($subCommand == 'status') {
//			try {
//				$this->status($sender);
//			} catch (Exception $e) {
//				$sender->sendMessage(TextFormat::RED . $e->getMessage());
//				echo 'EXCEPTION: '.$e->getMessage().PHP_EOL;
//			}
//		}
//		if ($subCommand == 'claim') {
//			try {
//				$this->claim();
//				$sender->sendMessage(TextFormat::WHITE . 'Сontest claim mode enabled');
//			} catch (Exception $e) {
//				$sender->sendMessage(TextFormat::RED . $e->getMessage());
//				echo 'EXCEPTION: '.$e->getMessage().PHP_EOL;
//			}
//		}
		
		return true;
	}

	/**
	 * Start/restart new treasure hunt
	 * 
	 * @param array $args
	 * @return boolean
	 */
	private function start($sender, $args = []) {
		try {
			$this->stop($sender);
//			$sender->sendMessage(TextFormat::GREEN . 'Current treasure hunt has been stopped');
		} catch (\Exception $e) {
			echo 'EXCEPTION: '.$e->getMessage().PHP_EOL;
		}	
		//prepare db connection
		$chestId = null;
		$mapName = '';
		$coordsString = '';
		$connection = new \mysqli($this->DBServer, $this->DBUser, $this->DBPass, $this->DBName);
		if ($connection->connect_error) {
			$message = "Database connection failed: " . $connection->connect_error .PHP_EOL;
			throw new \Exception($message);
		}
		$gameType = TreasureHunt::getInstance()->getGameType();
		//if no coords get random chest id from db
		if (count($args) < 4 && !(isset($args[1]) && strtolower($args[1]) == 'getpos')) {
			//find 100 most rare opened chests and get random from them
			$query = "(SELECT * FROM `chests` WHERE `game_type`='{$gameType}' AND is_temporary<>1 ORDER BY `opened_amount` ASC LIMIT 100) ORDER BY RAND() LIMIT 1;";
			$result = $connection->query($query);
			if ($result) {
				//if found save current chest id
				if ($result->num_rows > 0) {
					$chest = $result->fetch_object();
					$chestId = $chest->id;
					$mapName = $chest->arena_name;
					$coordsString = $chest->coords;
					echo "Found random chest with id " . $chestId . " on arena " . $mapName .PHP_EOL;
				} else {
					$connection->close();
					$message = 'No active chests for this gametype found' .PHP_EOL;
					throw new \Exception($message);
				}
			} else {
				$connection->close();
				$message = "Get chest error: " . $connection->error .PHP_EOL;			
				throw new \Exception($message);
			}			
		} else {
			$coords = $this->getCoordsFromCommand($sender, $args);
			$x = $coords['x'];
			$y = $coords['y'];
			$z = $coords['z'];
			//look for existing chest by these coords (radius 5 blocks)
			$coordsString = $this->getChest($x, $y, $z);
			if ($coordsString) {
				//get id of this chest from db
				$query = "SELECT * FROM `chests` WHERE coords='{$coordsString}' AND game_type='{$gameType}' LIMIT 1;";
				var_dump($query);
				$result = $connection->query($query);
				if ($result) {
					//if found save current chest id
					if ($result->num_rows > 0) {
						$chest = $result->fetch_object();
						$chestId = $chest->id;
						$mapName = $chest->arena_name;
						echo "Found chest with id " . $chestId . " on arena " . $mapName .PHP_EOL;
					} else {
						$connection->close();
						$message = 'No active chests by these coords found';
						throw new \Exception($message);
					}
				} else {
					$connection->close();
					$message = "Get chest error: " . $connection->error .PHP_EOL;
					throw new \Exception($message);
				}			
			//create new row (in chests table) for chest with flag is_temporary. 
			//Those chests appear when server is started and are not saved in map
			} else {
				if (TreasureHunt::isPlaceNearToSpawn($x, $z)) {
					$connection->close();
					$message = 'These coords are too close to spawnpoint';
					throw new \Exception($message);
				}
				$coordsString = $x . ':' . $y . ':' . $z;
				$mapName = TreasureHunt::getArenaByCoords($x, $z);
				if ($mapName) {
					$query = "INSERT INTO `chests` (game_type, arena_name, coords, is_temporary) VALUES ('{$gameType}', '{$mapName}', '{$coordsString}', '1');";
					$result = $connection->query($query);
					if (!$result) {
						$connection->close();
						$message =  "Insert error table chest: " . $connection->error . PHP_EOL;
						throw new \Exception($message);
					}
					//get id of new inserted chest
					$chestId = $connection->insert_id;
					echo "Created chest with id " . $chestId . " on arena " . $mapName.PHP_EOL;
					
				} else {
					$connection->close();
					$message =  "Could not find arena by these coords".PHP_EOL;
					throw new \Exception($message);
				}
			}
		}
		//create new contest to db
		if (!is_null($chestId)) {
			$query = "INSERT INTO `contests` (game_type, chest_id, prize_id) VALUES ('{$gameType}','{$chestId}', 1)";
			$connection->query($query);
			//then try to create chest on map - this code is in repeatable TreasureHunt->checkPrize method
			//to create chest on each server
			TreasureHunt::getInstance()->checkPrize($connection);
		}
		$connection->close();
		
		//send slack message
		$serverName = Server::getInstance()->getConfigString('server-dns', 'unknown.lbsg.net');
		$coordsSlack = str_replace(':', ', ', $coordsString);
		$message = "The Treasure Hunt contest has started on {$gameType}. The prize is hidden on \"{$mapName}\" map [{$coordsSlack}]."
		. " Please check the availability of the chest.";
		Server::getInstance()->getScheduler()->scheduleAsyncTask(new Slack($serverName, $message));
		
		//prepare message for sender
		$sender->sendMessage(TextFormat::GREEN . "New hunt has been launched. The prize is hidden on {$mapName} map [{$coordsSlack}]");
		return true;
	}
	
	/**
	 * Stop current treasure hunt if isset
	 */
	private function stop($sender) {
		//find active contest row	
		$connection = new \mysqli($this->DBServer, $this->DBUser, $this->DBPass, $this->DBName);
		if ($connection->connect_error) {
			$message = "Database connection failed: " . $connection->connect_error . "\n";
			throw new \Exception($message);
		}
		$gameType = TreasureHunt::getInstance()->getGameType();		
		$query = "SELECT c.*, ch.id AS chest_id FROM `contests` AS c JOIN `chests` as ch ON c.chest_id=ch.id WHERE c.game_type='{$gameType}' AND c.end_date=0;";
		$result = $connection->query($query);
		if ($result) {
			//if found - remove row
			if ($result->num_rows > 0) {
				$contest = $result->fetch_object();
				$contestId = $contest->id;
				$query = "DELETE FROM `contests` WHERE id='{$contestId}';";
				$connection->query($query);
				$connection->close();
				
				//send slack message
				$serverName = Server::getInstance()->getConfigString('server-dns', 'unknown.lbsg.net');
				$message = "The Treasure Hunt contest has been stopped on {$gameType}.";
				Server::getInstance()->getScheduler()->scheduleAsyncTask(new Slack($serverName, $message));
				$sender->sendMessage(TextFormat::GREEN . 'Current treasure hunt has been stopped');
				return true;
			} else {
				$connection->close();
				$message = 'No active treasure hunt to stop!'.PHP_EOL;				
				throw new \Exception($message);
			}
		} else {
			$connection->close();
			$message = "Get chest error: " . $connection->error .PHP_EOL;
			throw new \Exception($message);
		}			
	}
	
	
	/**
	 * Show status of current treasure hunt or info that it was stopped
	 * 
	 * @param LbPlayer $sender
	 * @throws \Exception
	 */
	private function status($sender) {
		//try to find active contest row
		$connection = new \mysqli($this->DBServer, $this->DBUser, $this->DBPass, $this->DBName);
		if ($connection->connect_error) {
			$message = "Database connection failed: " . $connection->connect_error . "\n";
			throw new \Exception($message);
		}
		$gameType = TreasureHunt::getInstance()->getGameType();		
		$query = "SELECT TIMEDIFF(NOW(), (SELECT `start_date` FROM `contests` WHERE `game_type`='{$gameType}' AND `end_date`=0)) AS time_ago, c.*, ch.arena_name, ch.coords, ch.id AS chest_id FROM `contests` AS c JOIN `chests` as ch ON c.chest_id=ch.id WHERE c.game_type='{$gameType}' AND c.end_date=0;";
		$result = $connection->query($query);
		$statMessage = '';
		if ($result) {
			if ($result->num_rows > 0) {
				//if found collect stats to send message
				$connection->close();
				$contest = $result->fetch_object();
				$contestArena = $contest->arena_name;
				$contestCoords = $contest->coords;
				$coords = str_replace(':', ', ', $contestCoords);
				$timeString = self::dateAsHumanReadableString($contest->time_ago);
				$statMessage = TextFormat::WHITE . "The contest is running.\n"
						. "Location: {$gameType} on {$contestArena} map [{$coords}]\n"
						. "Duration: {$timeString}.";
			} else {
				//else prepare message that contest is not running
				//find stats for last active contest
				$query = "SELECT TIMEDIFF(NOW(), (SELECT `end_date` FROM `contests` WHERE id=(SELECT max(`id`) FROM `contests`) AND `game_type`='{$gameType}'));";
				$result = $connection->query($query);
				if ($result) {
					if ($result->num_rows == 1) {
						$connection->close();
						$prevContest = $result->fetch_row();
						$contestEndTime = self::dateAsHumanReadableString($prevContest[0]);
						$statMessage = TextFormat::WHITE . "No contest is running.\n"
							. "The previous one completed {$contestEndTime} ago.";
					} else {
						$message = "Something went wrong".PHP_EOL;
						throw new \Exception($message);
					}
				} else {
					$connection->close();
					$message = "Get contest error: " . $connection->error .PHP_EOL;
					throw new \Exception($message);
				}	
			}
		}else {
			$connection->close();
			$message = "Get contest error: " . $connection->error .PHP_EOL;
			throw new \Exception($message);
		}	
		//send message
		if ($statMessage) {
			$sender->sendMessage($statMessage);
		} else {
			$message = "Something went wrong".PHP_EOL;
			throw new \Exception($message);
		}
	}

	/**
	 * Allows lbsg staff in test format claim T-Shirts
	 * 
	 * @param LbPlayer $sender
	 */
	private function claim($sender) {
		if (TreasureHunt::getInstance()->isAdminWinnerAllowed()) {
			TreasureHunt::getInstance()->setAdminWinnerMode(false);
			$sender->sendMessage(TextFormat::WHITE . 'Contest claim mode disabled');
		} else {
			TreasureHunt::getInstance()->setAdminWinnerMode(true);
			$sender->sendMessage(TextFormat::WHITE . 'Contest claim mode enabled');
		}
		return true;
	}

	/**
	 * Prepare integer coords for target chest from command args or from sender position
	 * 
	 * @param LbPlayer $sender
	 * @param array $args
	 */
	private function getCoordsFromCommand($sender, $args = []) {
		$coords = [];
		//get player current position coords if 'getpos' is written
		if (strtolower($args[1]) == 'getpos') {
			$coords['x'] = round($sender->getX(),0);
			$coords['y'] = round($sender->getY(),0);
			$coords['z'] = round($sender->getZ(),0);
		} else {
			$coords['x'] = (int)$args[1];
			$coords['y'] = (int)$args[2];
			$coords['z'] = (int)$args[3];
		}
		return $coords;
	}

	/**
	 * Check by coords with radius in 5 blocks if at least one chest isset there
	 * 
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @return string|boolean
	 */
	private function getChest($x, $y, $z) {		
		//check if coords are not too close to spawnpoints
		if (TreasureHunt::isPlaceNearToSpawn($x, $z)) {
			return false;
		}
		//search for chest block in specified radius around coords
		$level = Server::getInstance()->getDefaultLevel();
		$chunk = $level->getChunk($x >> 4, $z >> 4);
		$chunk->allowUnload = false;
		for ($i = $x-self::CHEST_AREA_RADIUS; $i <= $x+self::CHEST_AREA_RADIUS; $i++) {
			for ($j = $y-self::CHEST_AREA_RADIUS; $j <= $y+self::CHEST_AREA_RADIUS; $j++) {
				for ($k = $z-self::CHEST_AREA_RADIUS; $k <= $z+self::CHEST_AREA_RADIUS; $k++) {
					$block = $level->getBlock(new Vector3($i, $j, $k));
					$tile = $level->getTile(new Vector3($i, $j, $k));
					if ($block instanceof \pocketmine\block\Chest ||
							$tile instanceof \pocketmine\tile\Chest) {
						return $i . ':' . $j . ':' . $k;
					}
				}
			}
		}
		return false;
	}
	
	private static function dateAsHumanReadableString(string $date) {
		$dateArray = explode(':', $date);
		return "{$dateArray[0]}h {$dateArray[1]}min";
	}
}
