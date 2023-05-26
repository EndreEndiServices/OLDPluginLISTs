<?php

namespace TreasureHunt;

use mysqli;
use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\block\Block;
use pocketmine\tile\Tile;
use pocketmine\tile\Chest;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\IntTag;
use TreasureHunt\EventListener;
use TreasureHunt\command\ChestTPCommand;
use TreasureHunt\task\AddWinnerTask;
use TreasureHunt\task\GetChestWithPrizeTask;

class TreasureHunt {
	
	const SPAWN_PROTECT_RADIUS = 20;
	
	private $gameTypes = array(
		'SurvivalGames'  => 'sg',
        'CaptureTheFlag' => 'ctf',
        'Skywars'        => 'sw',
        'walls'          => 'wl',
        'Spleef'         => 'sp',
        'BountyHunter'	 => 'bh',
		'Fleet'			 => 'fl',
	);
	private static $gameType;

	static private $instance;
	static private $teeShirtChest = null;
	
//	private $DBServer = "accessory.lbsg.net";
//	private $DBName = "ingamekits";
//	private $DBUser = "ingamekits";
//	private $DBPass = "jdyhu7c7olaP3";
	private $holdSpawnRadius = self::SPAWN_PROTECT_RADIUS;
	private $mapNames = array(
		"1" => "SG Highway",
		"2" => "Alaskan Village",
		"3" => "Zone 85",
		"4" => "Moonlight Lake",
		"5" => "Breeze Island 2",
		'6' => 'The Alps',
		'7' => 'Zone 85',
		'8' => 'SG IV',
		'9' => 'Ant Mound',
		'10' => 'Howling Mountains',
		'11' => 'Salad Frost',
		'12' => 'Drybone Valley',
		'13' => 'Akupu Shores',
		'14' => 'Hunger Caves',
		'15' => 'Snowbound',
		'16' => 'Origins',
		'17' => 'Fortress Pyke',
		'18' => 'Moonbase 9',
		'19' => 'Turbulence',
		'20' => 'Holiday Resort',
		'21' => 'Excavation Zero'
	);
	private $mapCoords = array(
		//first map - Highway
		'1' => array(
			'1' => array(34, 43, 561)			
		),
		//second map - Alaskan Village
		'2' => array(
			'1' => array(137, 42, 1358)
		),
		// third map - Zone 85
		'3' => array(
			'1' => array(609, 39, -658)
		),
		// 4th map - Moonlight Lake
		'4' => array(
			'1' => array(-550, 18, 485)
		),
		//5th map - Breeze Island 2
		'5' => array(
			'1' => array(46, 9, -531)
		),
	);
	public static $mapBorders = array(
		'SG Highway' => array(
			'maxX' => 228,
			'minX' => -226,
			'maxZ' => 860,
			'minZ' => 366
		),
		'Alaskan Village' => array(
			'maxX' => 290,
			'minX' => -226,
			'maxZ' => 1522,
			'minZ' => 1006
		),
		'Zone 85' => array(
			'maxX' => 909,
			'minX' => 414,
			'maxZ' => -401,
			'minZ' => -834
		),
		'Moonlight Lake' => array(
			'maxX' => -360,
			'minX' => -658,
			'maxZ' => 660,
			'minZ' => 366
		),
		'Breeze Island 2' => array(
			'maxX' => 209,
			'minX' => -210,
			'maxZ' => -399,
			'minZ' => -802
		),
		'The Alps' => array(
			'maxX' => 3329,
			'minX' => 3070,
			'maxZ' => 3457,
			'minZ' => 3198,
		),
		'Zone 85' => array(
			'maxX' => 909,
			'minX' => 414,
			'maxZ' => -401,
			'minZ' => -834,
		),
		'SG IV' => array(
			'maxX' => -356,
			'minX' => -802,
			'maxZ' => 155,
			'minZ' => -242,
		),
		'Ant Mound' => array(
			'maxX' => -934,
			'minX' => -1266,
			'maxZ' => 708,
			'minZ' => 350,
		),
		'Howling Mountains' => array(
			'maxX' => -351,
			'minX' => -832,
			'maxZ' => -399,
			'minZ' => -899,
		),
		'Salad Frost' => array(
			'maxX' => -929,
			'minX' => -1282,
			'maxZ' => 136,
			'minZ' => -242,
		),
		'Drybone Valley' => array(
			'maxX' => 3943,
			'minX' => 3326,
			'maxZ' => -1679,
			'minZ' => -2297,
		),
		'Akupu Shores' => array(
			'maxX' => -2565,
			'minX' => -3250,
			'maxZ' => -866,
			'minZ' => -1586,
		),
		'Hunger Caves' => array(
			'maxX' => 2945,
			'minX' => 2686,
			'maxZ' => 3457,
			'minZ' => 3198,
		),
		'Snowbound' => array(
			'maxX' => 2945,
			'minX' => 2686,
			'maxZ' => 3073,
			'minZ' => 2814,
		),
		'Origins' => array(
			'maxX' => 3329,
			'minX' => 3070,
			'maxZ' => 3073,
			'minZ' => 2814,
		),
		'Fortress Pyke' => array(
			'maxX' => 3969,
			'minX' => 3456,
			'maxZ' => 3457,
			'minZ' => 2948,
		),
		'Moonbase 9' => array(
			'maxX' => -2703,
			'minX' => -3314,
			'maxZ' => -1711,
			'minZ' => -2290,
		),
		'Turbulence' => array(
			'maxX' => -2010,
			'minX' => -2642,
			'maxZ' => 3432,
			'minZ' => 2894,
		),
		'Holiday Resort' => array(
			'maxX' => -1373,
			'minX' => -1874,
			'maxZ' => 3431,
			'minZ' => 2926,
		),
		'Excavation Zero' => array(
			'maxX' => 924,
			'minX' => 414,
			'maxZ' => 225,
			'minZ' => -274,
		)
	);
	/** @var array - used to check if treasure chest is not near spawn*/
	public static $spawnCoords = [
		'bh' => [
			'1' => array(
				'1' => array(
					array(34, 43, 561),// between Swat truck and power tower
					array(80, 45, 537),//by another power tower
					array(-7, 43, 539),//next to the Swat truck
				),
				'2' => array(
					array(46, 43, 663),// in growth alongside the road
					array(12, 43, 701),// next to LBSG truck
					array(75, 46, 634),// by power tower, next to the road
				),
				'3' => array(
					array(556, 45, 627),// in forest, next to the base
					array(-6, 46, 628), //under helicopter, next to the chests  (screen 4)
					array(-49, 51, 660),// in the wood, next to windmill
				),
			),
			//second map - Alaskan Village
			'2' => array(
				'1' => array(
					array(137, 42, 1358),//next to the road, by the tall building
					array(163, 36, 1310),//next to еру road crossing
					array(211, 46, 1346)// on road crossing (screen 2)
				),
				'2' => array(
					array(-15, 40, 1309),//on the forest path from river to base
					array(13, 39, 1277),//default point, next to the chests (screen 1)
					array(22, 41, 1347),//on the road, next to the river
				),
				'3' => array(
					array(114, 35, 1262),//on the field in front of plant
					array(158, 36, 1234),//next to long stair on wall (screen 3)
					array(110, 35, 1227),// next to the truck by the energy plant
				),
			),
			// third map - Zone 85
			'3' => array(
				'1' => array(
					array(609, 39, -658),//between the hills
					array(601, 46, -616),//between another hills
					array(667, 37, -620),//default point, next to the chests (screen 3)
				),
				'2' => array(
					array(758, 47, -660),// under the tree, next to helipad 
					array(733, 58, -665),// on the green hill
					array(742, 40, -622),//in the corridor
				),
				'3' => array(
					array(659, 46, -557),// at the roof corner of the building
					array(723, 53, -584),//in tower (screen 2)
					array(686, 50, -587),//at the roof of the building
				),
			),
			// 4th map - Moonlight Lake
			'4' => array(
				'1' => array(
					array(-550, 18, 485),//on mountain, outer point in west-north
					array(-536, 27, 478),//on mountain, counter-clockwise from waterfall
					array(-514, 23, 470),//on mountain, next to waterfall (screen 3)
				),
				'2' => array(
					array(-550, 22, 544),//on mountain, outer point in west-south
					array(-532, 6, 519),//default point, next to the chests (screen 1)
					array(-510, 17, 559), //opposite to the warefall
				),
				'3' => array(
					array(-459, 24, 523),//on mountain, outer point in east
					array(-487, 21, 476),//on corner mountain (screen 5)
					array(-470, 19, 518),//next to the lake, waterfall on the right hand
				),
			),
			//5th map - Breeze Island 2
			'5' => array(
				'1' => array(
					array(46, 9, -531),// on a beach, next to a small ship
					array(8, 58, -566),//high above the main arena point on the rock (screen 4)
					array(57, 38, -574),//on the green mountain  (screen 5)
				),
				'2' => array(
					array(27, 11, -722),//on a beach of the north island
					array(40, 45, -653),// on the mountain trees, next to a broken ship
					array(7, 9, -667),// on a beach, next to a broken ship
				),
				'3' => array(
					array(-128, 14, -624),// on a big ship
					array(-59, 9, -609),// on a beach, next to a big ship
					array(-39, 38, -590),// on the mountain trees, next to a big ship
				),
			),
		],
		'tm' => array(
			'1' => array(
				'1' => array(
					array(6, 44, 608),
				),
			),
			'2' => array(
				'1' => array(
					array(33, 37, 1264),
				),
			),
			'3' => array(
				'1' => array(
					array(686, 18, 1276),
				),
			),
			'4' => array(
				'1' => array(
					array(-508, 4, 514),
				),
			),
			'5' => array(
				'1' => array(
					array(5, 16, -601),
				),
			),
			'6' => array(
				'1' => array(
					array(3207, 19, 3326),
				),
			),
			'7' => array(
				'1' => array(
					array(677, 35, -619),
				),
			),
			'8' => array(
				'1' => array(
					array(-599, 31, -47),
				),
			),
			'9' => array(
				'1' => array(
					array(-1097, 52, 533),
				),
			),
			'10' => array(
				'1' => array(
					array(-607, 12, -648),
				),
			),
			'11' => array(
				'1' => array(
					array(-1089, 4, -116),
				),
			),
			'12' => array(
				'1' => array(
					array(3635, 19, -1988),
				),
			),
			'13' => array(
				'1' => array(
					array(-2861, 20, -1085),
				),
			),
			'14' => array(
				'1' => array(
					array(2816, 7, 3328),
				),
			),
			'15' => array(
				'1' => array(
					array(2848, 14, 2952),
				),
			),
			'16' => array(
				'1' => array(
					array(3199, 71, 2943),
				),
			),
			'17' => array(
				'1' => array(
					array(3761, 70, 3206),
				),
			),
			'18' => array(
				'1' => array(
					array(-3065, 20, -2044),
				),
			),
			'19' => array(
				'1' => array(
					array(-2373, 53, 3170),
				),
			),
			'20' => array(
				'1' => array(
					array(-1620, 72, 3179),
				),
			),
			'21' => array(
				'1' => array(
					array(669, 10, -25),
				),
			),
		),
	];
	/** @var array - used to collect amount of openings for each chest by its coords*/
	public static $chestsStat = [];
	/** @var bool - when true lbsg staff can win T-shirts*/
	private $adminWinnerAllowed = false;

