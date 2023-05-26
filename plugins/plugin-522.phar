<?php

/*
 __PocketMine Plugin__
name=PocketHungerGames
description=Hunger games plugin.
version=0.0.8
apiversion=10
author=Omattyao
class=PocketHungerGames
*/

define("CONVERT_COEFFICIENT", 20);
define("DEFAULT_COIN", 12);
define("LINE_BREAK", 45);

class PocketHungerGames implements Plugin{
	protected $api, $path, $config, $kit, $db, $players, $score, $field;
	private $backup = array("world" => array(), "chest" => array());
	private $switch = array();
	private $schedule = array();
	private $count_id = 0;
	private $s_id = array(); //schedule_id
	protected $status = false;

	public function __construct(ServerAPI $api, $server = false) {
		$this->api = $api;
	}

	public function init() {
		$this->createConfig();
		$this->loadDB();
		$this->readyKit();
		$this->resetParams();
		$this->api->setProperty("gamemode", 0);
		$this->api->event("tile.update", array($this, "handle"));
		$this->api->event("player.join", array($this, "handle"));
		$this->api->event("player.quit", array($this, "handle"));
		$this->api->event("player.death", array($this, "handle"));
		$this->api->event("console.command.stop", array($this, "handle"));
		$this->api->addHandler("player.chat", array($this, "handle"), 5);
		$this->api->addHandler("player.spawn", array($this, "handle"), 5);
		$this->api->addHandler("api.ban.check", array($this, "handle"), 5);
		$this->api->addHandler("entity.explosion", array($this, "handle"), 5);
		$this->api->addHandler("player.offline.get", array($this, "handle"), 5);
		$this->api->addHandler("player.block.place", array($this, "handle"), 5);
		$this->api->addHandler("player.block.break", array($this, "handle"), 5);
		$this->api->addHandler("player.container.slot", array($this, "handle"), 5);
		$this->api->addHandler("console.command.spawn", array($this, "handle"), 5);
		$this->api->console->register("hg", "PocketHungerGames command.", array($this, "command"));
		$this->api->console->register("kit", "PocketHungerGames command.", array($this, "command"));
		$this->api->ban->cmdWhitelist("kit");
		$this->api->ban->cmdWhitelist("hg");
	}

	public function handle(&$data, $event) {
		switch ($event) {
			case "player.join":
				if ($this->getAccount($data->username) === false) {
					$this->createAccount($data->username);
				}
				break;
			case "player.chat":
				if ($this->status !== false) {
					$user = $data["player"]->username;
					$rec = $this->getAccount($user);
					$from = $user." Lv".$rec["level"];
					$this->api->chat->send($from, $data["message"]);
					return false;
				}
				break;
			case "player.spawn":
				if (!$this->switch["server.gate"]) {
					$data->blocked = true;
					$data->sendChat(" ");
					$data->sendChat(" ");
					$data->sendChat(" ");
					$data->sendChat("xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx");
					$data->sendChat(" ");
					$data->sendChat("[HungerGames] Now the tournament is going on.");
					$data->sendChat("[HungerGames] Please join later.");
					$data->sendChat(" ");
					$data->sendChat("xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx");
					$data->close("game is going on.", false);
					$this->broadcast("<server> ".$data->username." left the game.");
					return false;
				}
				if ($this->switch["first.spawn"] instanceof Position) {
					$data->setSpawn($this->switch["first.spawn"]);
				}
				if ($this->status != false) {
					$this->showAccountInfo($data);
				}
				break;
			case "api.ban.check":
				if ($this->status === "finish") {
					return false;
				}
				break;
			case "player.container.slot":
				if ($this->switch["chest.lock"]) return false;
				break;
			case "player.offline.get":
				$data->set("gamemode", 0); //survival
				$data->set("health", 20);
				if ($this->switch["first.spawn"] instanceof Position) {
					$p = $this->switch["first.spawn"];
					$data->set("position", array("x" => $p->x, "y" => $p->y, "z" => $p->z, "level" => $p->level->getName()));
				}
				break;
			case "player.death":
			case "player.quit":
				if ($this->switch["death.count"])	$this->loserProcess($data, $event);
				break;
			case "player.block.place":
				if ($this->switch["world.protect"] and $data["item"]->getID() !== SIGN)	return false;
				if ($this->switch["backup"])	$this->backupLevel("place", $data);
				break;
			case "player.block.break":
				if ($this->switch["world.protect"])	return false;
				if ($this->switch["backup"])	$this->backupLevel("break", $data);
				break;
			case "tile.update":
				if ($this->switch["world.protect"])	return true;
				if ($data instanceof Tile and $data->class === TILE_SIGN) {
					$data->level->setBlockRaw($data, new AirBlock());
					$this->api->tile->remove($data->id);
				}
				break;
			case "entity.explosion":
				return false;
			case "console.command.spawn":
				if ($this->status !== false) {
					if (!($data["issuer"] instanceof Player)) {
						console("Please run this command in-game.");
						break;
					}
					$data["issuer"]->teleport($this->getLobby());
					return false;
				}
				break;
			case "console.command.stop":
				if ($this->status !== false) {
					$this->gameStop();
				}
				break;
		}
	}

	public function loserProcess($data, $event) {
		switch($event) {
			case "player.death":
				$dead = $data["player"]->username;
				if (!isset($this->players[$dead]))	break;
				unset($this->players[$dead]);
				if (is_numeric($data["cause"])) {
					$entity = $this->api->entity->get($data["cause"]);
					if ($entity instanceof Entity and $entity->class === ENTITY_PLAYER) {
						$killer = $entity->name;
						if (!isset($this->players[$killer])) {
							$this->kick($killer, "unknown", false, false);
							break;
						}
						$this->score[$dead]["cause"] = $killer;
						$this->score[$killer]["kill"][] = $dead;
						$reason = "killed by $killer!";
						$this->giveEXP($killer, $this->config["exp"]["kill"]);
						$coin = $this->kit->grantPocketCash($dead);
						$this->kit->grantPocketCash($killer, $coin);
					} else {
						$reason = "killed.";
						$this->score[$dead]["cause"] = "killed";
					}
				} else {
					$reason = "accident.";
					$this->score[$dead]["cause"] = $data["cause"];
				}
				$this->schedule(1, "kick", array($dead, $reason));
				//$this->kick($dead, $reason);
				$nop = count($this->players);
				if ($nop > 1) {
					$this->broadcast(FORMAT_AQUA."[HungerGames] ".$nop." players remaining.");
				} else {
					$winner = array_shift($this->players);
					$this->schedule(1, "gameFinish", $winner->username);
				}
				break;
			case "player.quit":
				$user = $data->username;
				if (!isset($this->players[$user]))	break;
				unset($this->players[$user]);
				$this->score[$user]["cause"] = "disconnect";
				$nop = count($this->players);
				if ($nop > 1) {
					$this->broadcast(FORMAT_AQUA."[HungerGames] ".$nop." players remaining.");
				} else {
					$winner = array_shift($this->players);
					$this->schedule(1, "gameFinish", $winner->username);
				}
				break;
		}
	}

	public function command($cmd, $params, $issuer, $alias) {
		if ($issuer instanceof Player) {
			$output = $this->playerCommand($cmd, $params, $issuer, $alias);
		} else {
			$output = $this->consoleCommand($cmd, $params, $issuer, $alias);
		}
		return $output;
	}

