<?php

namespace bridge\Dragon\arena;

use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\scheduler\CallbackTask;
use pocketmine\math\Vector3;
use pocketmine\math\Vector2;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\DoubleTag;

use pocketmine\event\entity\EntityDamageEvent;

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\entity\Effect;
use pocketmine\item\enchantment\Enchantment;

use pocketmine\utils\Color;

use bridge\Dragon\Team;
use bridge\Dragon\Utils;
use bridge\Main;
use truexackep\arena\Arena;

class ArenaManager
{

    const STAT_WAITING = 0;
    const STAT_STARTING = 1;
    const STAT_START = 2;
    const STAT_RUN = 3;

    const STAT_RESTART = 5;
    const STAT_VICTORY = 6;

    private $players = [];

    public $stat = 0;

    private $time = 0;
    private $times = 0;

    public $plugin;
    private $nametag = [];

    public function __construct(Main $plugin, $data)
    {
        $this->plugin = $plugin;
        $this->data = $data;
        $this->initMap();
        $this->reset(false);
    }

    public function getUtils()
    {
        return (new Utils($this->plugin));
    }

    public function initMap()
    {
        $utils = $this->getUtils();
        $map = $this->data["world"];
        if ($utils->backupExists($map)) {
            $this->resetMap();
            return true;
        }
        $utils->backupMap($map, $this->plugin->getDataFolder());
    }

    public function getPlugin()
    {
        return $this->plugin;
    }

    public function resetMap()
    {
        $utils = $this->getUtils();
        $map = $this->data["world"];
        $utils->resetMap($map);

    }

    public function getServer()
    {
        return $this->plugin->getServer();
    }

    public function getData()
    {
        return $this->data;
    }

    public function getPlayers()
    {
        return $this->players;
    }

    public function getConfig()
    {
        return $this->plugin->getConfig()->getAll();
    }

    public function getPos1($v = true)
    {
        $data = $this->getData();
        if (isset($data["pos1"])) {
            $dt = $data["pos1"];
            if (!$v) {
                return new Vector2($dt["x"], $dt["z"]);
            }
            if (isset($data["world"])) {
                $name = $data["world"];
                if ($this->isLoad($name)) {
                    $level = $this->getServer()->getLevelByName($name);
                    $pos = new Position($dt["x"], $dt["y"], $dt["z"], $level);
                    return $pos;
                }
            }
        }
        return null;
    }
    public function createBaseColor(Player $player, $type = 1)
    {
        $team = $this->getTeam($player);
        if ($type == 1) {
            if ($team == "blue") {
                return "§9{$player->getName()}";
            } elseif ($team == "red") {
                return "§c{$player->getName()}";
            }
        } elseif ($type == 2) {
            switch ($team) {
                case "blue":
                    $perm = $this->getServer()->getPluginManager()->getPlugin("PurePerms")->getUserDataMgr()->getGroup($player)->getName();
                    switch ($perm) {
                        case "ViP+":
                            $named = "§l§6VIP+§9 " . $player->getName();
                            break;
                        case "MVP+":
                            $named = "§l§bMVP+§9 " . $player->getName();
                            break;
                        case "Slayer":
                            $named = "§l§cSlayer§9 " . $player->getName();
                            break;
                        case "YouTube":
                            $named = "§l§cY§fT§9 " . $player->getName();
                            break;
                        case "Xelper":
                            $named = "§l§bHelper§9 " . $player->getName();
                            break;
                        case "Curator":
                            $named = "§l§cCurator§9 " . $player->getName();
                            break;
                        case "Creator":
                            $named = "§l§cOwner§r§9 " . $player->getName();
                            break;
                        default:
                            $named = "§9" . $player->getName();
                            break;
                    }
                    break;
                case "red":
                    $perm = $this->getServer()->getPluginManager()->getPlugin("PurePerms")->getUserDataMgr()->getGroup($player)->getName();
                    switch ($perm) {
                        case "ViP+":
                            $named = "§l§6VIP+§c " . $player->getName();
                            break;
                        case "MVP+":
                            $named = "§l§bMVP+§c " . $player->getName();
                            break;
                        case "Slayer":
                            $named = "§l§cSlayer§c " . $player->getName();
                            break;
                        case "YouTube":
                            $named = "§l§cY§fT§c " . $player->getName();
                            break;
                        case "Xelper":
                            $named = "§l§bHelper§c " . $player->getName();
                            break;
                        case "Curator":
                            $named = "§l§cOwner§r§c " . $player->getName();
                            break;
                        case "Creator":
                            $named = "§l§cOwner§r§c" . $player->getName();
                            break;
                        default:
                            $named = "§c" . $player->getName();
                            break;
                    }
                    break;
            }
            return $named;
        }

    }
    public function getPos2($v = true)
    {
        $data = $this->getData();
        if (isset($data["pos2"])) {
            $dt = $data["pos2"];
            if (!$v) {
                return new Vector2($dt["x"], $dt["z"]);
            }
            if (isset($data["world"])) {
                $name = $data["world"];
                if ($this->isLoad($name)) {
                    $level = $this->getServer()->getLevelByName($name);
                    $pos = new Position($dt["x"], $dt["y"], $dt["z"], $level);
                    return $pos;
                }
            }
        }
        return null;
    }