	/**
	 * Check if coords in parameters are too close to spawnpoints
	 * 
	 * @param int $x
	 * @param int $z
	 * @return boolean
	 */
	public static function isPlaceNearToSpawn($x, $z) {
		foreach (self::$spawnCoords[self::$gameType] as $spawnByArena) {
			foreach ($spawnByArena as $spawnByTeam) {
				foreach ($spawnByTeam as $spawn) {
					if (sqrt(($x - $spawn[0]) ** 2 + ($z - $spawn[2]) ** 2 ) < self::SPAWN_PROTECT_RADIUS) {
						return true;
					}
				}
			}
		}
		return false;
	}


	private function __construct() {
		$this->initGameType();
//		$connection = $this->createDBConnection();
//		if ($connection) {	
////			$connection->query("TRUNCATE TABLE tee_shirt_winners");
////			$this->cteateTables($connection);
//			$this->findChests($connection);
////			$this->checkPrize($connection);
//		}
//		$connection->close();
////		$this->findArenaBorders();
	}

	static public function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	static public function enable($plugin) {
		self::getInstance();
		$server = Server::getInstance();
		$server->getPluginManager()->registerEvents(
			new EventListener(), $plugin
		);
		$server->getScheduler()->scheduleRepeatingTask(
			new TreasureHuntTick($plugin), 20 * 60 * 5
		);
		//save chests stat to db each hour
		$server->getScheduler()->scheduleRepeatingTask(
			new ChestsStatTick($plugin), 20 * 60 * 60
		);
		// register new command to fast teleportation to chest with prize
		$server->getCommandMap()->register('treasurehunt', new ChestTPCommand('chesttp'));
		$server->getCommandMap()->register('treasurehunt', new command\ContestCommand('contest'));
	}
	