	public function consoleCommand($cmd, $params, $issuer, $alias) {
		$output = "";
		switch ($cmd) {
			case "hg":
				$mode = array_shift($params);
				switch($mode) {
					case "run":
						if ($this->status !== false) {
							$output .= "The game already begins!\n";
							break;
						}
						console("");
						$this->api->chat->broadcast(FORMAT_AQUA."[HungerGames] HungerGame server has been running!!");
						console("");
						$field = array_shift($params);
						if (empty($field))	$field = false;
						$this->gameReady($field);
						break;
					case "start":
						if ($this->status !== "lobby") {
							$output .= "[HungerGames] This command is unavailable now.\n";
							break;
						}
						$this->cancelAllSchedules();
						$this->gameLobby(-($this->config["times"]["lobby"] - 10));
						break;
					case "stop":
						if ($this->status === false) {
							$output .= FORMAT_YELLOW."[HungerGames] game is not opened!\n";
							break;
						}
						$this->gameStop();
						break;
					case "marker":
						$bool = array_shift($params);
						if (!$this->formatBool($bool)) {
							$output .= "Usage: /hg marker <on | off>\n";
							break;
						}
						if ($bool) {
							$this->placePointMarker();
							$output .= "[HungerGames] placed markers on the world!";
						} else {
							$this->breakPointMarker();
							$output .= "[HungerGames] breaked markers on the world!";
						}
						break;
					case "state":
					case "status":
						if ($this->status !== "play") {
							$output .= "[HungerGames] This command is unavailable now.\n";
							break;
						}
						$this->showState($output);
						break;
					case "record":
					case "records":
						$this->showRecords($output);
						break;
					case "settime":
						$mode = (string) array_shift($params);
						$time = (int) array_shift($params);
						if (empty($time) or $time <= 0 or !in_array($mode, array("lobby", "invincible", "play"))) {
							$output .= "Usage: /hg settime <status> <time(sec)>\n<status> ... \"ready\" or \"invincible\" or \"play\"\n";
							break;
						}
						$this->config["times"][$mode] = $time;
						$this->writeConfig();
						$this->formatTime($time);
						$output .= "\"".$mode."\" time is seted to ".$time.".\n";
						break;
					case "setday":
					case "locktime":
						$mode = array_shift($params);
						if (in_array($mode, array("day", "night", "sunset", "sunrise"))) {
							$this->config["lock-time"] = $mode;
							$this->writeConfig();
							$output .= "[HungerGames] Setted lock-time to \"$mode\".\n";
						} else {
							$output .= "[HungerGames] Failed to set lock-time.\n";
						}
						break;
					case "setprize":
						$amount = (int) array_shift($params);
						if ($amount = "" or $amount < 0) {
							$output .= "Usgae :/hg setprize <amount>\n";
							break;
						}
						$this->config["prize"] = $amount;
						$this->writeConfig();
						$output .= "prize is setted to \"".$amount."\".\n";
						break;
					case "worldprotect":
					case "protection":
					case "protect":
						$bool = array_shift($params);
						if (!$this->formatBool($bool)) {
							$output .= "[HungerGames] Usage: /hg protect <on | off>\n";
							break;
						}
						if ($bool) {
							$output .= "[HungerGames] Turned on world protection for tournaments.\n";
							$this->config["world-protect"] = true;
						} else {
							$output .= "[HungerGames] Turned off the protection for tournaments.\n";
							$this->config["world-protect"] = false;
						}
						if ($this->status == "invincible" or $this->status == "play") {
							$this->tool("world.protect", $bool);
						}
						break;
					case "addfield":
						$field = (string) array_shift($params);
						$levelname = (string) array_shift($params);
						if (empty($field)) {
							$output .= "[HungerGames] Usage: /hg addfield <field name>\n";
							break;
						}
						if ($this->isAlnum($field) === false) {
							$output .= FORMAT_YELLOW."[HungerGames] You need to use English for field name.";
							break;
						}
						if (empty($levelname)) {
							$levelname = false;
						}
						if ($this->fieldExists($field)) {
							$output .= FORMAT_YELLOW."[HungerGames] \"".$field."\" already exists!\n";
							break;
						}
						$this->config["field"][$field] = array("lobby" => array(), "start" => array(), "level" => $levelname);
						$this->writeConfig();
						$output .= FORMAT_AQUA."[HungerGames] Adding \"".$field."\" field succeeded! Next, you must set a lobby point and start points of the field.\n";
						$output .= "[HungerGames] Usage: /hg setlobby <field name> <x> <y> <z>\n";
						$output .= "[HungerGames] Usage: /hg addpoint <field name> <x> <y> <z>\n";
						$output .= "[HungerGames] Usage: /hg rmpoint <field name> <number>\n";
						break;
					case "rmfield":
						$field = (string) array_shift($params);
						if (!$this->fieldExists($field)) {
							$output .= FORMAT_YELLOW."[HungerGames] \"".$field."\" doesn't exist!\n";
							break;
						}
						unset($this->config["field"][$field]);
						$output .= FORMAT_AQUA."[HungerGames] Removing \"".$field."\" field succeeded!\n";
						$this->writeConfig();
						break;
					case "fieldinfo":
						$field = (string) array_shift($params);
						if (!$this->fieldExists($field)) {
							$output .= FORMAT_YELLOW."[HungerGames] \"".$field."\" doesn't exist!\n";
							break;
						}
						$this->showFieldInfo($field);
						break;
					case "fieldlist":
						foreach ($this->config["field"] as $field => $data) {
							$output .= "FIELD: \"".FORMAT_YELLOW.$field.FORMAT_RESET."\"\n";
						}
						break;
					case "setlobby":
					case "addpoint":
					case "rmpoint":
						if ($this->status !== false) {
							$output .= "[HungerGames] This command is unavailable now.\n";
							break;
						}
						$this->editField($mode, $params, $output);
						break;
					case "debug":
						var_dump($this->switch);
						break;
					default:
						$output .= "Usage: /hg run ...run HungerGames\n";
						$output .= "Usage: /hg start ...start a game\n";
						$output .= "Usage: /hg stop ...suspend the game\n";
						$output .= "Usage: /hg marker <on | off>\n";
						$output .= "Usage: /hg addfield <field name>\n";
						$output .= "Usage: /hg rmfield <field name>\n";
						$output .= "Usage: /hg fieldinfo <field name>\n";
						$output .= "Usage: /hg setlobby <field name> <x> <y> <z>\n";
						$output .= "Usage: /hg setprize <amount>\n";
						$output .= "Usage: /hg protect <on | off>\n";
						$output .= "Usage: /hg addpoint <field name> <x> <y> <z>\n";
						$output .= "Usage: /hg rmpoint <field name> <number>\n";
				}
				break;
			case "kit":
				$mode = array_shift($params);
				switch ($mode) {
					case "addkit":
					case "add":
						$name = (String) array_shift($params);
						$price = array_shift($params);
						$level = array_shift($params);
						if ($this->kit->add($name, $price, $level) === true) {
							$output .= FORMAT_DARK_AQUA."[HungerGames] Added $name kit!\n";
						} else {
							$output .= FORMAT_YELLOW."[HungerGames] Failed to add $name !\n";
						}
						break;
					case "removekit":
					case "remove":
					case "rmkit":
					case "rm":
						$kitname = trim(array_shift($params));
						if ($this->isAlnum($kitname) === false) {
							$output .= FORMAT_YELLOW."[HungerGames] You need to use English for kit name.";
							break;
						}
						if ($this->kit->remove($kitname)) {
							$output .= FORMAT_DARK_AQUA."[HungerGames] Removed \"$kitname\"!\n";
						} else {
							$output .= FORMAT_YELLOW."[HungerGames] Failed to remove \"$kitname\"!\n";
						}
						break;
					case "list":
						$this->kit->showList($output);
						break;
					case "info":
						$kitname = array_shift($params);
						$this->kit->showKitInfo($kitname);
						break;
					case "additem":
						$kitname = array_shift($params);
						$id = array_shift($params);
						$meta = array_shift($params);
						$count = array_shift($params);
						if (empty($kitname) or $id === null or !is_numeric($id)) {
							$output .= "Usage: /kit additem <kit> <id> (meta) (count)\n";
							break;
						}
						if ($this->kit->get($kitname) === false) {
							$output .= FORMAT_YELLOW."[HungerGames] The kit \"$kitname\" doesn't exist.\n";
							break;
						}
						if (!isset(Block::$class[$id]) and !isset(Item::$class[$id])) {
							$output .= FORMAT_YELLOW."[HungerGames]NOTICE: The item id \"$id\" could be incorrect.\n";
						}
						if ($meta === null) {
							$meta = 0;
						}
						if ($count === null) {
							$count = 1;
						}
						$sets = array("id" => $id, "meta" => $meta, "count" => $count);
						if ($this->kit->editItem("add", $kitname, $sets)) {
							$output .= FORMAT_DARK_AQUA."[HungerGames] Added items to \"$kitname\"!\n";
						} else {
							$output .= FORMAT_YELLOW."[HungerGames] Failed to add items to \"$kitname\".\n";
						}
						$this->kit->showKitInfo($kitname);
						break;
					case debug:
						var_dump($this->kit->getAll());
						break;
					default:
						$output .= "Usage: /kit list\n";
						$output .= "Usage: /kit add <kit name>\n";
						$output .= "Usage: /kit additem <kit name> <id> (meta) (count)\n";
						$output .= "Usage: /kit rm <kit name>\n";
				}
				break;
		}
		return $output;
	}