    public function getSpawn1()
    {
        $data = $this->getData();
        if (isset($data["spawn1"])) {
            $dt = $data["spawn1"];
            if (isset($data["world"])) {
                $name = $data["world"];
                if ($this->isLoad($name)) {
                    $level = $this->getServer()->getLevelByName($name);
                    $pos = new Position($dt["x"], $dt["y"], $dt["z"], $level);
                    return $pos;
                }
            }
        }
        return null;
    }

    public function getSpawn2()
    {
        $data = $this->getData();
        if (isset($data["spawn2"])) {
            $dt = $data["spawn2"];
            if (isset($data["world"])) {
                $name = $data["world"];
                if ($this->isLoad($name)) {
                    $level = $this->getServer()->getLevelByName($name);
                    $pos = new Position($dt["x"], $dt["y"], $dt["z"], $level);
                    return $pos;
                }
            }
        }
        return null;
    }

    public function getRespawn1($v = true)
    {
        $data = $this->getData();
        if (isset($data["respawn1"])) {
            $dt = $data["respawn1"];
            if (!$v) {
                return new Vector2($dt["x"], $dt["z"]);
            }
            if (isset($data["world"])) {
                $name = $data["world"];
                if ($this->isLoad($name)) {
                    $level = $this->getServer()->getLevelByName($name);
                    $pos = new Position($dt["x"], $dt["y"], $dt["z"], $level);
                    return $pos;
                }
            }
        }
        return null;
    }

    public function getRespawn2($v = true)
    {
        $data = $this->getData();
        if (isset($data["respawn2"])) {
            $dt = $data["respawn2"];
            if (!$v) {
                return new Vector2($dt["x"], $dt["z"]);
            }
            if (isset($data["world"])) {
                $name = $data["world"];
                if ($this->isLoad($name)) {
                    $level = $this->getServer()->getLevelByName($name);
                    $pos = new Position($dt["x"], $dt["y"], $dt["z"], $level);
                    return $pos;
                }
            }
        }
        return null;
    }

    public function getLevel()
    {
        $data = $this->getData();
        if (isset($data["world"])) {
            $name = $data["world"];
            if ($this->isLoad($name)) {
                $level = $this->getServer()->getLevelByName($name);
                return $level;
            }
        }
        return null;
    }

    public function isTeamMode()
    {
        $data = $this->getData();
        if (isset($data["mode"])) {
            if ($data["mode"] == "team" or $data["mode"] == "squad") {
                return true;
            }
        }
        return false;
    }

    public function getSpawn()
    {
        $data = $this->getData();
        if (isset($data["info"])) {
            $dt = $data["info"];
            if (!isset($dt["level"])) {
                return null;
            }
            $pos = new Position($dt["x"], $dt["y"], $dt["z"]);
            if ($this->isLoad($dt["level"])) {
                $level = $this->getServer()->getLevelByName($dt["level"]);
                $pos->setLevel($level);
            } else {
                $this->broadcast("§c§lError§r§c Level " . $dt["level"] . " is not exist!");
                return null;
            }
            return $pos;
        }
        return null;
    }

    public function isInArena($name)
    {
        if ($name instanceof Player) {
            $name = $name->getName();
        }
        $name = strtolower($name);
        if (isset($this->players[$name])) {
            return true;
        }
        return false;
    }

    public function getNecCount()
    {
        $data = $this->getData();
        if (isset($data["mode"])) {
            switch ($data["mode"]) {
                case "solo":
                    return 2;
                case "team":
                    return 4;
                case "squad":
                    return 8;
            }
        }
        return 2;
    }