	private function initGameType() {
		$server = Server::getInstance();
		foreach ($this->gameTypes as $name => $prefix) {
			$plugin = $server->getPluginManager()->getPlugin($name);
			if (!is_null($plugin)) {
				if ($prefix == 'sg' && $plugin->useTeams) {
					$prefix = 'tm';
				}
				self::$gameType = $prefix;
			}
		}
	}
	
	public function getGametype() {
		return self::$gameType;
	}
	
	static public function setTeeShirtChest($chest) {
		self::$teeShirtChest = $chest;
	}
	
	static public function getTeeShirtChest() {
		return self::$teeShirtChest;
	}
	
	public function isAdminWinnerAllowed() {
		return $this->adminWinnerAllowed;
	}
	
	public function setAdminWinnerMode($value = false) {
		$this->adminWinnerAllowed = $value;
	}

	private function createDBConnection() {
//		$connection = new mysqli($this->DBServer, $this->DBUser, $this->DBPass, $this->DBName);
//		if ($connection->connect_error) {
//			echo "Database connection failed: " . $connection->connect_error . "\n";
//			return false;
//		}
//
//		return $connection;
	}

	private function findChests($connection) {
////		$connection->query("TRUNCATE TABLE chests");
//		$steps = array(
//			array("x" => 1, "z" => 0, "type" => "maxX"),
//			array("x" => -1, "z" => 0, "type" => "minX"),
//			array("x" => 0, "z" => 1, "type" => "maxZ"),
//			array("x" => 0, "z" => -1, "type" => "minZ")
//		);
//		$level = Server::getInstance()->getDefaultLevel();
////		foreach ($this->mapCoords as $id => $coords) {
//		foreach (self::$spawnCoords[self::$gameType] as $id => $coords) {
//			$borders = array();
//			foreach ($steps as $step) {
//				$currentX = $coords[1][0][0];
//				$currentZ = $coords[1][0][2];
//				$currentY = $coords[1][0][1];
//				$finded = false;
//				$stepX = $step["x"];
//				$stepZ = $step["z"];
//				while (!$finded) {
//					$level->getChunk($currentX >> 4, $currentZ >> 4, false);
//					if ($level->getBlock(new Vector3($currentX, $currentY, $currentZ))->getId() == Block::AIR) {
//						$onlyAir = true;
//						for ($tempY = 0; $tempY < 256; $tempY++) {
//							if ($level->getBlock(new \pocketmine\math\Vector3($currentX, $tempY, $currentZ))->getId() != Block::AIR) {
//								$onlyAir = false;
//								break;
//							}
//							if ($level->getBlock(new \pocketmine\math\Vector3($currentX + $stepX, $tempY, $currentZ + $stepZ))->getId() != Block::AIR) {
//								$onlyAir = false;
//								break;
//							}
//						}
//						$finded = $onlyAir;
//					}
//					$level->unloadChunk($currentX >> 4, $currentZ >> 4, false);
//					$currentX += $stepX;
//					$currentZ += $stepZ;
//				}
//				if ($stepX != 0) {
//					$borders[$step["type"]] = $currentX;
//				}
//				if ($stepZ != 0) {
//					$borders[$step["type"]] = $currentZ;
//				}
//			}
//			
////			echo "'{$this->mapNames[$id]}' => array(".PHP_EOL;
////			foreach ($borders as $key => $value) {
////				echo "'{$key}' => {$value},".PHP_EOL;
////			}
////			echo '),'.PHP_EOL;
//
//			$usedChunks = array();
//			$err = 0;
//			for ($x = $borders["minX"] + $err; $x < $borders["maxX"] - $err; $x++) {
//				for ($z = $borders["minZ"] + $err; $z < $borders["maxZ"] - $err; $z++) {
//					$usedChunks[$x >> 4][$z >> 4] = true;
//				}
//			}
//			
//			$count = 0;
//			foreach ($usedChunks as $dx => $zArr) {
//				foreach ($zArr as $dz => $val) {
//					$chunk = $level->getChunk($dx, $dz, false);
//					if ($chunk) {
//						foreach ($chunk->getTiles() as $tile) {
//							if ($tile instanceof Chest) {
//								$block = $tile->getBlock();
//								$x = $block->getX();
//								$y = $block->getY();
//								$z = $block->getZ();
//								foreach ($coords as $pointsArray) {
//									foreach ($pointsArray as $holdPoint) {
//										if (sqrt(($x - $holdPoint[0]) ** 2 + ($z - $holdPoint[2]) ** 2) < $this->holdSpawnRadius) {
//											continue 3;
//										}
//									}
//								}
//								if ($block->getId() != Block::CHEST) {
//									echo "Invalid block " . $block->getId() . "\n";
//								} else {
//									$blocksAround = array();
//									$blocksAround[] = $level->getBlock(new Vector3($x + 1, $y, $z));
//									$blocksAround[] = $level->getBlock(new Vector3($x - 1, $y, $z));
//									$blocksAround[] = $level->getBlock(new Vector3($x, $y + 1, $z));
//									$blocksAround[] = $level->getBlock(new Vector3($x, $y - 1, $z));
//									$blocksAround[] = $level->getBlock(new Vector3($x, $y, $z + 1));
//									$blocksAround[] = $level->getBlock(new Vector3($x, $y, $z - 1));
//									
//									$blocksNum = 0;
//									foreach ($blocksAround as $block) {
//										if (!$block->isSolid() || $block->canPassThrough()) {
//											break;
//										}
//										$blocksNum++;
//									}
//									
//									if ($blocksNum == 6) {
//										echo "Maybe the chest is under the ground. [{$x}:{$y}:{$z}]".PHP_EOL;
////										$coords123 = "{$x}:{$y}:{$z}";
////										$query = "SELECT * FROM chests WHERE `coords` = '{$coords123}' AND `game_type`='".self::$gameType."';";
////										$result = $connection->query($query);
////										if ($result->num_rows !== 0) {
////											echo 'Found it. Trying delete.'.PHP_EOL;
////											$query = "DELETE FROM `chests` WHERE `coords`='{$coords123}' AND `game_type`='".self::$gameType."';";
////											$result = $connection->query($query);
////										}
//									}
////									$coords123 = "{$x}:{$y}:{$z}";
////									$query = "SELECT * FROM chests WHERE `coords` = '{$coords123}' AND `game_type`='".self::$gameType."';";
////									$result = $connection->query($query);
////									if ($result->num_rows == 0) {
//////										echo "DB doesn't contains chest with coords {$coords123}.\n";
////										$this->addChest($connection, self::$gameType, $this->mapNames[$id], "{$x}:{$y}:{$z}");
////									} else {
//////										echo "DB contains chest with coords {$coords123}.\n";
////									}
//////									$this->addChest($connection, $this->gameType, $this->mapNames[$id], "{$x}:{$y}:{$z}");
////									$count++;
//								}
//							}
//						}
//					}
//
//					$level->unloadChunk($dx, $dz, false);
//				}
//			}
//			echo $this->mapNames[$id] . " = " . $count . "\n";
//		}
	}