	public function playerCommand($cmd, $params, $issuer, $alias) {
		$output = "";
		switch ($cmd) {
			case "kit":
				$kitname = trim(array_shift($params));
				switch ($kitname) {
					case "":
						$this->kit->showAccountInfo($issuer);
					case "help":
						$output .= "Usage: /kit <kitname> ......... buy kit\n";
						$output .= "Usage: /kit list ......... show a kit list\n";
						break;
					case "list":
					case "ls":
						$this->kit->showList($output);
						$output .= "way to buy: /kit <kit name>\n";
						break;
					default:
						$rec = $this->getAccount($issuer->username);
						$output .= $this->kit->buy($issuer->username, $rec["level"], $kitname);
						if ($this->status === "invincible" or $this->status === "play") {
							$this->kit->equip($issuer->username);
						}
				}
				break;
			case "hg":
				$this->showAccountInfo($issuer);
				break;
		}
		return $output;
	}

	public function editField($mode, $params, &$output) {
		$field = array_shift($params);
		if (!$this->fieldExists($field)) {
			$output .= FORMAT_YELLOW."[HungerGames] The field doesn't exist!".FORMAT_RESET.": \"".FORMAT_GREEN."".$field.FORMAT_RESET."\"\n";
			$output .= "[HungerGames] Usage: /hg setlobby <field name> <x> <y> <z>\n";
			$output .= "[HungerGames] Usage: /hg addpoint <field name> <x> <y> <z>\n";
			$output .= "[HungerGames] Usage: /hg rmpoint <field name> <number>\n";
			return;
		}
		switch ($mode) {
			case "setlobby":
				$x = array_shift($params);
				$y = array_shift($params);
				$z = array_shift($params);
				if (!is_numeric($x) or !is_numeric($y) or !is_numeric($z)) {
					$output .= "[HungerGames] Usage: /hg setlobby <field name> <x> <y> <z>\n";
					break;
				}
				$x = (float) $x;
				$y = (float) $y;
				$z = (float) $z;
				$this->config["field"][$field]["lobby"] = array($x, $y, $z);
				$this->writeConfig();
				$output .= FORMAT_AQUA."[HungerGames] Setted!\n";
				$this->showFieldInfo($field);
				break;
			case "addpoint":
				$x = array_shift($params);
				$y = array_shift($params);
				$z = array_shift($params);
				if (!is_numeric($x) or !is_numeric($y) or !is_numeric($z)) {
					$output .= "[HungerGames] Usage: /hg addlobby <field name> <x> <y> <z>\n";
					break;
				}
				$x = (float) $x;
				$y = (float) $y;
				$z = (float) $z;
				$this->config["field"][$field]["start"][] = array($x, $y, $z);
				$this->writeConfig();
				$output .= FORMAT_AQUA."[HungerGames] Added!\n";
				$this->showFieldInfo($field);
				break;
			case "rmpoint":
				$number = array_shift($params);
				if ($number == "") {
					$output .= "[HungerGames] Usage: /hg rmpoint <field name> <number>\n";
					break;
				}
				$number = (int) $number;
				if (!isset($this->config["field"][$field]["start"][$number])) {
					$output .= FORMAT_YELLOW."[HungerGames] No.".$number." has not been setted!\n";
					break;
				}
				unset($this->config["field"][$field]["start"][$number]);
				$this->config["field"][$field]["start"] = array_values($this->config["field"][$field]["start"]);
				$this->writeConfig();
				$output .= FORMAT_AQUA."[HungerGames] Removed No.\"".$number."\" point!\n";
				$this->showFieldInfo($field);
				break;
		}
	}

	public function gameReady($field = false) {
		$this->status = "ready";
		$this->resetParams();
		if (!$this->setField($field)) {
			$this->gameStop();
			return;
		}
		$this->cleanDropedItems();
		$this->gameLobby();
	}

	public function gameLobby($fix = 0) {
		$this->status = "lobby";
		$position = $this->getLobby();
		$this->tool("first.spawn", $position);
		$this->tool("lock.time", true);
		$this->tool("world.protect", true);
		$this->tool("chest.lock", true);
		$this->teleportAllPlayers("lobby");
		$this->setGamesSchedule($fix);
		$this->s_id["lobby_info"] = $this->schedule(33, "lobbyAnnounce", false, true);
		$this->countdown($this->config["times"]["lobby"] + $fix);
	}

	public function gameInvincible() {
		$this->status = "invincible";
		if ($this->setPlayers() === false) {
			$this->broadcast(FORMAT_YELLOW."[HungerGames] Failed to start a tournament!");
			$this->broadcast(FORMAT_YELLOW."[HungerGames] It requires 2 or more people.");
			console("");
			$this->gameSuspend();
			return;
		}
		$nop = count($this->players);
		$time = $this->config["times"]["invincible"];
		$this->readyScore();
		$this->tool("server.gate", false);
		$this->tool("world.protect", $this->config["world-protect"]);
		$this->tool("chest.lock", false);
		$this->tool("backup", true);
		$this->tool("death.count", true);
		$this->teleportAllPlayers("field");
		$this->healAllPlayers();
		$this->confiscateItems();
		$this->cleanDropedItems();
		array_map(function($user) {
			$this->kit->equip($user);
		}, array_keys($this->players));
		$this->countdown($time);
		$this->formatTime($time);
		$this->broadcast(" ");
		$this->broadcast(" ");
		$this->broadcast(" ");
		$this->broadcast(FORMAT_YELLOW."================================================");
		$this->broadcast(" ");
		$this->broadcast(FORMAT_YELLOW."The game has begun!");
		$this->broadcast(FORMAT_YELLOW."There are ".$nop." players participating.");
		$this->broadcast(FORMAT_YELLOW."Good Luck!");
		$this->broadcast(" ");
		$this->broadcast(FORMAT_YELLOW."================================================");
		$this->cancelSchedule($this->s_id["lobby_info"]);
	}