    public function getLolCount()
    {
        $data = $this->getData();
        if (isset($data["mode"])) {
            switch ($data["mode"]) {
                case "solo":
                    return 1;
                case "team":
                    return 2;
                case "squad":
                    return 4;
            }
        }
        return 1;
    }

    public function onRun($timer = null)
    {
        $nec = $this->getNecCount();
        switch ($this->stat) {
            case self::STAT_WAITING:
                $players = $this->getPlayers();
                if (count($players) >= $nec) {
                    $this->stat = self::STAT_STARTING;
                } else {
                    if (count($players) <= 0) {
                        return true;
                    }
                    $blank = "                                 ";
                    $lol = "$blank $blank §e§lTHE BRIDGE" . "\n\n§r" . "$blank $blank §fИгроков: §a" . $this->getCount() . "/" . $this->getNecCount() . "\n§r" . "$blank $blank §cНабор игроков..." . "\n\n§r" . "$blank $blank §fMode: §a" . $this->getLolCount() . "v" . $this->getLolCount() . "\n\n§r" . "$blank $blank §eАВТО ДОНАТ ИЛИ НАЗВАНИЕ СЕРВЕРА \n\n\n\n\n\n\n";
                    $this->broadcast($lol, 2);
                }
                break;
            case self::STAT_STARTING:
                $players = $this->getPlayers();
                if (count($players) < $nec) {
                    $this->stat = self::STAT_WAITING;
                    $this->time = 30;
                } else {
                    $this->time--;
                    $time = $this->time - 6;
                    if ($time == 10) {
                        $msg = "§eИгра начнётся через §610 §eсекунд!";
                        $this->broadcast($msg, 3);
                    }
                    if ($time == 5) {
                        $msg = "§eИгра начнётся через §c5 §eсекунд!";
                        $this->broadcast($msg, 3);
                        $cd = "§e5";
                        $this->broadcast($cd, 4);
                    }
                    if ($time == 4) {
                        $msg = "§eИгра начнётся через §c4 §eсекунды!";
                        $this->broadcast($msg, 3);
                        $cd = "§e4";
                        $this->broadcast($cd, 4);
                    }
                    if ($time == 3) {
                        $msg = "§eИгра начнётся через §c3 §eсекунды!";
                        $this->broadcast($msg, 3);
                        $cd = "§c3";
                        $this->broadcast($cd, 4);
                    }
                    if ($time == 2) {
                        $msg = "§eИгра начнётся через §c2 §eсекунды!";
                        $this->broadcast($msg, 3);
                        $cd = "§c2";
                        $this->broadcast($cd, 4);
                    }
                    if ($time == 1) {
                        $msg = "§eИгра начнётся через §c1 §eсекунду!";
                        $this->broadcast($msg, 3);
                        $cd = "§c1";
                        $this->broadcast($cd, 4);
                    }
                    if ($time <= 0) {
                        $this->setTeams();
                        $this->stat = self::STAT_START;
                        $this->replaceSpawn();
                        $this->teleportPlayers($this->getPlayers());
                        $msg = "§b»§a▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬§b«" . "\n§r" . "§f§lThe Bridge" . "\n§r" . "§eТвоя команда должна забить 5 очков вражеской команде, чтобы победить!" . "\n§r" . "§b»§a▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬§b«";
                        $this->broadcast($msg, 3);
                    } else {
                        $temp = $this->getTemp($time);
                        $blank = "                                 ";
                        $message = "$blank $blank §e§lTHE BRIDGE" . "\n\n§r" . "$blank $blank §fИгроков: §a" . $this->getCount() . "/" . $this->getNecCount() . "\n\n§r" . "$blank $blank Начнётся через §a" . $temp . " §fсекунд" . "\n\n§r" . "$blank $blank §fMode: §a" . $this->getLolCount() . "v" . $this->getLolCount() . "\n\n§r" . "$blank $blank §eАВТО ДОНАТ ИЛИ НАЗВАНИЕ СЕРВЕРА \n\n\n\n\n\n\n";
                        $this->broadcast($message, 2);
                    }
                }
                break;
            case self::STAT_START:
                $this->time--;
                if ($this->time <= 0) {
                    $this->stat = self::STAT_RUN;
                    $this->broadcast("§aFight!", 4);
                    $this->startGame();
                } else {
                    $this->broadcast("§cВорота откроются через §a" . $this->time . "s...", 4);
                }
                return true;
                break;
            case self::STAT_RUN:
                $level = $this->getLevel();
                if (!is_null($level)) {
                    foreach ($level->getPlayers() as $p) {
                        $blank = "                                 ";
                        $p->sendTip("$blank $blank §e§lTHE BRIDGE" . "\n\n§r" . "$blank $blank §9Команда Синих:§r§e " . $this->points["blue"] . "§7/5" . "\n" . "$blank $blank §cКоманда Красных:§r§e " . $this->points["red"] . "§7/5" . "\n\n" . "$blank $blank §fMode: §a" . $this->getLolCount() . "v" . $this->getLolCount() . "\n" . "$blank $blank §fКарта:§e " . "Towers"/*$this->data["world"]*/ . "\n\n" . "$blank $blank §eАВТО ДОНАТ ИЛИ НАЗВАНИЕ СЕРВЕРА\n\n\n\n\n\n\n");
                    }
                }
                $this->initPlayers();
                break;
            case self::STAT_RESTART:
                $this->time--;
                if ($this->time <= 0) {
                    $this->stat = self::STAT_RUN;
                    $this->broadcast("§aFight!", 4);
                    $this->startGame();
                } else {
                    $this->broadcast("§7Gages open in§a " . $this->time . "s...", 4);
                }
                return true;
                break;
            case self::STAT_VICTORY:
                $this->secs--;
                $this->broadcast($this->lastMessage, 2);
                if ($this->secs <= 0) {
                    $players = $this->getPlayers();
                    foreach ($players as $name => $pl) {
                        $p = $this->plugin->getServer()->getPlayerExact($name);
                        if (is_null($p)) {
                            unset($this->players[$name]);
                            continue;
                        }
                        $this->quit($p);
                    }
                    $this->reset(true);
                }
                break;
        }
    }