	private function addChest($connection, $gameType, $arenaName, $coords) {
//		$query = "INSERT INTO chests (game_type, arena_name, coords) VALUES ('{$gameType}', '{$arenaName}', '{$coords}')";
//		$result = $connection->query($query);
//		if (!$result) {
//			echo "Insert error table chest: " . $connection->error . "\n";
//		}
	}

	public function checkPrize($connection = false) {
		$task = new GetChestWithPrizeTask(self::$gameType);
		Server::getInstance()->getScheduler()->scheduleAsyncTask($task);
	}
	
	/**
	 * Create new chest for treasure hunt if it does not exist
	 */
	public static function createTreasureChest() {
		if (is_null(self::$teeShirtChest)) {
			return;
		}
		
		$level = Server::getInstance()->getDefaultLevel();
		
		$coords = explode(':', self::$teeShirtChest->coords);
		
		$targetChunk = $level->getChunk($coords[0] >> 4, $coords[2] >> 4, false);
		$targetChunk->allowUnload = false;
		$targetPos = new Vector3($coords[0], $coords[1], $coords[2]);
		$block = $level->getBlock($targetPos);		
		//create chest on map if not isset
		if (!($block instanceof \pocketmine\block\Chest)) {
			$level->setBlock($targetPos, Block::get(Block::CHEST));
			$nbt = new Compound(false, [
				new StringTag("id", \pocketmine\tile\Tile::CHEST),
				new IntTag("x", $coords[0]),
				new IntTag("y", $coords[1]),
				new IntTag("z", $coords[2])
			]);
			Tile::createTile("Chest", $targetChunk, $nbt);

			echo "New chest with coords " . $coords[0] . ',' . $coords[1] . ','	. $coords[2] . " created";
		}					
	}
	