	public function gamePlay() {
		$this->status = "play";
		$this->tool("pvp", true);
		$this->broadcast(FORMAT_YELLOW."[HungerGames] You are no longer invincible.".FORMAT_RESET."");
		$this->countdown($this->config["times"]["play"]);
	}

	public function gameFinish($winner = false) {
		$this->status = "finish";
		$this->tool("death.count", false);
		$this->tool("world.backup", false);
		//$this->teleportAllPlayers("lobby");
		$this->cancelSchedule($this->s_id["finish"]);
		$this->cancelCountSchedule();
		if ($winner !== false) {
			if ($this->givePrize($winner)) {
				$msg = "\"".$winner."\" won a prize of \"".$this->config["prize"]."\" for the tournament!";
			} else {
				$msg = "\"".$winner."\" won the tournament!";
			}
			$this->broadcast(" ");
			$this->broadcast(FORMAT_YELLOW."================================================");
			$this->broadcast(" ");
			$this->broadcast(FORMAT_YELLOW."The game has been finished!!");
			$this->broadcast(FORMAT_YELLOW.$msg);
			$this->broadcast(" ");
			$this->broadcast(FORMAT_YELLOW."================================================");
			$this->giveEXP($winner, $this->config["exp"]["win-tournament"]);
		} else {
			$message= "";
			foreach ($this->players as $user => $player) {
				$message .= $user." ";
			}
			$this->broadcast(" ");
			$this->broadcast(FORMAT_YELLOW."================================================");
			$this->broadcast(" ");
			$this->broadcast(FORMAT_YELLOW."[HungerGames] This game is suspended!");
			$this->broadcast(FORMAT_YELLOW."[HungerGames] Remnants list:");
			$this->broadcast(FORMAT_YELLOW.$message);
			$this->broadcast(" ");
			$this->broadcast(FORMAT_YELLOW."================================================");
		}
		$this->schedule(5, "broadcast", "[HungerGames] All players will be kicked.");
		$this->schedule(10, "gameReady", $this->field["stage"]);
		$this->record($winner, $this->score);
		$players = $this->api->player->getAll();
		if (count($players) == 0)	return;
		foreach ($players as $player) {
			$this->schedule(7, "kick", array($player, "game finished."));
		}
	}

	public function gameSuspend() {
		$this->status = "finish";
		$this->schedule(10, "gameReady", array());
	}

	public function gameStop() {
		$this->broadcast(FORMAT_RED."[HungerGames] Stopped working by the host!");
		$this->cancelAllSchedules();
		$this->resetParams();
	}

	public function resetParams() {
		$this->tool("server.gate", true);
		$this->tool("pvp", false);
		$this->tool("world.protect", false);
		$this->tool("chest.lock", false);
		$this->tool("death.count", false);
		$this->tool("backup", false);
		$this->tool("lock.time", false);
		$this->tool("first.spawn", false);
		$this->cancelAllSchedules();
		$this->players = array();
		$this->score = array();
		$this->field = false;
		$this->status = false;
		$this->kit->resetParams();
	}

	public function tool($tool, $params) {
		switch($tool) {
			case "server.gate":
				$bool = $params;
				$this->formatBool($bool);
				$this->switch["server.gate"] = (bool) $bool;
				break;
			case "pvp":
				$bool = $params;
				$this->formatBool($bool);
				$this->api->setProperty("pvp", $bool);
				break;
			case "first.spawn":
				$position = $params;
				$this->switch["first.spawn"] = $position;
				break;
			case "world.protect":
				$bool = $params;
				$this->formatBool($bool);
				$this->switch["world.protect"] = (bool) $bool;
				break;
			case "chest.lock":
				$bool = $params;
				$this->formatBool($bool);
				$this->switch["chest.lock"] = (bool) $bool;
				break;
			case "death.count":
				$bool = $params;
				$this->formatBool($bool);
				$this->switch["death.count"] = (bool) $bool;
				break;
			case "freeze.players":
				$bool = $params;
				$this->formatBool($bool);
				$players = $this->api->player->getAll();
				if (count($players) == 0)	return;
				foreach ($players as $player) {
					$player->blocked = (bool) $bool;
				}
				break;
			case "backup":
				$bool = $params;
				$this->formatBool($bool);
				$this->switch["backup"] = $bool;
				if ($bool) {
					console("[HungerGames] The world data is backuped.");
					$this->backupChest();
				} else {
					$this->restoreWorld();
				}
				break;
			case "lock.time":
				$time = $params;
				@$this->cancelSchedule($this->s_id["time"]);
				if ($time === false) {
					$this->setTime(1000);
				} else {
					$this->setTime($this->config["lock-time"]);
					$this->s_id["time"] = $this->schedule(300, "setTime", $this->config["lock-time"], true);
				}
				break;
		}
	}

	public function setGamesSchedule($fix = 0) {
		$cfg = $this->config["times"];
		$lobby = $cfg["lobby"] + $fix;
		$invincible = $lobby + $cfg["invincible"];
		$play = $invincible + $cfg["play"];
		$finish = $play + $cfg["finish"];
		$this->s_id["invincible"] = $this->schedule($lobby, "gameInvincible", array());
		$this->s_id["play"] = $this->schedule($invincible, "gamePlay", array());
		$this->s_id["finish"] = $this->schedule($play, "gameFinish", array());
	}

	public function setPlayers() {
		$this->players = array();
		$players = $this->api->player->getAll();
		if (count($players) !== 0) {
			console("");
			console(FORMAT_AQUA."[HungerGames] Player list:");
			console();
			foreach ($players as $player) {
				if ($player->gamemode == 0) { //creative
					$this->players[$player->username] = $player;
					console(FORMAT_GREEN."[OK] ".FORMAT_YELLOW."\"".$player->username."\"".FORMAT_RESET." is ready.");
				} else {
					console(FORMAT_RED."[FAIL] ".FORMAT_YELLOW."\"".$user."\"".FORMAT_RESET." is not survival mode.");
					$player->sendChat("[HungerGames] You are not Survival mode!");
					$player->sendChat("[HungerGames] You will be kicked.");
					$this->kick($player, "not survival.");
				}
			}
			console();
			console(FORMAT_AQUA."=======================================");
			console("[The number of player: ".FORMAT_AQUA."".count($this->players).FORMAT_RESET." players]");
			console("");
		}
		if (count($this->players) <= 1) {
			return false;
		}
	}

	public function setField($field = false) {
		if (count($this->config["field"]) == 0) {
			console(FORMAT_YELLOW."[HungerGames] There is no field data! You have to add a field at first.");
			console ("[HungerGames] Usage: /hg addfield <field name>");
			return false;
		}
		if ($field === false) {
			$field = $this->setFieldAutomatically();
			$this->field = $this->config["field"][$field];
			$this->field["stage"] = false;
			if ($field === false) {
				console(FORMAT_YELLOW."[HungerGames] There is no proper field data! You have to add a field at first.");
				console ("[HungerGames] Usage: /hg addfield <field name>");
				return false;
			}
		} elseif ($this->fieldExists($field)) {
			$this->field = $this->config["field"][$field];
			$this->field["stage"] = $field;
		} else {
			console(FORMAT_YELLOW."[HungerGames] \"".$field."\" doesn't exist!");
			return false;
		}
		if (!$this->testField($field)) {
			console(FORMAT_YELLOW."[HungerGames] \"".$field."\" has some incomplete parts!");
			return false;
		}
		$this->broadcast("[HungerGames] Next stage is selected to \"".$field."\"!");
		return true;
	}