    private $secs = 10;

    private function addWin($winner = "blue")
    {
        $players = $this->getPlayers();
        foreach ($players as $name => $pl) {
            $team = $this->getTeam($name);
            if ($team == $winner) {
                $this->getServer()->getPluginManager()->getPlugin("Top")->addWin($name);
            }
        }
    }

    public function getCount()
    {
        $count = count($this->players);
        return $count;
    }

    private $base = ["blue" => 0, "red" => 0];

    private $points = [];
    private $team = null;

    public function getTeamData()
    {
        if (is_null($this->team)) {
            $nec = $this->getNecCount() / 2;
            $this->team = new Team($nec);
        }
        return $this->team;
    }

    public function setTeam($team = "blue", $name)
    {
        $data = $this->getTeamData();

        if ($this->getTeam($name) == $team) {
            return true;
        }
        if ($data->addPlayerTeam($name, $team)) {
            return true;
        }
        return false;
    }

    private function setTeams()
    {
        $data = $this->getTeamData();
        $players = $this->getPlayers();

        if (count($players) <= 0) {
            $this->reset();
            return true;
        }
        foreach ($players as $name => $p) {
            if (!$data->isInTeam($name)) {
                if ($this->setTeam("blue", $name)) {
                } elseif ($this->setTeam("red", $name)) {
                }
            }
        }
    }

    public function getTeam($name)
    {
        $data = $this->getTeamData();
        return $data->getPlayerTeam($name);
    }

    public function isTeam($p1, $p2)
    {
        $data = $this->getTeamData();
        if ($data->isTeam($p1, $p2)) {
            return true;
        }
        return false;
    }

    public function reset($value = true)
    {
        $this->players = [];

        $this->time = 20;
        $this->secs = 10;
        $this->winner = "blue";
        $this->stat = self::STAT_WAITING;

        $this->getTeamData()->reset();
        $this->nametag = [];
        $this->points = $this->base;

        if ($value) {
            $this->resetMap();
        }
    }

    public function close()
    {
        $players = $this->getPlayers();
        if (count($players) <= 0) {
            $this->reset(true);
            return true;
        }
        foreach ($players as $name => $pl) {
            $p = $this->plugin->getServer()->getPlayerExact($name);
            if (!is_null($p)) {
                $this->quit($p);
            } else {
                unset($this->players[$name]);
            }
        }
        $this->reset(true);
    }

    private $lastMessage = "";