	public function addWinners($player, $x, $y, $z) {
		if (!is_null(self::$teeShirtChest)) {
			$playerName = $player->getName();
			$task = new AddWinnerTask(
				self::$teeShirtChest->id, 
				self::$teeShirtChest->contest_id, 
				$playerName
			);
			Server::getInstance()->getScheduler()->scheduleAsyncTask($task);
		}
	}
	
	private function findArenaBorders() {
		
		$steps = array(
			array("x" => 1, "z" => 0, "type" => "maxX"),
			array("x" => -1, "z" => 0, "type" => "minX"),
			array("x" => 0, "z" => 1, "type" => "maxZ"),
			array("x" => 0, "z" => -1, "type" => "minZ")
		);
		$level = Server::getInstance()->getDefaultLevel();
		foreach ($this->mapCoords as $id => $coords) {
			$borders = array();
			foreach ($steps as $step) {
				$currentX = $coords[1][0];
				$currentZ = $coords[1][2];
				$currentY = $coords[1][1];
				$finded = false;
				$stepX = $step["x"];
				$stepZ = $step["z"];
				while (!$finded) {
					$level->getChunk($currentX >> 4, $currentZ >> 4, false);
					if ($level->getBlock(new Vector3($currentX, $currentY, $currentZ))->getId() == Block::AIR) {
						$onlyAir = true;
						for ($tempY = 0; $tempY < 256; $tempY++) {
							if ($level->getBlock(new \pocketmine\math\Vector3($currentX, $tempY, $currentZ))->getId() != Block::AIR) {
								$onlyAir = false;
								break;
							}
							if ($level->getBlock(new \pocketmine\math\Vector3($currentX + $stepX, $tempY, $currentZ + $stepZ))->getId() != Block::AIR) {
								$onlyAir = false;
								break;
							}
						}
						$finded = $onlyAir;
					}
					$level->unloadChunk($currentX >> 4, $currentZ >> 4, false);
					$currentX += $stepX;
					$currentZ += $stepZ;
				}
				if ($stepX != 0) {
					$borders[$step["type"]] = $currentX;
				}
				if ($stepZ != 0) {
					$borders[$step["type"]] = $currentZ;
				}
			}

			var_dump($this->mapNames[$id]);
			var_dump($borders);
		}
	}
	
	public static function getArenaByCoords($x, $z) {
		foreach (self::$mapBorders as $mapName => $value) {
			if ($x >= $value['minX'] && $x <= $value['maxX'] && 
					$z >= $value['minZ'] && $z <= $value['maxZ']) {
				return $mapName;
			}
		}
		return false;
	}
}