	public function setFieldAutomatically() {
		$fields = $this->config["field"];
		while (true) {
			$field = array_rand($fields);
			if ($this->testField($field)) {
				break;
			}
			unset($fields[$field]);
			if (count($fields) < 1) {
				return false;
			}
		}
		return $field;
	}

	public function testField($field) {
		$map = $this->config["field"][$field];
		if (is_numeric($map["lobby"][0]) and is_numeric($map["lobby"][1]) and is_numeric($map["lobby"][2])) {
			if (count($map["start"] > 0)) {
				foreach ($map["start"] as $c) {
					if (!is_numeric($c[0]) or !is_numeric($c[1]) or !is_numeric($c[2])) {
						return false;
					}
				}
				return true;
			}
		}
		return true;
	}

	public function readyScore() {
		$this->score = array();
		if (count($this->players) === 0)	return;
		foreach ($this->players as $player) {
			$this->score[$player->username] = array("kill" => array(), "cause" => " - ", "exp" => (int) 0);
		}
	}

	public function getLobby() {
		$p = $this->field["lobby"];
		$level = $this->getFieldLevel();
		$lobby = new Position($p[0], $p[1], $p[2], $level);
		return $lobby;
	}

	public function getStartPoints() {
		$return = array();
		$level = $this->getFieldLevel();
		foreach ($this->field["start"] as $p) {
			$return[] = new Position($p[0], $p[1], $p[2], $level);
		}
		return $return;
	}

	public function getFieldLevel() {
		if (empty($this->field))	return false;
		if ($this->field["level"] === false)	return $this->api->level->getDefault();
		$level = $this->api->level->get($this->field["level"]);
		if ($level === false) {
			console(FORMAT_YELLOW."[HungerGames] level: \"".$field."\" doesn't exist!");
			$this->gameStop();
		}
		return $level;
	}

	public function teleportAllPlayers($point) {
		$level = $this->getFieldLevel();
		$players = $this->api->player->getAll();
		if (count($players) == 0)	return false;
		if ($level === false)	return false;
		switch ($point) {
			case "field":
				foreach ($players as $player) {
					$s = $this->field["start"][array_rand($this->field["start"])];
					$position =new Position($s[0], $s[1], $s[2], $level);
					$player->teleport($position);
					$player->setSpawn($position);
				}
				break;
			case "lobby":
				$s = $this->field["lobby"];
				$position = new Position($s[0], $s[1], $s[2], $level);
				foreach ($players as $player) {
					$player->teleport($position);
					$player->setSpawn($position);
				}
				break;
		}
	}

	public function backupLevel($type, $data) {
		switch($type) {
			case "break":
				$block = $data["target"];
				break;
			case "place":
				$block = new AirBlock();
				$block->position(new Position($data["block"]->x, $data["block"]->y, $data["block"]->z, $data["block"]->level));
				break;
		}
		$this->backup["world"][] = $block;
	}

	public function backupChest() {
		if (count($this->api->tile->getAll()) == 0)	break;
		foreach ($this->api->tile->getAll() as $tile) {
			if ($tile->class === TILE_CHEST) {
				$c = array();
				for ($i = 0; $i <= 26; $i++) {
					$c[$i] = $tile->getSlot($i);
				}
				$pos = new Position($tile->x, $tile->y, $tile->z, $tile->level);
				$this->backup["chest"][] = array("pos" => $pos, "inv" => $c);
				//$this->backup["chest"][$tile->id] = $c;
			}
		}
	}

	public function restoreWorld() {
		if (count($this->backup["world"]) !== 0){
			$blocks = array_reverse($this->backup["world"]);
			foreach ($blocks as $block) {
				$block->level->setBlockRaw($block, $block);
			}
		}
		if (count($this->backup["chest"]) !== 0) {
			foreach ($this->backup["chest"] as $chest) {
				$tile = $this->api->tile->get($chest["pos"]);
				if ($tile === false) {
					$tile = $this->api->tile->add($chest["pos"]->level, TILE_CHEST, $chest["pos"]->x, $chest["pos"]->y, $chest["pos"]->z);
				}
				if (($tile instanceof Tile) and $tile->class === TILE_CHEST) {
					foreach ($chest["inv"] as $s => $item) {
						$tile->setSlot($s, $item);
					}
				}
			}
		}
		$this->backup = array("world" => array(), "chest" => array());
	}

	public function countdown($int) {
		$time = $int;
		$counts = array();
		switch ($time) {
			case ($time >= 60):
				$counts = array_merge($counts, $this->getMultiple($time, 60));
				$time = (int) 59;
			case ($time >= 10):
				$counts = array_merge($counts, $this->getMultiple($time, 10));
				$time = (int) 9;
			case ($time >= 5):
				$counts = array_merge($counts, $this->getMultiple($time, 5));
				$time = (int) 4;
			case ($time >= 1):
				$counts = array_merge($counts, $this->getMultiple($time, 1));
				break;
			default:
				return;
		}
		asort($counts);
		if (!in_array($int, $counts)) {
			$this->showTimelimit($int);
		}
		$this->cancelCountSchedule();
		foreach ($counts as $cnt) {
			$this->s_id["count"][] = $this->schedule(($int - $cnt), "showTimelimit", $cnt);
		}
	}

	public function showTimelimit($time) {
		if (!$this->formatTime($time)) {
			return;
		}
		switch ($this->status) {
			case "lobby":
				$this->broadcast(FORMAT_RED."Next tournament will start in ".$time.".");
				break;
			case "invincible":
				$this->broadcast(FORMAT_RED."Invincibility wears off in ".$time.".");
				break;
			case "confiscate":
				$this->broadcast(FORMAT_RED."Everyone will be kicked in ".$time.".");
				break;
			default:
				$this->broadcast(FORMAT_RED.$time." remaining.");
		}
	}

	public function getMultiple($int, $mlt) {
		$arg = (int) $mlt;
		$return = array();
		while ($arg <= $int) {
			if (($arg % $mlt) == 0) {
				$return[] = $arg;
			}
			++$arg;
		}
		return $return;
	}

	public function showState(&$output) {
		$output .= FORMAT_AQUA."[HungerGames] The tournament's state:".FORMAT_RESET."   [Remnants: ".FORMAT_GREEN."".count($this->players).FORMAT_RESET."/".FORMAT_GREEN."".count($this->score).FORMAT_RESET." players]\n";
		foreach ($this->score as $user => $score) {
			if (isset($this->players[$user])) {
				$output .= FORMAT_GREEN." ";
			} else {
				$output .= FORMAT_RED." ";
			}
			$kill = count($score["kill"]);
			$output .= $user."   ".FORMAT_RESET."[kill: ".$kill." death: ".$score["cause"]."]\n";
		}
	}

	public function showRecords(&$output) {
		$records = $this->getRecords();
		foreach ($records as $rec) {
			$kd = $this->kdFormula($rec["kill"], $rec["death"]);
			$name = substr($rec["username"], 0, 15);
			$name = $name . str_repeat(" ", 15 - strlen($name));
			$output .= $name."| level:".FORMAT_AQUA.$rec["level"].FORMAT_RESET." exp:".FORMAT_LIGHT_PURPLE.$rec["exp"].FORMAT_RESET." playing:".FORMAT_GREEN.$rec["times"].FORMAT_RESET." win:".FORMAT_DARK_AQUA.$rec["win"].FORMAT_RESET." kill:".FORMAT_YELLOW.$rec["kill"].FORMAT_RESET." death:".FORMAT_RED.$rec["death"].FORMAT_RESET." k/d:".FORMAT_BLUE.$kd.FORMAT_RESET."\n";
		}
	}