    public function broadcast($message, $type = 1)
    {
        $players = $this->getPlayers();
        if (count($players) <= 0) {
            if ($message !== 2) {
                $this->reset();
            }
            return true;
        }
        foreach ($players as $name => $pl) {
            $p = $this->plugin->getServer()->getPlayerExact($name);
            if (is_null($p)) {
                unset($this->players[$name]);
                continue;
            } elseif (!$this->isInArena($p)) {
                unset($this->players[$name]);
                continue;
            }
			
            $this->lastMessage = $message;
            switch ($type) {
                case 1:
                    $p->sendPopup($message);
                    break;
                case 2:
                    $p->sendTip($message);
                    break;
                case 3:
                    $p->sendMessage($message);
                    break;
                case 4:
                    $p->addTitle($message, "", 0, 50, 0);
                    break;
            }
        }
    }

    public function initPlayers()
    {
        $nec = $this->getNecCount() / 2;
        foreach ($this->players as $name => $pl) {
            $p = $this->plugin->getServer()->getPlayerExact($name);
            if (is_null($p)) {
                unset($this->players[$name]);
                continue;
            } elseif (!$this->isInArena($p)) {
                unset($this->players[$name]);
                continue;
            }
        }

        $count = count($this->players);
        if ($count <= 0) {
            $this->reset(true);
            return false;
        } elseif ($count <= $nec) {
            $value = false;
            if ($this->isTeamMode()) {
                $data = $this->players;
                if ($count <= 1) {
                    $value = true;
                } elseif ($this->isTeam(array_shift($data), array_shift($data))) {
                    $value = true;
                }
            } else {
                $value = true;
            }
            if ($value) {
                $data = $this->players;
                $team = $this->getTeam(array_shift($data));

                $msg2 = "§cRED WINS!";
                switch ($team) {
                    case "blue":
                        $msg2 = "§9BLUE WINS!";
                }
                $players = $this->getPlayers();
                foreach ($players as $name => $pl) {
                    $p = $this->plugin->getServer()->getPlayerExact($name);
                    if (is_null($p)) {
                        unset($this->players[$name]);
                        continue;
                    }
                    $this->respawnPlayer($p, false);
                }
                $this->broadcast("$msg2\n  §c{$this->points["red"]} §7- §9{$this->points["blue"]}", 4);
                $this->secs = 10;
                $this->stat = self::STAT_VICTORY;
                $this->addWin($team);
                return true;
            }
        }
    }

    public function startGame($value = true)
    {
        $this->removeY($this->getSpawn1(), true, null, 3);
        $this->removeY($this->getSpawn2(), true, null, 3);
    }

    public function replaceSpawn($value = true)
    {
        $this->removeY($this->getSpawn1(), false);
        $this->removeY($this->getSpawn2(), false);
    }