	public function showAccountInfo(Player $player) {
		$rec = $this->getAccount($player->username);
		$server = $this->api->getProperty("server-name");
		$kd = $this->kdFormula($rec["kill"], $rec["death"]);
		$quota = $this->levelFormula($rec["level"] + 1);
		$coin = (String) $this->kit->grantPocketCash($player->username);
		$player->sendChat(" ");
		$player->sendChat(" ");
		$player->sendChat("xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx");
		$player->sendChat(" ");
		$player->sendChat("PocketHungerGames server:");
		$player->sendChat("             ".$server);
		$player->sendChat("name: ".$player->username."        level: ".$rec["level"]."  exp: ".$rec["exp"]."/".$quota);
		$player->sendChat(" win: ".$rec["win"]."  kill: ".$rec["kill"]."  death: ".$rec["death"]."  k/d: ".$kd."  coin: ".$coin);
		$player->sendChat(" ");
		$player->sendChat("xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx");
	}

	public function showFieldInfo($field) {
		if (!$this->fieldExists($field)) {
			return false;
		}
		if ($this->config["field"][$field]["level"] === false) {
			$levelname = "==========";
		} else {
			$levelname = "LEVEL: \"".$this->config["field"][$field]["level"]."\" ";
		}
		$map = $this->config["field"][$field];
		if (!isset($map["lobby"][0])) {
			$map["lobby"] = array(0 => "-", 1 => "-", 2 => "-");
		}
		console();
		console(FORMAT_AQUA."===FIELD: \"".$field."\"  ".$levelname."==============");
		console(FORMAT_GREEN."#LOBBY");
		console(FORMAT_YELLOW."  lobby".FORMAT_RESET.":  (x ".$map["lobby"][0].",y ".$map["lobby"][1].",z ".$map["lobby"][2].")");
		console("");
		console(FORMAT_GREEN."#START POINTS");
		if (count($map["start"]) == 0) {
			console("    -");
		} else {
			foreach ($map["start"] as $i => $point) {
				if (!isset($point[0])) {
					$point = array(0 => "-", 1 => "-", 2 => "-");
				}
				console(FORMAT_YELLOW."  No.".$i.FORMAT_RESET.":  (x ".$point[0].",y ".$point[1].",z ".$point[2].")");
			}
		}
		console("");
		console("");
	}

	public function fieldExists($field) {
		if (!isset($this->config["field"][$field])) {
			return false;
		}
		return true;
	}

	public function schedule($ticks, $method, $args = array(), $repeat = false) {
		$id = $this->count_id;
		$ticks = $ticks * CONVERT_COEFFICIENT;
		$this->schedule[$id] = array($ticks, $method, $args, $repeat);
		$this->api->schedule($ticks, array($this, "callback"), array($method, $args), $repeat, $id);
		$this->count_id++;
		return $id;
	}

	public function cancelSchedule($id) {
		unset($this->schedule[$id]);
	}

	public function cancelCountSchedule() {
		if (count($this->s_id["count"]) == 0)	return false;
		foreach ($this->s_id["count"] as $id) {
			$this->cancelSchedule($id);
		}
		$this->s_id["count"] = array();
	}

	public function cancelAllSchedules() {
		$this->schedule = array();
	}

	public function getSchedule($id) {
		if (!isset($this->schedule[$id]))    return false;
		return $this->schedule[$id];
	}

	public function callback($args, $id) {
		$schedule = $this->getSchedule($id);
		if ($schedule === false)    return false;
		$method = $args[0];
		$params = (Array) $args[1];
		@call_user_func_array(array($this, $method), $params);
		if ($schedule[3] === false) {
			unset($this->schedule[$id]);
		}
	}

	public function placePointMarker() {
		$sign = new SignPostBlock();
		$line2 = "";
		$line3 = "START POINT";
		foreach ($this->config["field"] as $field => $data) {
			if ($data["level"] === false) {
				$level = $this->api->level->getDefault();
			} else {
				$level = $this->api->level->get($data["level"]);
				if ($level === false) {
					console(FORMAT_YELLOW."[HungerGames] ".$field."'s level doesn't exist!".FORMAT_RESET."");
					continue;
				}
			}
			$line1 = "Field: " . $field;
			foreach ($data["start"] as $no => $p) {
				$line4 = "No." . $no;
				$level->setBlock(new Vector3($p[0], $p[1], $p[2]), $sign, false, true, true);
				$this->api->tile->addSign($level, $p[0], $p[1], $p[2], array($line1, $line2, $line3, $line4));
			}
			$p = $data["lobby"];
			$level->setBlock(new Vector3($p[0], $p[1], $p[2]), $sign, false, true, true);
			$this->api->tile->addSign($level, $p[0], $p[1], $p[2], array($line1, $line2, "LOBBY", ""));
		}
	}

	public function breakPointMarker() {
		$air = new AirBlock();
		foreach ($this->config["field"] as $field => $data) {
			if ($data["level"] === false) {
				$level = $this->api->level->getDefault();
			} else {
				$level = $this->api->level->get($data["level"]);
				if ($level === false) {
					continue;
				}
			}
			foreach ($data["start"] as $no => $p) {
				$vector = new Vector3($p[0], $p[1], $p[2]);
				if ($level->getBlock($vector)->getID() === SIGN_POST) {
					$level->setBlockRaw($vector, $air);
					$this->api->tile->remove($this->api->tile->get(new Position($vector, false, false, $level))->id);
				}

			}
			$p = $data["lobby"];
			$vector = new Vector3($p[0], $p[1], $p[2]);
			if ($level->getBlock($vector)->getID() === SIGN_POST) {
				$level->setBlockRaw($vector, $air);
				$this->api->tile->remove($this->api->tile->get(new Position($vector, false, false, $level))->id);
			}
		}
	}

	public function healAllPlayers() {
		$players = $this->api->player->getAll();
		if (count($players) == 0)	return;
		foreach ($players as $player) {
			if ($player->entity instanceof Entity and $player->entity->class === ENTITY_PLAYER) {
				$player->entity->heal(20);
			}
		}
	}

	public function cleanDropedItems() {
		$entities = $this->api->entity->getAll();
		if (count($entities) == 0)	return;
		foreach ($entities as $e) {
			if ($e->class === ENTITY_ITEM) {
				$e->close();
			}
		}
	}

	public function confiscateItems() {
		$players = $this->api->player->getAll();
		if (count($players) == 0)	return;
		$air = BlockAPI::getItem(Air, 0, 0);
		foreach ($players as $player) {
			foreach ($player->inventory as $s => $item) {
				if ($item->getID() !== Air) {
					$player->inventory[$s] = $air;
				}
			}
			$player->armor = array($air, $air, $air, $air);
			$player->sendInventorySlot();
			$player->sendArmor($player);
		}
	}

	public function lobbyAnnounce() {
		$msg = $this->config["announce"];
		if (count($msg) === 0)	return;
		$no = rand(0, count($msg) - 1);
		$this->broadcast(FORMAT_DARK_AQUA."[TIPS] ".$msg[$no]);
	}

	public function givePrize($username) {
		$amount = (int) $this->config["prize"];
		$data = array(
				"issuer" => "PocketHungerGames",
				"username" => $username,
				"method" => "grant",
				"amount" => $amount,
		);
		if ($this->api->dhandle("money.handle", $data) === true)	return true;
		return false;
	}

	public function giveEXP($user, $point) {
		$this->score[$user]["exp"] += $point;
		$this->api->chat->sendTo(false, "You got $point exp!", $user);
	}

	public function setTime($time) {
		$this->api->time->set($time);
	}

	public function broadcast($message, $whitelist = false, $linebreak = false) {
		if ($linebreak === true) {
			$message = $this->lineBreak($message);
		}
		$this->api->chat->broadcast($message);
	}

	public function kick($player, $reason = "you died.", $msg1 = true, $msg2 = false) {
		if (!($player instanceof Player)) {
			$player = $this->api->player->get($player);
			if(!($player instanceof Player))	return false;
		}
		$player->close($reason, $msg2);
		if ($msg1 == true)	$this->broadcast($player->username." has been kicked: ".$reason);
	}

	public function createConfig() {
		$config = array(
				"prize" => 1000,
				"world-protect" => false,
				"lock-time" => "day",
				"times" => array(
						"lobby" => 300,
						"play" => 1000,
						"invincible" => 30,
						"finish" => 30,
				),
				"exp" => array(
						"kill" => 20,
						"win-tournament" => 50,
				),
				"announce" => array(
						"Your inventory will be emptied when game begins.",
						"HungerGames is developed by @omattyao_yk",
						"You should not carry any items in the game.",
						"This game is not the team system. All the others are enemies.",
						"Who survived at the very end will be the winner.",
						"You can buy Kits by using \"/kit\".",
						"Coin has been provided when you joined.",
				),
				"field" => array(),
		);
		$this->path = $this->api->plugin->createConfig($this, $config);
		$this->config = $this->api->plugin->readYAML($this->path."config.yml");
	}

	public function writeConfig() {
		$this->api->plugin->writeYAML($this->path."config.yml", $this->config);
	}

	private function formatBool(&$bool)
	{
		$bool = strtoupper($bool);
		switch ($bool) {
			case "TRUE":
			case "ON":
			case "1":
				$bool = (boolean) true;
				break;
			case "FALSE":
			case "OFF":
			case "0":
				$bool = (boolean) false;
				break;
			default:
				return false;
		}
		return true;
	}

	private function formatTime(&$s) {
		$time = "";
		if ($s == 0) {
			$time = "0 second";
			return false;
		}
		$ms = array(floor($s / 60), $s - floor($s / 60) * 60);
		$hm = array(floor($ms[0] / 60), $ms[0] - floor($ms[0] / 60) * 60);
		if ($hm[0] >= 2) {
			$time .= "$hm[0] hours ";
		} elseif ($hm[0] == 1) {
			$time .= "$hm[0] hour ";
		}
		if ($hm[1] >= 2) {
			$time .= "$hm[1] minutes ";
		} elseif ($hm[1] == 1) {
			$time .= "$hm[1] minute ";
		}
		if ($ms[1] >= 2) {
			$time .= "$ms[1] seconds ";
		} elseif ($ms[1] == 1) {
			$time .= "$ms[1] second ";
		}
		$s = trim($time);
		return true;
	}

	private function isAlnum($text) {
		if (preg_match("/^[a-zA-Z0-9]+$/",$text)) {
			return true;
		} else {
			return false;
		}
	}

	private function lineBreak($str, $length = LINE_BREAK) {
		$result = implode("\n", str_split($str, $length));
		return $result;
	}

	private function kdFormula($kill, $death) {
		if ($kill === 0 and $death === 0) {
			$kd = " - ";
		} elseif ($kill === 0) {
			$kd = (String) "0.00";
		} else {
			$kd = bcdiv($kill, $kill + $death, 2);
		}
		return $kd;
	}

	private function levelFormula($level) {
		$quota  = round((4 * (pow(1.4, $level) - 1.4) / 0.7) * 10);
		return $quota;
	}

	private function levelCheck($level, $exp) {
		$quota = $this->levelFormula($level + 1);
		while ($exp >= $quota) {
			++$level;
			$exp -= $quota;
			$quota = $this->levelFormula($level + 1);
		}
		return array("level" => $level, "exp" => $exp, "quota" => $quota);
	}

	private function loadDB() {
		$this->db = new SQLite3($this->api->plugin->configPath($this) . "record.sqlite3");
		$this->db->exec(
				"CREATE TABLE IF NOT EXISTS records(
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				username TEXT NOT NULL,
				lastjoin TEXT,
				times INTEGER NOT NULL DEFAULT '0',
				win INTEGER NOT NULL DEFAULT '0',
				kill INTEGER NOT NULL DEFAULT '0',
				death INTEGER NOT NULL DEFAULT '0',
				lose INTEGER NOT NULL DEFAULT '0',
				exp INTEGER NOT NULL DEFAULT '0',
				level INTEGER NOT NULL DEFAULT '1'
		)"
		);
	}

	private function record($winner, $scores) {
		if ($winner !== false) {
			$this->db->exec("UPDATE records SET win = win + 1, lose = lose - 1, death = death - 1 WHERE username = '" . $winner . "';");
		}
		$stmt = $this->db->prepare("UPDATE records SET times = times + 1, exp = exp + :exp, kill = kill + :kill, lose = lose + 1, death = death + 1, lastjoin = datetime ('now', 'localtime') WHERE username = :username");
		foreach ($scores as $username => $score) {
			$stmt->clear();
			$kill = count($score["kill"]);
			$stmt->bindValue(":exp", $score["exp"]);
			$stmt->bindValue(":kill", $kill);
			$stmt->bindValue(":username", $username);
			$stmt->execute();
			$rec = $this->getAccount($username);
			$result = $this->levelCheck($rec["level"], $rec["exp"]);
			$this->db->exec("UPDATE records SET level = '".$result["level"]."', exp = '".$result["exp"]."' WHERE username = '".$username."';");
		}
		$stmt->close();
		foreach ($scores as $username => $score) {
			$rec = $this->getAccount($username);

		}
	}

	private function getRecords() {
		$records = array();
		$result = $this->db->query("SELECT * FROM records");
		while ($res = $result->fetchArray(SQLITE3_ASSOC)) {
			$records[] = $res;
		}
		return $records;
	}

	private function createAccount($username) {
		$this->db->exec("INSERT INTO records (username) VALUES ('" . $username . "')");
	}

	private function getAccount($username) {
		$result = $this->db->query("SELECT * FROM records WHERE username = '" . $username . "';")->fetchArray(SQLITE3_ASSOC);
		if ($result === false)	return false;
		return $result;
	}

	public function readyKit() {
		$this->kit = new KitManager($this->api);
		$this->kit->DB($this->path);
	}

	public function __destruct() {
		if ($this->status !== false) {
			$this->gameStop();
		}
		$this->db->close();
		$this->kitdb->close();
	}

}

class KitManager{
	private $api, $config, $kitdb, $coin, $players;

	public function __construct(ServerAPI $api, $server = false) {
		$this->api = $api;
		$this->coin = array();
		$this->players = array();
		$this->config = array(
				"max-kit" => 5,
				"max-item" => 5,
				"max-skill" => 5,
		);
	}

	public function buy($user, $level, $kitname) {
		$output = "";
		if ($user instanceof Player) {
			$user = $user->username;
		}
		$coin = $this->grantPocketCash($user);
		$kit = $this->get($kitname);
		if ($kit === false) {
			$output .= "The kit \"$kitname\" doesn't exist!\n";
		} else if ($coin < $kit["price"]) {
			$output .= "You are short of coin to buy this kit!\n";
			$output .= "coin: $coin,  price: ".$kit["price"]."\n";
		} elseif ($level < $kit["level"]) {
			$output .= "You are short of level to buy this kit!\n";
			$output .= "your level: $level,  kit's level ".$kit["level"]."\n";
		} else {
			if ($this->setEquipment($user, $kit["name"]) !== false) {
				$this->grantPocketCash($user, -$kit["price"]);
				$output .= "You bought \"$kitname\" !\n";
			}
		}
		return $output;
	}