    public function removeY($pos, $v = true, $dis = null, $ad = 0)
    {
        if ($v == false) {
            foreach ($this->getPlayers() as $name => $pl) {
                $p = $this->getServer()->getPlayerExact($name);
                if (is_null($p)) {
                    unset($this->players[$name]);
                    continue;
                }
                $p->setImmobile(true);
                $this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$p, 'setImmobile'], [false]), 10);
            }
        }
        $level = $this->getLevel();
        if (is_null($dis)) {
            $dis = $this->getNecCount();
            $dis = $dis > 4 ? 4 : $dis;
        }
        $yy = $v ? 3 : 5;
        $yy += $ad;
        for ($x = $pos->x - $dis; $x <= $pos->x + $dis; $x++) {
            for ($y = $pos->y + $yy; $y >= $pos->y - 1; $y--) {
                for ($z = $pos->z + $dis; $z >= $pos->z - $dis; $z--) {
                    if ($v == true) {
                        $level->setBlock(new Vector3($x, $y, $z), Block::get(0));
                    } else {
                        $level->setBlock(new Vector3($x, $y, $z), Block::get(20));
                    }
                }
            }
        }
        if (!$v) {
            $this->removeY($pos->add(0, 1), true, $dis - 1);
        }
    }

    public function teleportPlayers($players)
    {
        $level = $this->getLevel();

        foreach ($players as $name => $pl) {
            $p = $this->getServer()->getPlayerExact($name);
            if (is_null($p)) {
                unset($this->players[$name]);
                continue;
            }

            $named = "§cRed " . $p->getName();
            $pos = $this->getSpawn2();

            $team = $this->getTeam($name);

            switch ($team) {
                case "blue":
                    $named = $this->createBaseColor($p, 2);
                    $pos = $this->getSpawn1();
                    break;
                case "red":
                    $named = $this->createBaseColor($p, 2);
                    $pos = $this->getSpawn2();
                    break;
            }
            if (!isset($this->nametag[$name])) {
                $this->nametag[$name] = $p->getNameTag();
            }

            $p->setNameTag($named);
            $this->addItems($p);

            $p->teleport($pos);

        }
    }

    public function quit(Player $player, $msg = true)
    {
        $name = strtolower($player->getName());
        if (!$this->isInArena($player)) {
            return false;
        }

        $player->setGamemode(Player::SURVIVAL);
        $player->removeAllEffects();
        $player->setMaxHealth(1);
        $player->setHealth($player->getMaxHealth());
        $player->setFood(20);

        $this->getTeamData()->removePlayerTeam($name);

        $inv = $player->getInventory();
        $inv->clearAll();
        $floatingInv = $player->getFloatingInventory();
        $floatingInv->clearAll();
        //$this->getPlugin()->api->openCategory($player, "hub");
        $player->setNameTag($this->getPlugin()->api->createBaseColor($player));
        $level = $this->getServer()->getDefaultLevel();

        unset($this->players[$name]);
        if ($msg) {
            $player->teleport($level->getSafeSpawn());
        }
    }

    public function join(Player $player)
    {
        $nec = $this->getNecCount();
        if ($this->stat == self::STAT_WAITING) {
            if (count($this->players) >= $nec) {
                return false;
            }
        } elseif ($this->stat < 2) {
            if (count($this->players) >= $nec) {
                return false;
            }

        } else {
            return false;
        }
        $player->setGamemode(Player::SURVIVAL);
        $player->removeAllEffects();
        $player->setMaxHealth(20);
        $player->setHealth($player->getMaxHealth());
        $player->setFood(20);
        $player->setAllowFlight(false);
        $player->setFlying(false);

        $inv = $player->getInventory();
        $inv->clearAll();

        $inv->setItem(8, Item::get(355, 14, 1)->setCustomName("§r§cВыйти с арены!"));

        $spawn = $this->getSpawn();
        if (!is_null($spawn)) {
            $player->teleport($spawn);
        }

        $name = strtolower($player->getName());
        $this->players[$name] = $player;

        $this->broadcast("§7{$player->getName()} §eприсоединился (§b{$this->getCount()}§e/§b{$this->getNecCount()}§e)§7!§r", 3);
        return true;
    }


    public function respawnPlayer($p, $v = true)
    {
        $name = strtolower($p->getName());
        $this->addItems($p, $v);

        $team = $this->getTeam($name);
        if (!is_null($team)) {
            switch ($team) {
                case "blue":
                    $pos = $this->getRespawn1();
                    // $p->setImmobile(true);
                    $p->teleport($pos);

                    break;
                case "red":
                    $pos = $this->getRespawn2();
                    //$p->setImmobile(true);
                    $p->teleport($pos);
                    // $this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$p, 'setImmobile'], [false]), 10);
                    break;
            }
        }
    }

    public function addItems($p, $v = true)
    {

        $p->setGamemode(Player::SURVIVAL);
        $p->setHealth($p->getMaxHealth());
        $p->setFood(20);

        if (!$v) {
            $p->removeAllEffects();
            $inv = $p->getInventory();
            $inv->clearAll();

            return true;
        }

        $name = strtolower($p->getName());
        $damage = 14;

        $team = $this->getTeam($name);
        if (!is_null($team)) {
            switch ($team) {
                case "blue":
                    $color = 0x003333ff;
                    $damage = 11;
                    break;
                case "red":
                    $color = 0x00ff3300;
                    $damage = 14;
                    break;
            }
        }
        $inv = $p->getInventory();
        $inv->clearAll();

        $sword = Item::get(267, 0, 1);
        $pickaxe = Item::get(278, 0, 1);
        $bow = Item::get(261, 0, 1);
        $arrows = Item::get(262, 0, 8);
        $gapple = Item::get(322, 0, 8);
        $block = Item::get(159, $damage, 64);

        $inv->setItem(0, $sword);
        $inv->setItem(1, $pickaxe);
        $inv->setItem(2, $bow);

        $inv->setItem(3, $block);
        $inv->setItem(4, $block);
        $inv->setItem(5, $gapple);
        $inv->setItem(32, $arrows);

        $cap = Item::get(298, 0, 1);
        $tempTag = new CompoundTag("", []);
        $tempTag->color = new IntTag("customColor", $color);
        $cap->setCompoundTag($tempTag);

        $chestplate = Item::get(299, 0, 1);
        $tempTag = new CompoundTag("", []);
        $tempTag->color = new IntTag("customColor", $color);
        $chestplate->setCompoundTag($tempTag);

        $leggings = Item::get(300, 0, 1);
        $tempTag = new CompoundTag("", []);
        $tempTag->color = new IntTag("customColor", $color);
        $leggings->setCompoundTag($tempTag);

        $boots = Item::get(301, 0, 1);
        $tempTag = new CompoundTag("", []);
        $tempTag->color = new IntTag("customColor", $color);
        $boots->setCompoundTag($tempTag);

        $inv->setHelmet($cap);
        $inv->setChestplate($chestplate);
        $inv->setLeggings($leggings);
        $inv->setBoots($boots);
    }

    public function getPointPos($p, $v = true)
    {
        $name = strtolower($p->getName());
        $team = $this->getTeam($name);

        if (!is_null($team)) {
            switch ($team) {
                case "blue":
                    if (!$v) {
                        return $this->getPos1();
                    }
                    $pos = $this->getPos2();
                    return $pos;
                case "red":
                    if (!$v) {
                        return $this->getPos2();
                    }
                    $pos = $this->getPos1();
                    return $pos;
            }
        }
    }

    public function addPoint($p)
    {
        $name = strtolower($p->getName());

        $team = $this->getTeam($name);
        if (!is_null($team)) {
            if (isset($this->points[$team])) {
                if ($this->points[$team] >= 5) {
                    return true;
                }
                $this->points[$team]++;
            }
            $msg = "§b»§a▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬§b«" . "\n\n" . "§7Игрок §7{$p->getName()} §7забил очко команде §9Синих! §e(§7Всего очков у этой команды: §a" . $this->points[$team] . "§e)§r\n\n" . "§b»§a▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬§b«";
            $msg2 = "§cRED WINS!";
            switch ($team) {
                case "blue":
                    $msg = "§b»§a▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬§b«" . "\n\n" . "§7Игрок §7{$p->getName()} §7забил очко команде §cКрасных! §e(§7Всего очков у этой команды: §a" . $this->points[$team] . "§e)§r\n\n" . "§b»§a▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬§b«";
                    $msg2 = "§9BLUE WINS!";
            }
            $this->broadcast("$msg", 3);
            if ($this->points[$team] >= 5) {
                $players = $this->getPlayers();
                foreach ($players as $name => $pl) {
                    $p = $this->plugin->getServer()->getPlayerExact($name);
                    if (is_null($p)) {
                        unset($this->players[$name]);
                        continue;
                    }
                    $this->respawnPlayer($p, false);
                }
                $this->broadcast("{$msg2}\n   §c{$this->points["red"]} §7- §9{$this->points["blue"]}", 4);
                $this->secs = 10;
                $this->stat = self::STAT_VICTORY;
                $this->addWin($team);
                return true;
            }

            $this->replaceSpawn();
            $this->teleportPlayers($this->getPlayers());
            foreach ($this->players as $name => $p) {
                if ($this->getTeam($p) == $team) {
                }
            }

            $this->time = 6;
            $this->stat = self::STAT_RESTART;
        }
    }

    public function getPoint($team = "blue")
    {
        if (!isset($this->points[$team])) {
            return 0;
        }
        return $this->points[$team];
    }

    public function getTemp($time)
    {
        $sec = (int)($time % 60);
        $time /= 60;
        $min = (int)($time % 60);
        $time /= 60;
        $timel = (int)($time % 24);
        if ($sec < 10) {
            $sec = "0" . $sec;
        }
        if ($min < 10) {
            $min = "0" . $min;
        }
        if ($timel < 10) {
            $timel = "0" . $timel;
        }
        return "$min:$seg";
    }

    public function isLoad($world)
    {
        if ($this->getServer()->isLevelLoaded($world)) {
            return true;
        }
        if (!$this->getServer()->isLevelGenerated($world)) {
            return false;
        }
        $this->getServer()->loadLevel($world);
        return $this->getServer()->isLevelLoaded($world);
    }
}