	public function equip($user) {
		$player = $this->api->player->get($user);
		$eq = $this->getEquipment($user);
		foreach($eq as $kitname) {
			if ($this->players[$user][$kitname] === false)	continue;
			$kit = $this->get($kitname);
			for ($i = 0; $i <= 4; $i++) {
				if (!empty($kit["id".$i])) {
					$player->addItem($kit["id".$i], $kit["meta".$i], $kit["count".$i], true);
				}
				/*
				 if (!empty($kit["skill".$i])) {
				}
				*/
				$this->players[$user][$kitname] = false;
			}
		}
	}

	public function setEquipment($user, $kitname) {
		$eq = $this->getEquipment($user);
		if (count($eq) >= $this->config["max-kit"]) {
			$this->api->chat->sendTo(false, "You cannot add the kits anymore.", $user);
			return false;
		}
		if (in_array($kitname, $this->players[$user])) {
			$this->api->chat->sendTo(false, "You cannot buy the same kit.", $user);
			return false;
		}
		$this->players[$user][$kitname] = true;
		return true;
	}

	public function getEquipment($user) {
		if (empty($user))	return false;
		if (!isset($this->players[$user])) {
			$this->players[$user] = array();
		}
		return array_keys($this->players[$user]);
	}

	public function add($name, $price, $level) {
		$kit = $this->get($name);
		if ($kit !== false) {
			return false;
		}
		$level = (Int) max(1, $level);
		$price = (Int) max(0, $price);
		$this->kitdb->exec("INSERT INTO kits (name, price, level) VALUES ('".$name."', '".$price."', '".$level."');");
		/*
		 for ($i = 0; $i <= 4; $i++) {
		if (isset($sets["item"][$i]["id"]) and isset(Item::$class[$sets["items"][$i]["id"]])) {
		$k = $sets["item"][$i];
		$this->kitdb->exec("UPDATE kits SET id'".$i."' = '".$k["id"]."', meta'".$i."' = '".$k["meta"]."', count'".$i."' = '".$k["count"]."' WHERE name = '".$name."';");
		}
		if (isset($sets["skill"][$i]) and $this->getSkill($sets["skill"][$i]) === false) {
		$this->kitdb->exec("UPDATE kits SET skill'".$i."' = '".$sets["skill"][$i]."' WHERE name = '".$name."';");
		}
		}
		*/
		return true;
	}

	public function editItem($mode, $param, $sets) {
		switch ($mode) {
			case "add":
				$kit = $this->get($param);
				for ($i = 0; $i <= 4; $i++) {
					if (empty($kit["id".$i])) {
						$this->kitdb->exec("UPDATE kits SET id".$i." = '".$sets["id"]."',  meta".$i." = '".$sets["meta"]."', count".$i." = '".$sets["count"]."' WHERE name = '".$kit["name"]."';");
						return true;
					}
				}
				console(FORMAT_RED."[HungerGames] cannot add items anymore.");
				return false;
			case "remove":
				$slot = (Int) $param;
				break;
		}
	}

	public function remove($name) {
		if ($this->get($name) === false)	return false;
		$this->kitdb->exec("DELETE FROM kits WHERE LOWER(name) = LOWER('".$name."');");
		return true;
	}

	public function get($name) {
		$kit = $this->kitdb->querySingle("SELECT * FROM kits WHERE LOWER(name) = LOWER('".$name."');", true);
		if (empty($kit))	return false;
		return $kit;
	}

	public function getAll() {
		$kits = array();
		$result = $this->kitdb->query("SELECT * FROM kits;");
		while ($kit= $result->fetchArray(SQLITE3_ASSOC)) {
			$kits[] = $kit;
		}
		return $kits;
	}

	public function grantPocketCash($user, $coin = 0) {
		if (!array_key_exists($user, $this->coin)) {
			$this->coin[$user] = DEFAULT_COIN;
		}
		$this->coin[$user] += $coin;
		return $this->coin[$user];
	}

	public function showKitInfo($kitname) {
		$kit = $this->get($kitname);
		if ($kit === false) {
			console(FORMAT_YELLOW."[HungerGames] The kit \"$kitname\" doesn't exist!");
		}
		console("");
		console(FORMAT_AQUA."===KIT: \"".$kitname."\"  =======================");
		console(FORMAT_GREEN."#ITEM");
		for ($i = 0; $i <= 4; $i++) {
			$info = FORMAT_YELLOW."  slot".$i.FORMAT_RESET.":  ";
			if ($kit["id".$i] === null) {
				$info .= "    -";
			} else {
				$info .= "(id ".$kit["id".$i].", meta ".$kit["meta".$i].", count ".$kit["count".$i].")";
			}
			console($info);
		}
		console("");
		console(FORMAT_GREEN."#SKILL");
		for ($i = 0; $i <= 4; $i++) {
			$info = FORMAT_YELLOW."  slot".$i.FORMAT_RESET.":  ";
			if ($kit["skill".$i] === null) {
				$info .= "    -";
			} else {
				$info .= "\"".$kit["skill".$i]."\"";
			}
			console($info);
		}
		console("");
		console("");
	}

	public function showList(&$output) {
		$kits = $this->kitdb->query("SELECT name, price, level FROM kits;");
		if (!empty($kits)) {
			$output .= "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx\n";
			$output .= "Kits list:\n";
			$strings = "";
			while ($kit = $kits->fetchArray(SQLITE3_ASSOC)) {
				$strings .= $kit["name"].'($'.$kit["price"].':#'.$kit["level"].") ";
			}
			$output .= $this->lineBreak($strings);
			$output .= "\n \n";
		} else {
			$output .= "There is no kits.\n";
		}
	}

	public function showAccountInfo(Player $player) {
		$user = $player->username;
		$eq = $this->getEquipment($user);
		$coin = $this->grantPocketCash($user);
		$player->sendChat("xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx\n");
		$output = "coin: ".$coin."   kit: ";
		if (empty($eq)) {
			$output .= "-";
		} else {
			foreach ($eq as $kitname) {
				$output .= $kitname." ";
			}
		}
		$output .= "\n \n";
		$player->sendChat($output);
	}

	public function resetParams() {
		$this->players = array();
		$this->coin = array();
	}

	public function DB($path) {
		$this->kitdb = new SQLite3($path . "kit.sqlite3");
		$this->kitdb->exec(
				"CREATE TABLE IF NOT EXISTS kits(
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				name TEXT NOT NULL,
				price INTEGER NOT NULL,
				level INTEGER NOT NULL DEFAULT '1',
				id0 INTEGER, id1 INTEGER, id2 INTEGER, id3 INTEGER, id4 INTEGER,
				meta0 INTEGER, meta1 INTEGER, meta2 INTEGER, meta3 INTEGER, meta4 INTEGER,
				count0 INTEGER, count1 INTEGER, count2 INTEGER, count3 INTEGER, count4 INTEGER,
				skill0 INTEGER, skill1 INTEGER, skill2 INTEGER, skill3 INTEGER, skill4 INTEGER
		)"
		);
	}

	private function lineBreak($str, $length = LINE_BREAK) {
		$result = implode("\n", str_split($str, $length));
		return $result;
	}

	public function __destruct() {
		$this->kitdb->close();
	}
}