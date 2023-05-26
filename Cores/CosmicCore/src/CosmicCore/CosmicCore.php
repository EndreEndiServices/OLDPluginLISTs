<?php
namespace CosmicCore;

use onebone\economyapi\EconomyAPI;
use pocketmine\block\Block;
use pocketmine\inventory\ChestInventory;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\plugin\PluginBase;
use SQLite3;
use pocketmine\{
    Player, Server
};
use pocketmine\command\{
    Command, CommandSender, ConsoleCommandSender
};
use pocketmine\entity\{
    Creature, Effect, Entity, Human, Item as EntityItem, Living
};
use pocketmine\item\{
    Item, enchantment\Enchantment
};
use pocketmine\level\{
    Level, Position
};
use pocketmine\level\particle\{
    AngryVillagerParticle, BubbleParticle, CriticalParticle, DustParticle, EnchantParticle, EnchantmentTableParticle, ExplodeParticle, FlameParticle, FloatingTextParticle, GenericParticle, HappyVillagerParticle, HeartParticle, HugeExplodeParticle, InkParticle, InstantEnchantParticle, ItemBreakParticle, LargeExplodeParticle, LavaDripParticle, LavaParticle, MobSpawnParticle, Particle, PortalParticle, RedstoneParticle, SmokeParticle, SplashParticle, SporeParticle, TerrainParticle, WaterDripParticle, WaterParticle
};
use pocketmine\level\sound\{
    ExpPickupSound, ExplodeSound
};
use pocketmine\utils\{
    Config, TextFormat as TF
};
use pocketmine\tile\{
    Tile, Chest, MobSpawner
};
use pocketmine\nbt\tag\{
    CompoundTag, IntTag, ListTag, StringTag
};
use CosmicCore\Tasks\{
    Envoy\StartEnvoyTask, Envoy\ClearEnvoyTask, CosmicFX\FXTask
};

class CosmicCore extends PluginBase implements \pocketmine\event\Listener
{

    const MOB_SPAWNER = "MobSpawner";
    const DEFAULT_RANK = "•Player•";

    public $config;
    public $data;
    public $disableItems = array();
    private $items = [];
    public $message = "";
    public $messages;
    public $players = array();
    protected $exemptedEntities = [];
    public $blockedcommands = array();
    public $cmd;
    public $db;
    public $dbcreative;
    public $interval = 10;
    public $talked = [];
    public $victim = [];
    public $warnings = [];
    public $queue = [];
    private $commands = [];
    public $using = array();

    public function onEnable()
    {
        @mkdir($this->getDataFolder());
        $fileExistancy = array("config.yml", "chat.yml", "sell.yml", "xyz.yml", "vote.yml");
        foreach ($fileExistancy as $file) {
            if (!file_exists($this->getDataFolder() . $file)) {
                @mkdir($this->getDataFolder());
                file_put_contents($this->getDataFolder() . $file, $this->getResource($file));
            }
        }

        if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
        if (!is_dir($this->getDataFolder() . "players/")) mkdir($this->getDataFolder() . "players/");
        if (!is_dir($this->getDataFolder() . "vaults/")) mkdir($this->getDataFolder() . "vaults/");
        if (!is_dir($this->getDataFolder() . "/bounties")) mkdir($this->getDataFolder() . "/bounties");
        if (!is_dir($this->getDataFolder() . "Lists/")) mkdir($this->getDataFolder() . "Lists/");

        /* Configuring configuration files */
        $this->voteReward = new Config($this->getDataFolder() . "vote.yml", Config::YAML);
        $this->data = new Config($this->getDataFolder() . "obsidian.json", Config::JSON);
        $this->backups = new Config($this->getDataFolder() . "backups.txt", Config::ENUM);
        $this->dbcreative = new SQLite3($this->getDataFolder() . "blocks.bin");
        $this->xyz = new Config($this->getDataFolder() . "xyz.yml", Config::YAML);
        $this->particleData = new Config($this->getDataFolder() . "players.yml", Config::YAML, array());

        /* Specifying children files */
        $this->reward = new Reward($this);
        $this->ce = new CustomEnchantments($this);
        $this->stackHeartbeat = new StackHeartbeat($this);
        $this->spaceReward = new SpaceReward($this);

        /* Specifying external plugin routes */
        $this->essentialsPE = $this->getServer()->getPluginManager()->getPlugin("EssentialsPE");
        $this->purePerms = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
        $this->pureChat = $this->getServer()->getPluginManager()->getPlugin("PureChat");

        $this->dbcreative->exec("CREATE TABLE IF NOT EXISTS blocks(world varchar(60), location varchar(10000000));");

        $this->getServer()->getScheduler()->scheduleRepeatingTask(new SpawnFly($this, 20), 20);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new Scheduler($this, $this->interval), 20);

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        
        $saveResources = array("obsidian.json", "values.txt", "vote.yml", "limitedcreative.yml");
        foreach ($saveResources as $resource) {
            $this->saveResource($resource);
        }
        $this->lists = [];

        /* Functions executed onEnable() */
        $this->spawnHolo();
        $this->reloadVoteReward();
    }

    public function onDisable()
    {
        $this->data->save(true);
        $this->particleData->save(true);
        $this->voteReward->save(true);
    }

    public function isCustomEnchant($enchant)
    {
        if ($enchant > 99) return true;
        else return false;
    }

    public function isDonator($p)
    {
        $group = $this->purePerms->getUserDataMgr()->getGroup($p);
        return $group->getName() != self::DEFAULT_RANK;
    }

    public function p($c, $s)
    {
        if ($c === "6") $a = TF::GOLD;
        if ($c === "a") $a = TF::GREEN;
        if ($c === "c") $a = TF::RED;
        if ($c === "e") $a = TF::YELLOW;
        if ($c === "f") $a = TF::WHITE;
        if ($c === "r") $a = "";
        if ($s === "text") {
            return TF::BOLD . TF::DARK_GRAY . "[" . TF::AQUA . "Cosmic" . TF::LIGHT_PURPLE . "PE" . TF::DARK_GRAY . "] " . TF::RESET . $a;
        } elseif ($s === "!") {
            return TF::BOLD . $a . "(!) " . TF::RESET . $a;
        }
    }

    public function strike($p)
    {
        $s = new \pocketmine\network\protocol\AddEntityPacket();
        $s->type = 93;
        $s->eid = Entity::$entityCount++;
        $s->metadata = array();
        $s->speedX = 0;
        $s->speedY = 0;
        $s->speedZ = 0;
        $s->yaw = $p->getYaw();
        $s->pitch = $p->getPitch();
        $s->x = $p->x;
        $s->y = $p->y;
        $s->z = $p->z;
        foreach ($p->getLevel()->getPlayers() as $a) {
            $a->dataPacket($s);
        }
    }

    public function setTime(Player $player)
    {
        $msg = $this->p("c", "!") . "You have entered combat. Do not log out for 10s.";
        if (isset($this->players[$player->getName()])) {
            if ((time() - $this->players[$player->getName()]) > $this->interval) {
                $player->sendMessage($msg);
            }
        } else {
            $player->sendMessage($msg);
        }
        $this->players[$player->getName()] = time();
    }

    public function allowed()
    {
        $array = array("muqsit" => 1, "muqsitrayyanxo" => 2, "thecatsmeowow" => 3, "monkeydluffy368" => 4, "people_1" => 5, "snake" => 6, "ranger2341" => 7);
        if ($this->getConfig()->get("allow-clans") == true) {
            return $array;
        } else {
            return array("muqsit" => 1, "muqsitrayyanxo" => 2, "thecatsmeowow" => 3, "monkeydluffy368" => 4, "people_1" => 5, "snake" => 6, "ranger2341" => 7, "xstrqfe" => 8);
        }
    }

    public function consoleCmd($cmd)
    {
        $this->getServer()->dispatchCommand(new ConsoleCommandSender, $cmd);
    }

    public function titles()
    {
        return array("Noob", "Trollolol", "Poofless", "Lavamob", "Cosmonaut", "Hax0r", "Lagg", "$$$", "Unraidable", "TeamWOOF", "GetTheCamera", "Enchanter", "Ezpz", "Salty");
    }

    public function unsetTitle($p)
    {
        $this->pureChat->unsetTitle($p);
    }

    public function setTitle($p, $t)
    {
        $this->pureChat->setTitle($p, $t);
    }

    public function nutrition($l, $p)
    {
        $basic = 0.0025;
        $multiply = $l === 5 ? 4.4 : $l;
        $add = $basic + $multiply * $basic;
        $p->setFood($p->getFood() + $add);
    }

    public function isAtSpawn($p)
    {
        $xyz = new Config($this->getDataFolder() . "xyz.yml", Config::YAML);
        $xx1 = $xyz->get("spawnX1");
        $xx2 = $xyz->get("spawnX2");
        $yy1 = $xyz->get("spawnY1");
        $yy2 = $xyz->get("spawnY2");
        $zz1 = $xyz->get("spawnZ1");
        $zz2 = $xyz->get("spawnZ2");
        if ($p->getX() >= $xx1 && $p->getX() <= $xx2 && $p->getY() >= $yy1 && $p->getY() <= $yy2 && $p->getZ() >= $zz1 && $p->getZ() <= $zz2) {
            return true;
        } else {
            return false;
        }
    }

    public function isAtWarzone($p)
    {
        $xyz = new Config($this->getDataFolder() . "xyz.yml", Config::YAML);
        $xx1 = $xyz->get("protX1");
        $xx2 = $xyz->get("protX2");
        $zz1 = $xyz->get("protZ1");
        $zz2 = $xyz->get("protZ2");
        if ($p->getX() >= $xx1 && $p->getX() <= $xx2 && $p->getZ() >= $zz1 && $p->getZ() <= $zz2) {
            return true;
        } else {
            return false;
        }
    }

    public function titleRetrieve($p, $n)
    {
        if ($n === "unlocked") {
            $p->sendMessage($this->p("6", "text") . TF::GOLD . "Your have unlocked a title. Use " . TF::YELLOW . "/title retrieve" . TF::GOLD . " to retreive your title. Make sure you have some free space in your inventory.");
        }
        foreach ($this->titles() as $ar) if ($n === $ar) {
            $paper = Item::get(339, 2394, 1);
            $paper->setCustomName(TF::BOLD . TF::AQUA . "Cosmic Title " . TF::RESET . TF::GRAY . "(Right Click)\n" . TF::LIGHT_PURPLE . "Title: " . TF::WHITE . "$n");
            $p->getInventory()->addItem($paper);
        }
    }

    public function title($p, $title)
    {
        $array = $this->titles();
        $this->setTitle($p, $title);
    }

    public function filterBadwords($text, array $badwords, $replaceChar = '*')
    {
        return preg_replace_callback(
            array_map(function ($w) {
                return '/\b' . preg_quote($w, '/') . '\b/i';
            }, $badwords),
            function ($match) use ($replaceChar) {
                return str_repeat($replaceChar, strlen($match[0]));
            },
            $text
        );
    }

    public function spawnHolo()
    {
        $level = $this->getServer()->getLevelByName("world");
        $holo = $this->getConfig();
        $x = $holo->get("X");
        $y = $holo->get("Y");
        $z = $holo->get("Z");
        $text = $holo->get("TEXT");
        $particle = new FloatingTextParticle(new Vector3($x, $y, $z), $text);
        $level->addParticle($particle);
    }

    public function getPlayerParticle(Player $player)
    {
        $t = $this->particleData->getAll();
        if ($t[$player->getName()]["particle"] !== null) return $t[$player->getName()]["particle"];
        else return "null";
    }

    public function setParticle(Player $player, $particle)
    {
        $t = $this->particleData->getAll();
        $t[$player->getName()]["particle"] = $particle;
        $this->particleData->setAll($t);
        $this->particleData->save();
    }

    public function getParticle($name, Vector3 $pos)
    {
        switch ($name) {
            case "angry":
            case "angryvillager":
                return new AngryVillagerParticle($pos);
            case "enchant":
            case "enchantmenttable":
                return new EnchantmentTableParticle($pos);
            case "twinkle":
            case "happyvillager":
                return new HappyVillagerParticle($pos);
            case "hugeexplode":
                return new HugeExplodeParticle($pos);
            case "generic":
                return new GenericParticle($pos);
            case "magician":
            case "instantenchant":
                return new InstantEnchantParticle($pos);
            case "largeexplode":
                return new LargeExplodeParticle($pos);
            case "spawn":
                return new MobSpawnParticle($pos);
            case "explode":
                return new ExplodeParticle($pos);
            case "bubble":
                return new BubbleParticle($pos);
            case "splash":
                return new SplashParticle($pos);
            case "water":
                return new WaterParticle($pos);
            case "crit":
            case "critical":
                return new CriticalParticle($pos);
            case "spell":
                return new EnchantParticle($pos);
            case "dripwater":
                return new WaterDripParticle($pos);
            case "driplava":
                return new LavaDripParticle($pos);
            case "townaura":
            case "spore":
                return new SporeParticle($pos);
            case "portal":
                return new PortalParticle($pos);
            case "flame":
                return new FlameParticle($pos);
            case "lava":
                return new LavaParticle($pos);
            case "reddust":
            case "redstone":
                return new RedstoneParticle($pos, 1);
            case "snowballpoof":
            case "snowball":
                return new ItemBreakParticle($pos, Item::get(Item::SNOWBALL));
            case "heart":
                return new HeartParticle($pos, 0);
            case "ink":
                return new InkParticle($pos, 0);
        }
        return null;
    }

    public function onCommand(CommandSender $issuer, Command $cmd, $label, array $args)
    {
        switch (strtolower($cmd->getName())) {
            case "privateopen":
                if ($issuer->hasPermission("cosmicpe.privateopen")){
                    if($issuer->hasPermission("cosmicpe.privateopen.on")){
                        $this->consoleCmd("unsetuperm ".$issuer->getName()." cosmicpe.privateopen.on");
                        $issuer->sendMessage($this->p("a","!")."Enabled envoy logging.");
                    }else{
                        $this->consoleCmd("setuperm ".$issuer->getName()." cosmicpe.privateopen.on");
                        $issuer->sendMessage($this->p("c","!")."Disabled envoy logging.");
                    }
                }
                break;
            case "cc":
                $issuer->sendMessage("Health: " . $issuer->getHealth() . "/" . $issuer->getMaxHealth());
                if ($issuer->craftingType = 0) $issuer->craftingType = 1;
                break;
            case "vote":
                if (isset($args[0]) && strtolower($args[0]) == "help") {
                    $issuer->sendMessage(TF::AQUA . "---" . TF::BOLD . "[" . TF::RESET . TF::YELLOW . "Vote Help Menu" . TF::BOLD . TF::AQUA . "]" . TF::RESET . "---");
                    $issuer->sendMessage(TF::GREEN . "/vote " . TF::GRAY . "- " . TF::YELLOW . "Claim your vote reward.");
                    $issuer->sendMessage(TF::GREEN . "/vote tutorial " . TF::GRAY . "- " . TF::YELLOW . "Find out how to vote.");
                    $issuer->sendMessage(TF::GREEN . "/vote where " . TF::GRAY . "- " . TF::YELLOW . "Find out where to vote.");
                    $issuer->sendMessage(TF::GREEN . "/vote rewards " . TF::GRAY . "- " . TF::YELLOW . "Find out what you get when you vote.");
                    return true;
                }
                if (isset($args[0]) && strtolower($args[0]) == "tutorial") {
                    $issuer->sendMessage(TF::AQUA . "---" . TF::BOLD . "[" . TF::RESET . TF::YELLOW . "Tutorial for noobs" . TF::BOLD . TF::AQUA . "]" . TF::RESET . "---\n" . TF::GREEN . "1. " . TF::RESET . TF::YELLOW . "Visit our vote links (" . TF::AQUA . "/vote where" . TF::YELLOW . ").\n" . TF::BOLD . TF::GREEN . "2. " . TF::RESET . TF::YELLOW . "Type in your username on the website(COMPULSORY).\n" . TF::BOLD . TF::GREEN . "3. " . TF::RESET . TF::YELLOW . "Solve the captcha on the website. \n" . TF::BOLD . TF::GREEN . "4. " . TF::RESET . TF::YELLOW . "Click the VOTE button.\n" . TF::BOLD . TF::GREEN . "5. " . TF::RESET . TF::YELLOW . "Come back to this server and type " . TF::AQUA . "/vote " . TF::YELLOW . "to earn your reward.");
                    return true;
                }
                if (isset($args[0]) && strtolower($args[0]) == "where") {
                    $issuer->sendMessage(TF::AQUA . "---" . TF::BOLD . "[" . TF::RESET . TF::YELLOW . "Our Vote Websites" . TF::BOLD . TF::AQUA . "]" . TF::RESET . "---\n" . TF::BOLD . TF::GREEN . "1. " . TF::RESET . TF::AQUA . "bit.ly/votecosmicpe\n" . TF::BOLD . TF::GREEN . "2. " . TF::RESET . TF::AQUA . "bit.ly/votemcpe");
                    return true;
                }
                if (isset($args[0]) && strtolower($args[0]) == "rewards") {
                    $config = $this->voteReward->getAll();
                    $issuer->sendMessage($config["RewardMessage"]);
                    return true;
                }
                if (isset($args[0]) && strtolower($args[0]) == "reload") {
                    if ($issuer->hasPermission("votereward.command.reload")) {
                        $this->reloadVoteReward();
                        $issuer->sendMessage($this->p("a", "text") . "Reloaded.");
                        break;
                    }
                    $issuer->sendMessage("You do not have permission to use this subcommand.");
                    break;
                }
                if (in_array(strtolower($issuer->getName()), $this->queue)) {
                    $issuer->sendMessage($this->p("a", "text") . "Chill! We are investigating your vote claim!");
                    break;
                }
                $this->queue[] = strtolower($issuer->getName());
                $requests = [];
                foreach ($this->lists as $list) {
                    if (isset($list["check"]) && isset($list["claim"])) $requests[] = new ServerListQuery($list["check"], $list["claim"]);
                }
                $query = new RequestThread(strtolower($issuer->getName()), $requests);
                $this->getServer()->getScheduler()->scheduleAsyncTask($query);
                break;
            case "sec":
                if (!$issuer->hasPermission("cosmicpe.security.command.security")) {
                    return false;
                }
                if (isset($args[0])) {
                    switch (strtolower($args[0])) {
                        case "help":
                            $commands = [
                                "help" => "Shows all CosmicPE security commands",
                                "list" => "Lists all backup players",
                                "restore" => "Restores OP status of all online players listed in backup.txt"
                            ];
                            $issuer->sendMessage("CosmicPE Security commands:");
                            foreach ($commands as $name => $description) $issuer->sendMessage("/security " . $name . ": " . $description);
                            break;
                        case "l":
                        case "list":
                            $this->sendBackups($issuer);
                            break;
                        case "r":
                        case "restore":
                            if ($this->isBackupPlayer($issuer->getName())) {
                                $this->restoreOps();
                            } else {
                                $issuer->sendMessage($this->getConfig()->get("noPermissionMessage"));
                            }
                            break;
                        default:
                            $issuer->sendMessage("Usage: /security <sub-command> [parameters]");
                            break;
                    }
                } else {
                    $commands = [
                        "help" => "Shows all CosmicPE security commands",
                        "list" => "Lists all backup players",
                        "restore" => "Restores OP status of all online players listed in backup.txt"
                    ];
                    $issuer->sendMessage("CosmicPE Security commands:");
                    foreach ($commands as $name => $description) $issuer->sendMessage("/security " . $name . ": " . $description);
                }
                break;
            case "lag":
                if (!$issuer->hasPermission("cosmicpe.clearlagg.command.clearlagg")) return false;
                if (isset($args[0])) switch ($args[0]) {
                    case "tile":
                        if(!$issuer instanceof Player) $level = $this->getServer()->getLevelByName("world");
                        else $level = $issuer->getLevel();
                        foreach($level->getTiles() as $tile){
                            if($this->isAtWarzone($tile)) $level->removeTile($tile);
                        }
                        $issuer->sendMessage($this->p("c","text") . "Removed all tiles in the warzone.");
                        break;
                    case "not":
                        $issuer->sendMessage($this->p("c", "text") . "Removed " . TF::GREEN . ($d = $this->removeMobs()) . TF::YELLOW . " mob" . ($d == 1 ? "" : "§es") . TF::RED . " and " . TF::GREEN . ($d = $this->removeEntities()) . TF::AQUA . " entit" . ($d == 1 ? "y" : "ies") . TF::RED . ".");
                        break;
                    case "clearall":
                        $issuer->sendMessage($this->p("c", "text") . "Removed " . TF::GREEN . ($d = $this->removeAllMobs()) . TF::YELLOW . " mob" . ($d == 1 ? "" : "§es") . TF::RED . " and " . TF::GREEN . ($d = $this->removeEntities()) . TF::AQUA . " entit" . ($d == 1 ? "y" : "ies") . TF::RED . ".");
                        break;
                    default:
                        return false;
                }
                break;
            case "task":
                foreach ($this->getServer()->getOnlinePlayers() as $on) {
                    if (!$on->hasPermission("cosmicpe.title.earned0") && !$on->hasPermission("cosmicpe.title.noob")) {
                        $this->titleRetrieve($on, "unlocked");
                    }
                    if (!$on->hasPermission("cosmicpe.title.earned1")) {
                        if (EconomyAPI::getInstance()->myMoney($on) > 1000000) {
                            $this->titleRetrieve($on, "unlocked");
                        }
                    }
                }
                $this->restoreOps();
                break;
            case "title":
                $blockedTitles = array("unequip", "retrieve");
                if (!isset($args[0])) {
                    $issuer->sendMessage($this->p("6", "text") . "Your titles:\n");
                    $array = $this->titles();
                    foreach ($array as $ar) {
                        $arPermColor = TF::RED;
                        $strAr = strtolower($ar);
                        if ($issuer->hasPermission("cosmicpe.title." . $strAr)) {
                            $arPermColor = TF::GREEN;
                        }
                        $issuer->sendMessage(TF::GRAY . "- " . $arPermColor . $ar);
                    }
                }
                if (isset($args[0])) {
                    $rraY = $this->titles();
                    if ($args[0] === "unequip") {
                        $this->unsetTitle($issuer);
                        $issuer->sendMessage($this->p("a", "text") . "Title unequipped.");
                    } elseif ($args[0] === "retrieve") {
                        if (!$issuer->hasPermission("cosmicpe.title.earned1") && EconomyAPI::getInstance()->myMoney($issuer) > 1000000) {
                            $this->consoleCmd("setuperm " . $issuer->getName() . " cosmicpe.title.earned1");
                            $this->titleRetrieve($issuer, "$$$");
                            $issuer->sendMessage($this->p("a", "text") . "Title retrieved. Please check your inventory.");
                        } elseif (!$issuer->hasPermission("cosmicpe.title.earned0")) {
                            $this->consoleCmd("setuperm " . $issuer->getName() . " cosmicpe.title.earned1");
                            $this->titleRetrieve($issuer, "Noob");
                            $issuer->sendMessage($this->p("a", "text") . "Title retrieved. Please check your inventory.");
                        } else {
                            $issuer->sendMessage($this->p("c", "text") . "You don't have any pending titles.");
                        }
                    } elseif (!in_array($args[0], $blockedTitles)) {
                        $theTitle = strtolower($args[0]);
                        $theArray = array_map('strtolower', $this->titles());
                        if (in_array($theTitle, $theArray)) {
                            if ($issuer->hasPermission("cosmicpe.title." . $theTitle)) {
                                $key = array_search($theTitle, $theArray);
                                $this->title($issuer, $this->titles()[$key]);
                                $issuer->sendMessage($this->p("a", "text") . "Title successfully changed to :" . TF::GOLD . $this->titles()[$key]);
                            }
                        }
                    }
                }
                break;
            case "chest":
                $chest = Item::get(Item::CHEST, 101, 1);
                $chest->setCustomName(TF::RESET . TF::WHITE . TF::BOLD . "Simple Cosmic Chest " . TF::RESET . TF::GRAY . "(Right Click)\n" . TF::GRAY . "A cache of equipment packaged by\n" . TF::GRAY . "the Intergalactic Cosmonaut Station.");
                $issuer->getInventory()->addItem($chest);
                break;
            case "envoy":
                /* Envoys can only be spawned via ConsoleCommandSender */
                if ($issuer instanceof Player && $issuer->hasPermission("envoy")) {
                    $this->sendEnvoyStats($issuer);
                    return;
                }
                if (!$issuer instanceof Player) {
                    if (strtolower($args[0]) == "start") {
                        $this->getServer()->getScheduler()->scheduleAsyncTask(new StartEnvoyTask());
                        break;
                    }
                    if (strtolower($args[0]) == "nocheat") {
                        $this->getServer()->getScheduler()->scheduleAsyncTask(new ClearEnvoyTask($issuer));
                        break;
                    }
                }
                break;
            case "pv":
                $pvrefix = TF::DARK_GRAY."[".TF::BOLD.TF::AQUA."Cosmic".TF::LIGHT_PURPLE."Vaults".TF::RESET.TF::DARK_GRAY."] ";
                if ($this->isAtSpawn($issuer)) {
                    $issuer->sendMessage($pvrefix . TF::RED . "You cannot access vaults while at spawn! Please go further.");
                    return;
                }
                if ($issuer->getAllowFlight() || $issuer->isCreative() || !$issuer->isOnGround()) {
                    $issuer->sendMessage($pvrefix . TF::RED . "You cannot access private vaults while fly mode is enabled.");
                    return false;
                }
                if ($this->hasPrivateVault($issuer)) {
                    if (!isset($args[0])) {
                        $issuer->sendMessage($pvrefix . TF::WHITE . "/pv <number>");
                        return false;
                    }
                    if (empty($args[0])) {
                        if ($issuer->hasPermission("cosmicpe.vault.1")) {
                            $args[0] = 1;
                            $issuer->addWindow($this->loadVault($issuer, 1));
                            $issuer->sendMessage($pvrefix . TF::GRAY . "Opening vault " . TF::GREEN . "#1" . TF::GRAY . "...");
                            $this->using[strtolower($issuer->getName())] = (int)$args[0];
                            return true;
                        } else {
                            $issuer->sendMessage($pvrefix . "You don't have permission to access that vault!");
                            return true;
                        }
                    } else {
                        if ($args[0] < 1 || $args[0] > 25) {
                            if (!isset($this->allowed()[strtolower($issuer->getName())]))
                                $issuer->sendMessage($pvrefix . TF::WHITE . "/pv <1-25>");
                            return true;
                        } else {
                            if ($issuer->hasPermission("cosmicpe.vault." . $args[0])) {
                                $issuer->addWindow($this->loadVault($issuer, $args[0]));
                                $issuer->sendMessage($pvrefix . TF::GRAY . "Opening vault " . TF::GREEN . "#" . $args[0] . TF::GRAY . "...");
                                $this->using[strtolower($issuer->getName())] = (int)$args[0];
                                return true;
                            } else {
                                $issuer->sendMessage($pvrefix . "You don't have permission to access that vault!");
                                return true;
                            }
                        }
                    }
                } else {
                    $issuer->sendMessage($pvrefix . TF::YELLOW . "Setting up your vault..");
                    for ($i = 0; $i < 26; $i++) {
                        $this->createVault($issuer, $i);
                    }
                    $issuer->sendMessage($pvrefix . TF::YELLOW . "Vault created, run " . TF::AQUA . "/pv 1" . TF::YELLOW . " to open your first vault!");
                    return true;
                }
                break;
            case "sell":
                $cfg = new Config($this->getDataFolder() . "sell.yml", Config::YAML);
                if ($issuer->hasPermission("cosmicpe.sell")) {

                    if ($issuer->isCreative()) {
                        $issuer->sendMessage(TF::RED . TF::BOLD . "Error: " . TF::RESET . TF::DARK_RED . "You are in creative mode.");
                        return false;
                    }

                    switch (strtolower($args[0])) {
                        case "hand":
                            $item = $issuer->getInventory()->getItemInHand();
                            $itemId = $item->getId();

                            if ($item->getId() === 0) {
                                $issuer->sendMessage(TF::RED . TF::BOLD . "Error: " . TF::RESET . TF::DARK_RED . "You aren't holding any blocks/items.");
                                return false;
                            }

                            if ($cfg->get($itemId) == null) {
                                $issuer->sendMessage(TF::RED . TF::BOLD . "Error: " . TF::RESET . TF::DARK_RED . "This block/item cannot be sold.");
                                return false;
                            }

                            EconomyAPI::getInstance()->addMoney($issuer, $cfg->get($itemId) * $item->getCount());
                            $issuer->getInventory()->removeItem($item);
                            $price = $cfg->get($item->getId()) * $item->getCount();
                            $issuer->sendMessage($this->p("a", "!") . "$" . $price . " has been added to your account.");
                            $issuer->sendMessage(TF::GREEN . "Sold for " . TF::RED . "$" . $price . TF::GREEN . " (" . $item->getCount() . " " . $item->getName() . " at $" . $cfg->get($itemId) . " each).");
                            break;
                        case "all":
                            $cfg = new Config($this->getDataFolder() . "sell.yml", Config::YAML);
                            $items = $issuer->getInventory()->getContents();
                            foreach ($items as $item) {
                                if ($cfg->get($item->getId()) !== null && $cfg->get($item->getId()) > 0) {
                                    $price = $cfg->get($item->getId()) * $item->getCount();
                                    EconomyAPI::getInstance()->addMoney($issuer, $price);
                                    $issuer->sendMessage($this->p("a", "!") . "Sold for " . TF::RED . "$" . $price . TF::GREEN . " (" . $item->getCount() . " " . $item->getName() . " at $" . $cfg->get($item->getId()) . " each).");
                                    $issuer->getInventory()->removeItem($item);
                                }
                            }
                            break;
                        default:
                            $issuer->sendMessage($this->p("c", "!") . "Cosmoverse Online Market");
                            $issuer->sendMessage(TF::RED . "- " . TF::DARK_RED . "/sell hand " . TF::GRAY . "- Sell the item that's in your hand.");
                            $issuer->sendMessage(TF::RED . "- " . TF::DARK_RED . "/sell all " . TF::GRAY . "- Sell everything you have on you.");
                            return true;
                            break;
                    }
                } else {
                    $issuer->sendMessage(TF::RED . TF::BOLD . "Error: " . TF::RESET . TF::RED . "You must purchase a rank to use this command");
                }
                break;
            case "security":
                $backupCount = 0;
                $backupNames = "";

                foreach (file($this->getDataFolder() . "backups.txt", FILE_SKIP_EMPTY_LINES) as $name) {
                    $backupNames .= trim($name) . ", ";
                    $backupCount++;
                }

                $issuer->sendMessage(TF::DARK_GRAY . "[" . TF::BOLD . TF::AQUA . "Cosmic" . TF::LIGHT_PURPLE . "Security" . TF::RESET . TF::DARK_GRAY . "] " . TF::YELLOW . "Live security status.\n" . TF::YELLOW . "Number of players: " . TF::GOLD . $backupCount . "\n" . TF::YELLOW . "Names of players: " . TF::GOLD . substr($backupNames, 0, -2));
                return true;
                break;
            case "rules":
                $getRules = $this->getConfig()->get("rules");
                foreach ($getRules as $rules) $issuer->sendMessage($rules);
                break;
            case "opme":
                if ($issuer->isOp()) {
                    $message = $this->getConfig()->getNested("already-op");
                    $issuer->sendMessage("$message");
                    return true;
                }

                if ($this->getConfig()->getNested("enable-fire")) {
                    $issuer->setOnFire(10);
                }

                $message = $this->getConfig()->getNested("cannot-op-yourself");
                $issuer->kick("404 Error\nYou were trolled!");
                $fine = $this->getConfig()->getNested("fine");
                EconomyAPI::getInstance()->reduceMoney($issuer, $fine);
                return true;
                break;
            case "bountyget":
                if (isset($args[0])) {
                    $this->getBounty($args[0], $issuer);
                    return true;
                } else {
                    $issuer->sendMessage(TF::DARK_RED . "/bountyget " . TF::GRAY . "<player>");
                    return true;
                }
                break;
            case "bounty":
                $prefix = TF::DARK_GRAY . "[" . TF::BOLD . TF::AQUA . "Cosmic" . TF::LIGHT_PURPLE . "Bounty" . TF::RESET . TF::DARK_GRAY . "] " . TF::RED;
                if (isset($args[0]) && isset($args[1])) {

                    if ($player = $this->getServer()->getPlayer($args[0])) {
                        $moni = EconomyAPI::getInstance()->myMoney($issuer);
                        $pName = $issuer->getName();
                        $futureKill = $player->getName();

                        if (!file_exists($this->getDataFolder() . "/bounties/" . strtolower($futureKill) . ".yml")) {
                            $cfg = new Config($this->getDataFolder() . "/bounties/" . strtolower($futureKill) . ".yml", Config::YAML);
                            $cfg->set("bounty", 0);
                            $cfg->save();
                        }

                        if ($args[1] < 25000) {
                            $issuer->sendMessage($prefix . "The minimum bounty to place on a player is $25,000.");
                            return true;
                        }

                        if (strpos($args[1], ".") !== false) {
                            $issuer->sendMessage($prefix . "Please type in a valid number.");
                            return true;
                        }

                        if (!is_numeric($args[1])) {
                            $issuer->sendMessage($prefix . "You specified an invalid bounty amount.");
                            return true;
                        }

                        if ($moni < $args[1]) {
                            $issuer->sendMessage($prefix . "You don't have enough balance to put a $" . $args[1] . " bounty on " . $args[0] . "!");
                            return true;
                        }

                        if ($futureKill === $pName or $pName === $futureKill) {
                            $issuer->sendMessage($prefix . "You cannot put a bounty on yourself!");
                            return true;
                        }

                        EconomyAPI::getInstance()->reduceMoney($pName, $args[1]);
                        $this->getServer()->broadcastMessage(TF::DARK_GRAY . TF::BOLD . "<" . TF::AQUA . "Cosmic" . TF::WHITE . "Bounty" . TF::DARK_GRAY . "> " . TF::RESET . TF::WHITE . $pName . TF::AQUA . " has put a " . TF::WHITE . "$" . $args[1] . TF::RESET . TF::AQUA . " bounty on the head of " . TF::WHITE . $futureKill . "!");
                        $this->addBounty($player->getName(), $args[1]);
                        return true;
                    } else {
                        $issuer->sendMessage($prefix . "Player is offline.");
                        return true;
                    }
                } else {
                    $issuer->sendMessage("" . $cmd->getUsage());
                    return true;
                }
                break;
            case "gms":
                if ($issuer->hasPermission("cosmicpe.gamemodes")) {
                    $issuer->sendMessage($this->p("a", "text") . "Switched gamemode to survival mode");
                    $issuer->setGamemode(0);
                }
                break;
            case "gmc":
                if ($issuer->hasPermission("cosmicpe.gamemodes")) {
                    $issuer->sendMessage($this->p("a", "text") . "Switched gamemode to creative mode");
                    $issuer->setGamemode(1);
                }
                break;
            case "withdraw":
                $money = EconomyAPI::getInstance()->myMoney($issuer);
                if (!isset($args[0])) {
                    $issuer->sendMessage(TF::RED . "/withdraw <$>\n" . TF::GRAY . "This will create a cosmic note with an equivalent value of <$>.");
                    return false;
                }

                if ($args[0] === 2394) {
                    $issuer->sendMessage(TF::DARK_RED . "Error: " . TF::RED . "Error during redemption. If the problem still persists, try redeeming another amount.");
                    return true;
                }

                if (($args[0]) > 32000) {
                    $issuer->sendMessage(TF::DARK_RED . "Error: " . TF::RED . "You cannot redeem more than" . TF::BOLD . " $32000 " . TF::RESET . TF::DARK_RED . "at once!");
                    return true;
                }

                if (!is_numeric($args[0]) || $args[0] < 1) {
                    $issuer->sendMessage(TF::RED . "You specified an invalid withdrawal amount.");
                    return true;
                }
                if ($money < $args[0]) {
                    $issuer->sendMessage(TF::RED . "You do not have enough $ to sign a cosmic note that large!");
                } else {
                    EconomyAPI::getInstance()->setMoney($issuer, $money - $args[0]);
                    $item = Item::get(339, $args[0], 1);
                    $item->setCustomName(TF::AQUA . TF::BOLD . "Cosmic Note" . TF::RESET . TF::GRAY . " (Right Click)\n" . TF::LIGHT_PURPLE . "Value " . TF::WHITE . $args[0] . "\n" . TF::LIGHT_PURPLE . "Signer " . TF::GRAY . $issuer->getName());
                    $issuer->getInventory()->addItem($item);
                    $issuer->sendMessage(TF::RED . TF::BOLD . "- $" . $args[0] . TF::RESET . "\n" . TF::GREEN . "You have signed a cosmic note for $" . $args[0] . "!");
                }
                break;
            case "fx":
                if ($issuer->hasPermission("cosmicpe.fx")) {
                    if (!isset($args[0])) {
                        $issuer->sendMessage($this->p("r", "text") . TF::WHITE . "/fx " . TF::GOLD . "<" . TF::GRAY . "set" . TF::GOLD . "/" . TF::GRAY . "clear" . TF::GOLD . "/" . TF::GRAY . "list" . TF::GOLD . ">");
                        $issuer->sendMessage(TF::GRAY . "CosmicFX is a fun-to-use command that features nearly every particle that is in the game. Try out" . TF::ITALIC . "/fx set bubble");
                        return false;
                    }
                    if (isset($args[0])) {
                        switch ($args[0]) {
                            case "clear":
                                $this->setParticle($issuer, "clear");
                                $issuer->sendMessage($this->p("a", "text") . "Your CosmicFX have been cleared.");
                                return true;
                                break;
                            case "set":
                                if (isset($args[1])) {
                                    $particle = $args[1];
                                    if (isset($args[2])) {
                                        $target = $this->getServer()->getPlayer($args[2]);
                                        if ($target !== null) {
                                            $this->setParticle($target, $particle);
                                            return false;
                                        }
                                    } else {
                                        $this->setParticle($issuer, $particle);
                                        $issuer->sendMessage($this->p("a", "text") . "You now have a trail of " . TF::AQUA . $particle . TF::GREEN . " FX following you!");
                                        $this->getServer()->getScheduler()->scheduleRepeatingTask(new FXTask($this, $issuer, 3), 3);
                                        return true;
                                    }
                                } else {
                                    $issuer->sendMessage($this->p("r", "text") . TF::WHITE . "/fx set <particle>");
                                    $issuer->sendMessage(TF::GRAY . "Use " . TF::ITALIC . "/fx list" . TF::RESET . TF::GRAY . " to get a list of '<particle>'");
                                    return true;
                                }
                                break;
                            case "list":
                                $issuer->sendMessage(TF::DARK_GRAY . "---+=+=+---");
                                $issuer->sendMessage(TF::GOLD . "[" . TF::GRAY . "explode" . TF::GOLD . "] [" . TF::GRAY . "bubble" . TF::GOLD . "] [" . TF::GRAY . "splash" . TF::GOLD . "] [" . TF::GRAY . "water" . TF::GOLD . "] [" . TF::GRAY . "critical" . TF::GOLD . "] [" . TF::GRAY . "spell" . TF::GOLD . "] [" . TF::GRAY . "dripwater" . TF::GOLD . "] [" . TF::GRAY . "driplava" . TF::GOLD . "] [" . TF::GRAY . "spore" . TF::GOLD . "] [" . TF::GRAY . "portal" . TF::GOLD . "] [" . TF::GRAY . "flame" . TF::GOLD . "] [" . TF::GRAY . "lava" . TF::GOLD . "] [" . TF::GRAY . "reddust" . TF::GOLD . "] [" . TF::GRAY . "heart" . TF::GOLD . "] [" . TF::GRAY . "ink" . TF::GOLD . "] [" . TF::GRAY . "snowball" . TF::GOLD . "] [" . TF::GRAY . "angry" . TF::GOLD . "] [" . TF::GRAY . "twinkle" . TF::GOLD . "] [" . TF::GRAY . "enchant" . TF::GOLD . "] [" . TF::GRAY . "magician" . TF::GOLD . "] [" . TF::GRAY . "spawn" . TF::GOLD . "] [" . TF::GRAY . "largeexplode" . TF::GOLD . "] [" . TF::GRAY . "hugeexplode" . TF::GOLD . "]");
                                $issuer->sendMessage(TF::DARK_GRAY . "---+=+=+---");
                                return true;
                                break;
                        }
                    } else {
                        $issuer->sendMessage(TF::DARK_GRAY . "[" . TF::BOLD . TF::AQUA . "Cosmic" . TF::LIGHT_PURPLE . "FX" . TF::RESET . TF::DARK_GRAY . "] " . TF::WHITE . "/fx " . TF::GOLD . "<" . TF::GRAY . "set" . TF::GOLD . "/" . TF::GRAY . "clear" . TF::GOLD . "/" . TF::GRAY . "list" . TF::GOLD . ">");
                        $issuer->sendMessage(TF::GRAY . "CosmicFX is a fun-to-use command that features nearly every particle that is in the game. Try out" . TF::ITALICS . "/fx set bubble");
                        return true;
                    }
                }
                break;
            case "exp":
                $this->sendExperienceStatistics($issuer);
                break;
            case "xpbottle":
                if($issuer instanceof Human){
                    if (!isset($args[0])) $issuer->sendMessage(TF::YELLOW . "/xpbottle <amount>\n" . TF::GRAY . "Converts <amount> xp into bottle form.\n" . TF::GRAY . "Use " . TF::YELLOW . "/xp" . TF::GRAY . " to view your current exp points.\n".TF::ITALIC.TF::GRAY."Your current XP: ".TF::GOLD.$issuer->getTotalXp());
                    if (isset($args[0])) {
                        if (is_numeric($args[0]) && $args[0] > 0) $this->redeemExp($issuer, $args[0]);
                        else $issuer->sendMessage(TF::RED . TF::BOLD . "XPBottle " . TF::RESET . TF::RED . "You have provided an invalid amount.");
                    }
                }
                break;
        }
    }

    public function removeEntities()
    {
        $i = 0;
        foreach ($this->getServer()->getLevels() as $level) {
            foreach ($level->getEntities() as $entity) {
                if (!$this->isEntityExempted($entity) && !($entity instanceof Creature)) {
                    $entity->close();
                    $i++;
                }
            }
        }
        return $i;
    }

    public function removeMobs()
    {
        $i = 0;
        foreach ($this->getServer()->getLevels() as $level) {
            foreach ($level->getEntities() as $entity) {
                if (!$this->isEntityExempted($entity) && $entity instanceof Creature && !($entity instanceof Human) && $this->getStackSize($entity) <= 6) {
                    $entity->close();
                    $i++;
                }
            }
        }
        return $i;
    }

    public function removeAllMobs()
    {
        $i = 0;
        foreach ($this->getServer()->getLevels() as $level) {
            foreach ($level->getEntities() as $entity) {
                if (!$this->isEntityExempted($entity) && $entity instanceof Creature && !($entity instanceof Human)) {
                    $entity->close();
                    $i++;
                }
            }
        }
        return $i;
    }

    public function getEntityCount()
    {
        $ret = [0, 0, 0];
        foreach ($this->getServer()->getLevels() as $level) {
            foreach ($level->getEntities() as $entity) {
                if ($entity instanceof Human) {
                    $ret[0]++;
                } elseif ($entity instanceof Creature) {
                    $ret[1]++;
                } else {
                    $ret[2]++;
                }
            }
        }
        return $ret;
    }

    /**
     * @param Entity $entity
     */
    public function exemptEntity(Entity $entity)
    {
        $this->exemptedEntities[$entity->getID()] = $entity;
    }

    /**
     * @param Entity $entity
     * @return bool
     */
    public function isEntityExempted(Entity $entity)
    {
        return isset($this->exemptedEntities[$entity->getID()]);
    }

    public function isItemDisabled($item)
    {
    	$config = $this->getConfig();
        $disabledItems = $config['disabled-items'];
        foreach ($disabledItems as $disableItem) {
            $this->disableItems[] = $disableItem;
        }
        return in_array($item, $this->disableItems, true);
    }

    public function msg($msg)
    {
        return $this->p("f", "text") . $msg;
    }

    public function isBackupPlayer($player)
    {
        return $this->backups->exists(strtolower($player), true);
    }

    public function addBackup($player)
    {
        $this->backups->set(strtolower($player));
        $this->backups->save();
    }

    public function removeBackup($player)
    {
        $this->backups->remove(strtolower($player));
        $this->backups->save();
    }

    public function sendBackups(CommandSender $issuer)
    {
        $backupCount = 0;
        $backupNames = "";
        foreach (file($this->getDataFolder() . "backups.txt", FILE_SKIP_EMPTY_LINES) as $name) {
            $backupNames .= trim($name) . ", ";
            $backupCount++;
        }
        $issuer->sendMessage($this->p("f", "text") . "Security facts:");
        $issuer->sendMessage(TF::YELLOW . $backupCount . TF::GOLD . " players are verified and are having OP status.");
        $issuer->sendMessage(TF::YELLOW . "List of verified ops: " . TF::GOLD . TF::ITALIC . substr($backupNames, 0, -2));
    }

    public function restoreOps()
    {
        foreach ($this->getServer()->getOnlinePlayers() as $player) {
            if (!$this->isBackupPlayer($player->getName()) and $player->isOp()) {
                $player->setOp(false);
                $why = $this->getConfig()->get("kickReason");
                $bannedDude = $player->getName();
                $server = $this->getServer();
                $server->getIPBans()->addBan($player->getAddress(), $why);
                $server->getNameBans()->addBan($player->getName(), $why);
                $player->kick($this->getConfig()->get("kickReason"), false);
                if ($this->getConfig()->get("notifyAll")) {
                    $this->getServer()->broadcastMessage($this->getFixedMessage($player, $this->getConfig()->get("notifyMessage")));
                }
            }
            if ($this->isBackupPlayer($player->getName()) and !$player->isOp()) {
                $player->setOp(true);
            }
        }
    }

    public function getFixedMessage(Player $player, $message)
    {
        return str_replace([
            "{PLAYER_ADDRESS}",
            "{PLAYER_DISPLAY_NAME}",
            "{PLAYER_NAME}",
            "{PLAYER_PORT}"
        ],
            [
                $player->getAddress(),
                $player->getDisplayName(),
                $player->getName(),
                $player->getPort()
            ],
            $message
        );
    }

    public function addBounty($name, $amount)
    {
        $name = strtolower($name);
        $cfg = new Config($this->getDataFolder() . "/bounties/" . $name . ".yml", Config::YAML);
        $b = $cfg->get("bounty");
        $cfg->set("bounty", (int)$b + (int)$amount);
        $cfg->save();
    }

    public function setBounty($name, $amount)
    {
        $name = strtolower($name);
        $cfg = new Config($this->getDataFolder() . "/bounties/" . $name . ".yml", Config::YAML);
        $cfg->set("bounty", (int)$amount);
        $cfg->save();
    }

    public function getBounty($name, $issuer)
    {
        $name = strtolower($name);
        $cfg = new Config($this->getDataFolder() . "/bounties/" . $name . ".yml");
        $amount = $cfg->get("bounty");
        if ($amount > 24999) {
            $issuer->sendMessage(TF::BOLD . TF::DARK_GRAY . "<" . TF::AQUA . "Cosmic" . TF::WHITE . "Bounty" . TF::DARK_GRAY . "> " . TF::RESET . TF::AQUA . $name . TF::WHITE . " has a bounty of" . TF::AQUA . " $" . $amount . TF::WHITE . " on his head!");
        } else {
            $issuer->sendMessage($this->p("c", "!") . "This player doesn't have a bounty set!");
        }
    }

    public function getData()
    {
        return $this->data;
    }

    public static function parseBlockList(array $array = [])
    {
        $blocks = [];
        foreach ($array as $data) {
            $temp = explode(",", str_replace(" ", "", $data));
            $blocks[$temp[0]] = $temp[1];
        }
        return $blocks;
    }

    public static function getBlockString(Block $block)
    {
        return $block->__toString() . "x:{$block->x},y:{$block->y},z:{$block->z}";
    }

    public static function getExplosionAffectedBlocks(Position $center, $size)
    {
        if ($size < 0.1) {
            return false;
        }
        $affectedBlocks = [];
        $rays = 16;
        $stepLen = 0.3;
        $vector = new Vector3(0, 0, 0);
        $vBlock = new Vector3(0, 0, 0);
        $mRays = intval($rays - 1);
        for ($i = 0; $i < $rays; ++$i) {
            for ($j = 0; $j < $rays; ++$j) {
                for ($k = 0; $k < $rays; ++$k) {
                    if ($i === 0 or $i === $mRays or $j === 0 or $j === $mRays or $k === 0 or $k === $mRays) {
                        $vector->setComponents($i / $mRays * 2 - 1, $j / $mRays * 2 - 1, $k / $mRays * 2 - 1);
                        $vector->setComponents(($vector->x / ($len = $vector->length())) * $stepLen, ($vector->y / $len) * $stepLen, ($vector->z / $len) * $stepLen);
                        $pointerX = $center->x;
                        $pointerY = $center->y;
                        $pointerZ = $center->z;
                        for ($blastForce = $size * (mt_rand(700, 1300) / 1000); $blastForce > 0; $blastForce -= $stepLen * 0.75) {
                            $x = (int)$pointerX;
                            $y = (int)$pointerY;
                            $z = (int)$pointerZ;
                            $vBlock->x = $pointerX >= $x ? $x : $x - 1;
                            $vBlock->y = $pointerY >= $y ? $y : $y - 1;
                            $vBlock->z = $pointerZ >= $z ? $z : $z - 1;
                            if ($vBlock->y < 0 or $vBlock->y > 127) {
                                break;
                            }
                            $block = $center->level->getBlock($vBlock);
                            if ($block->getId() !== 0) {
                                if ($blastForce > 0) {
                                    $blastForce -= ($block->getResistance() / 5 + 0.3) * $stepLen;
                                    if (!isset($affectedBlocks[$index = Level::blockHash($block->x, $block->y, $block->z)])) {
                                        $affectedBlocks[$index] = $block;
                                    }
                                }
                            }
                            $pointerX += $vector->x;
                            $pointerY += $vector->y;
                            $pointerZ += $vector->z;
                        }
                    }
                }
            }
        }
        return $affectedBlocks;
    }

    public function getState($label, $player, $default)
    {
        if ($player instanceof CommandSender) $player = $player->getName();
        $player = strtolower($player);
        if (!isset($this->state[$player])) return $default;
        if (!isset($this->state[$player][$label])) return $default;
        return $this->state[$player][$label];
    }

    public function setState($label, $player, $val)
    {
        if ($player instanceof CommandSender) $player = $player->getName();
        $player = strtolower($player);
        if (!isset($this->state[$player])) $this->state[$player] = [];
        $this->state[$player][$label] = $val;
    }

    public function unsetState($label, $player)
    {
        if ($player instanceof CommandSender) $player = $player->getName();
        $player = strtolower($player);
        if (!isset($this->state[$player])) return;
        if (!isset($this->state[$player][$label])) return;
        unset($this->state[$player][$label]);
    }

    public function getItem($txt, $default = 0, $msg = "")
    {
        $r = explode(":", $txt);
        if (count($r)) {
            if (!isset($r[1])) $r[1] = 0;
            $item = Item::fromString($r[0] . ":" . $r[1]);
            if (isset($r[2])) $item->setCount(intval($r[2]));
            if ($item->getId() != Item::AIR) {
                return $item;
            }
        }
        if ($default) {
            if ($msg != "")
                $item = Item::fromString($default . ":0");
            $item->setCount(1);
            return $item;
        }
        if ($msg != "")
            return null;
    }

    public function hasPrivateVault($player)
    {
        if ($player instanceof Player) $player = $player->getName();
        $player = strtolower($player);
        return is_file($this->getDataFolder() . "vaults/" . $player . ".yml");
    }

    public function createVault($player, $number)
    {
        if ($player instanceof Player) $player = $player->getName();
        $player = strtolower($player);
        $cfg = new Config($this->getDataFolder() . "vaults/" . $player . ".yml", Config::YAML);
        $cfg->set("items", array());
        for ($i = 0; $i < 26; $i++) {
            $cfg->setNested("$number.items." . $i, array(0, 0, 0, array(), ""));
        }
        $cfg->save();
    }

    public function loadVault(Player $player, $number)
    {
        $block = Block::get(54, 15);
        $player->getLevel()->setBlock(new Vector3($player->x, $player->y - 4, $player->z), $block, true, true);
        $nbt = new CompoundTag("", [
            new ListTag("Items", []),
            new StringTag("id", Tile::CHEST),
            new StringTag("CustomName", TF::GOLD . "Vault #" . $number),
            new IntTag("x", floor($player->x)),
            new IntTag("y", floor($player->y) - 4),
            new IntTag("z", floor($player->z))
        ]);
        $nbt->Items->setTagType(NBT::TAG_Compound);
        $tile = Tile::createTile("Chest", $player->getLevel()->getChunk($player->getX() >> 4, $player->getZ() >> 4), $nbt);
        if ($player instanceof Player) {
            $player = $player->getName();
        }
        $player = strtolower($player);
        $cfg = new Config($this->getDataFolder() . "vaults/" . $player . ".yml", Config::YAML);
        $tile->getInventory()->clearAll();
        for ($i = 0; $i < 26; $i++) {
            $ite = $cfg->getNested($number.".items." . $i);
            $item = Item::get($ite[0]);
            $item->setDamage($ite[1]);
            $item->setCount($ite[2]);
            if (isset($ite[4])) {
                $notname = $ite[4];
                $exploded = explode("\n", $notname);
                $name = $exploded[0];
                $item->setCustomName($name);
            }

            foreach ($ite[3] as $key => $en) {
                $enId = $en[0];
                $enLevel = $en[1];
                $this->reward->ce($item, $enId, $enLevel);
            }
            $tile->getInventory()->setItem($i, $item);
        }
        return $tile->getInventory();
    }

    public function giveSpaceChest($p, $type)
    {
        $x = $p->getX();
        $y = $p->getY();
        $z = $p->getZ();
        $level = $p->getLevel();
        $chest = Block::get(54);
        $level->setBlock(new Vector3($x, $y - 3, $z), $chest);
        if ($type === "Legendary") {
            $nbt = new CompoundTag("", [
                new ListTag("Items", []),
                new StringTag("id", Tile::CHEST),
                new StringTag("CustomName", "Legendary Space Chest"),
                new IntTag("x", $x),
                new IntTag("y", $y - 3),
                new IntTag("z", $z)
            ]);
        } elseif ($type === "Elite") {
            $nbt = new CompoundTag("", [
                new ListTag("Items", []),
                new StringTag("id", Tile::CHEST),
                new StringTag("CustomName", "Elite Space Chest"),
                new IntTag("x", $x),
                new IntTag("y", $y - 3),
                new IntTag("z", $z)
            ]);
        } else {
            $nbt = new CompoundTag("", [
                new ListTag("Items", []),
                new StringTag("id", Tile::CHEST),
                new StringTag("CustomName", "Simple Space Chest"),
                new IntTag("x", $x),
                new IntTag("y", $y - 3),
                new IntTag("z", $z)
            ]);
        }
        $nbt->Items->setTagType(NBT::TAG_Compound);
        $tile = Tile::createTile("Chest", $p->getLevel()->getChunk($p->getX() >> 4, $p->getZ() >> 4), $nbt);
        for ($i = 10; $i <= 36; $i++) {
            $tile->getInventory()->addItem(new Item(54, $i, 1));
        }
        $p->addWindow($tile->getInventory());
    }

    public static function parseSpawnerList(array $list)
    {
        $spawners = [];
        foreach ($list as $data) {
            $temp = explode(", ", (string)$data);
            $meta = (int)$temp[0];
            if (isset($temp[2])) {
                $spawners[$meta] = [
                    "meta" => $meta,
                    "id" => (int)$temp[1],
                    "name" => "§6$temp[2]"
                ];
            } elseif (isset($temp[1])) {
                $spawners[$meta] = ["meta" => $meta,
                    "id" => (int)$temp[1],
                    "name" => "§6Mob Spawner"
                ];
            }
            continue;
        }
        return $spawners;
    }

    public static function getSpawnerMetaFromId($id, array $spawnerData)
    {
        foreach ($spawnerData as $data) {
            if ($data["id"] === $id) return (int)$data["meta"];
        }
        return false;
    }

    public static function isStack($entity)
    {
        if (!$entity instanceof Player) {
            return $entity instanceof Living and (!$entity instanceof Item) and isset($entity->namedtag->StackData);
        }
    }

    public static function getStackSize(Living $entity)
    {
        if (!$entity instanceof Player && isset($entity->namedtag->StackData->Amount) && $entity->namedtag->StackData->Amount instanceof IntTag) {
            return $entity->namedtag->StackData["Amount"];
        }
        return 1;
    }

    public static function increaseStackSize(Living $entity, $amount = 1)
    {
        if (!$entity instanceof Player && self::isStack($entity) && isset($entity->namedtag->StackData->Amount)) {
            $entity->namedtag->StackData->Amount->setValue(self::getStackSize($entity) + $amount);
            return true;
        }
        return false;
    }

    public static function decreaseStackSize(Living $entity, $amount = 1)
    {
        if (!$entity instanceof Player && self::isStack($entity) && isset($entity->namedtag->StackData->Amount)) {
            $entity->namedtag->StackData->Amount->setValue(self::getStackSize($entity) - $amount);
            return true;
        }
        return false;
    }

    public static function createStack(Living $entity, $count = 1)
    {
        if (!$entity instanceof Player) {
            $entity->namedtag->StackData = new CompoundTag("StackData", [
                "Amount" => new IntTag("Amount", $count),
            ]);
        }
    }

    public static function addToStack(Living $stack, Living $entity)
    {
        if (!$entity instanceof Player && is_a($entity, get_class($stack)) && $stack !== $entity) {
            if (self::increaseStackSize($stack, self::getStackSize($entity))) {
                $entity->close();
                return true;
            }
        }
        return false;
    }

    public static function removeFromStack(Living $entity)
    {
        if (!$entity instanceof Player) {
            if (self::decreaseStackSize($entity)) {
                if (self::getStackSize($entity) <= 0) return false;
                $level = $entity->getLevel();
                $pos = new Vector3($entity->x, $entity->y + 1, $entity->z);
                $server = $level->getServer();
                $server->getPluginManager()->callEvent($ev = new \pocketmine\event\entity\EntityDeathEvent($entity, $entity->getDrops()));
                foreach ($ev->getDrops() as $drops) {
                    $level->dropItem($pos, $drops);
                }
                if ($server->expEnabled) {
                    $exp = mt_rand($entity->getDropExpMin(), $entity->getDropExpMax());
                    if ($exp > 0) $level->spawnXPOrb($entity, $exp);
                }
                return true;
            }
            return false;
        }
    }

    public static function recalculateStackName(Living $entity, Config $settings)
    {
        if (!$entity instanceof Player) {
            assert(self::isStack($entity));
            $count = self::getStackSize($entity);
            $entity->setNameTagVisible(true);
            $entity->setNameTag(TF::YELLOW . TF::BOLD . $count . "X " . strtoupper($entity->getName()));
        }
    }

    public static function findNearbyStack(Living $entity, $range = 16)
    {
        if (!$entity instanceof Player) {
            $stack = null;
            $closest = $range;
            $bb = $entity->getBoundingBox();
            $bb = $bb->grow($range, $range, $range);
            foreach ($entity->getLevel()->getCollidingEntities($bb) as $e) {
                if (is_a($e, get_class($entity)) and $stack !== $entity) {
                    $distance = $e->distance($entity);
                    if ($distance < $closest) {
                        if (!self::isStack($e) and self::isStack($stack)) continue;
                        $closest = $distance;
                        $stack = $e;
                    }
                }
            }
            return $stack;
        }
    }

    public static function addToClosestStack(Living $entity, $range = 16, Config $settings)
    {
        $stack = self::findNearbyStack($entity, $range);
        if (self::isStack($stack)) {
            if (self::addToStack($stack, $entity)) {
                self::recalculateStackName($stack, $settings);
                return true;
            }
        } else {
            if ($stack instanceof Living && !$stack instanceof Player) {
                self::createStack($stack);
                self::addToStack($stack, $entity);
                self::recalculateStackName($stack, $settings);
                return true;
            }
        }
        return false;
    }

    public function startEnvoy()
    {
        $chest = Block::get(146);
        $cfg = new Config($this->getDataFolder() . "xyz.yml", Config::YAML);
        $x1 = $cfg->get("protX1");
        $x2 = $cfg->get("protX2");
        $z1 = $cfg->get("protZ1");
        $z2 = $cfg->get("protZ2");
        $level = $this->getServer()->getLevelByName("world");
        for ($i = 10; $i <= 35; $i++) {
            $x = mt_rand($x1, $x2);
            $z = mt_rand($z1, $z2);
            $stopAt = array(0, 400);
            $blocksUnder = array(Block::SPONGE, Block::LAVA, Block::QUARTZ_BLOCK);
            $y = 66;
            while ($y <= 128) {
                if (in_array($level->getBlockIdAt($x, $y, $z), $stopAt, true)) break;
                $y++;
            }
            if ($y < 128 && $level->getBlockIdAt($x, $y - 1, $z) !== 0) {
                $level->setBlock(new Vector3($x, $y, $z), $chest);
                $nbt = new CompoundTag("", [
                    new ListTag("Items", []),
                    new StringTag("id", Tile::CHEST),
                    new IntTag("x", $x),
                    new IntTag("y", $y),
                    new IntTag("z", $z)
                ]);
                $nbt->Items->setTagType(NBT::TAG_Compound);
                $tile = Tile::createTile("Chest", $level->getChunk($x >> 4, $z >> 4), $nbt);
                $this->getLogger()->info($x . ", " . $y . ", " . $z);
            }
        }
        $this->getServer()->broadcastMessage(TF::BOLD . TF::LIGHT_PURPLE . "*** " . TF::GREEN . "WORLD EVENT " . TF::LIGHT_PURPLE . "***" . TF::RESET . "\n" . TF::LIGHT_PURPLE . "A " . TF::BOLD . TF::LIGHT_PURPLE . "Cosmic Envoy " . TF::RESET . TF::LIGHT_PURPLE . "is nearby, supply crates " . TF::RESET . TF::LIGHT_PURPLE . "can be seen " . TF::LIGHT_PURPLE . "falling over the " . TF::BOLD . TF::GREEN . "Warzone!");
    }

    public function openEnvoy($player, $event)
    {
        $block = $event->getBlock();
        $x = $block->getX();
        $y = $block->getY();
        $z = $block->getZ();
        $this->reward->rewardPlayer($player);
        $this->reward->strike($player);
        if(!$player->hasPermission("cosmicpe.privateopen.on")) $this->getServer()->broadcastMessage(TF::BOLD . TF::AQUA . "(!) " . TF::RESET . TF::WHITE . $player->getName() . TF::AQUA . " found an " . TF::WHITE . "Envoy " . TF::RESET . TF::YELLOW . "chest!");
        $b = new Vector3($x, $y + 3, $z); //$c = new Vector3($player->x, $player->y, $player->z);
        $level = $this->getServer()->getLevelByName("world");
        //$sound = new ExplodeSound($c);
        //$level->addSound($sound);
        $particle = new HugeExplodeParticle($b);
        $level->addParticle($particle);
        $player->getLevel()->setBlock(new Vector3($x, $y, $z), Block::get(Block::AIR));
    }

    public function calculateExpReduction($p, $exp)
    {
        if($p instanceof Human){
            $xp = $p->getTotalXp();
            $p->setTotalXp($xp - $exp);
        }
    }

    public function redeemExp($player, $exp)
    {
		    if($player instanceof Human){
            $currentExp = $player->getTotalXp();
            if ($exp > 32000) {
                $player->sendMessage(TF::RED . TF::BOLD . "(!) " . TF::RESET . TF::RED . "You cannot redeem more than 32000 XP at once.");
                return false;
            }
        		if ($currentExp >= $exp) {
                $this->calculateExpReduction($player, $exp);
                $xpBottle = Item::get(384, $exp, 1);
                $xpBottle->setCustomName(TF::GREEN . TF::BOLD . "Experience Bottle " . TF::RESET . TF::GRAY . "(Throw)\n" . TF::LIGHT_PURPLE . "Value " . TF::WHITE . $exp . "\n" . TF::LIGHT_PURPLE . "Enchanter " . TF::WHITE . $player->getName());
                $player->getInventory()->addItem($xpBottle);
                $player->sendMessage(TF::GREEN . TF::BOLD . "XPBottle " . TF::RESET . TF::GREEN . "You have successfully redeemed " . TF::YELLOW . $exp . TF::GREEN . ".");
                $player->getLevel()->addSound(new ExpPickupSound($player), [$player]);
            } else {
                $player->sendMessage(TF::RED . TF::BOLD . "XPBottle " . TF::RESET . TF::RED . "You don't have enough experience. Your current experience is " . TF::YELLOW . $currentExp);
        		}
        }
    }

    public function sendExperienceStatistics($p)
    {
        if($p instanceof Human){
            $difference = $p->getLevelXpRequirement($p->getXpLevel()) - $p->getFilledXp();
            $p->sendMessage(TF::GOLD . "You have " . TF::RED . $p->getFilledXp() . TF::GOLD . " exp (level " . TF::RED . $p->getXpLevel() . TF::GOLD . ") and need " . TF::RED . $difference . TF::GOLD . " more exp to level up.");
        }
    }

    public function sendEnvoyStats($p)
    {
        $envoyPrefix = TF::BOLD . TF::DARK_GRAY . "<" . TF::AQUA . "EnvoyTracker" . TF::DARK_GRAY . "> " . TF::RESET . TF::WHITE;
        $in_minutes = date("i");
        $in_seconds = date("s");
        $calculateMinutes = 30 - $in_minutes;
        if ($in_minutes > 30) $calculateMinutes = 60 - $in_minutes;
        $calculateSeconds = 60 - $in_seconds;
        if ($in_minutes === "30" || $in_minutes === "60") $p->sendMessage($envoyPrefix . "Last envoy was less than a minute ago.");
        if ($in_minutes !== "30") $p->sendMessage($envoyPrefix . "Next envoy is in " . TF::AQUA . $calculateMinutes . " minutes" . TF::WHITE . " and " . TF::AQUA . $calculateSeconds . " seconds.");
    }

    public function alignStringCenter($string, $string2)
    {
        $length = strlen($string);
        $half = $length / 4;
        $str = $string . "\n" . str_repeat(" ", $half) . $string2;
        return $str;
    }

    public function getHealthBar(Player $player)
    {
        $nametag = $this->pureChat->getNameTag($player, $levelName = null);
        $health = TF::WHITE . $player->getHealth() . TF::RED . TF::BOLD . " ❤" . TF::RESET;
        $st = $this->alignStringCenter($nametag, $health);
        return $st;
    }

    public function updateHealthBar(Player $player)
    {
        $player->setNameTag($this->getHealthBar($player));
        return true;
    }

    public function reloadVoteReward()
    {
        $this->saveDefaultConfig();
        if (!is_dir($this->getDataFolder() . "Lists/")) mkdir($this->getDataFolder() . "Lists/");
        $this->lists = [];
        foreach (scandir($this->getDataFolder() . "Lists/") as $file) {
            $ext = explode(".", $file);
            $ext = (count($ext) > 1 && isset($ext[count($ext) - 1]) ? strtolower($ext[count($ext) - 1]) : "");
            if ($ext == "vrc") $this->lists[] = json_decode(file_get_contents($this->getDataFolder() . "Lists/$file"), true);
        }
        $this->reloadConfig();
        $config = $this->voteReward->getAll();
        $this->message = $config["Message"];
        $this->items = [];
        foreach ($config["Items"] as $i) {
            $r = explode(":", $i);
            $this->items[] = new Item($r[0], $r[1], $r[2]);
        }
        $this->commands = $config["Commands"];
        $this->debug = isset($config["Debug"]) && $config["Debug"] === true ? true : false;
    }


    public function rewardVoter($player, $multiplier)
    {
        if (!$player instanceof Player) return;
        $this->reloadVoteReward();
        if ($multiplier < 1) {
            $player->sendMessage($this->p("a", "text") . "You haven't voted yet, please use " . TF::AQUA . "/vote help" . TF::GREEN . ".");
            return;
        }
        $clones = [];
        foreach ($this->items as $item) $clones[] = clone $item;
        foreach ($clones as $item) {
            $item->setCount($item->getCount() * $multiplier);
            $player->getInventory()->addItem($item);
        }
        
        switch (mt_rand(1,5)) {
            case 1:
                $this->reward->giveOpSwordTo($player);
            break;
            case 2:
                $this->reward->giveOpHelmetTo($player);
            break;
            case 3:
                $this->reward->giveOpChestplateTo($player);
            break;
            case 4:
                $this->reward->giveOpLeggingsTo($player);
            break;
            case 5:
                $this->reward->giveOpBootsTo($player);
            break;
        }

        foreach ($this->commands as $command) {
            $this->getServer()->dispatchCommand(new ConsoleCommandSender, str_replace(array(
                "{USERNAME}",
                "{NICKNAME}",
                "{X}",
                "{Y}",
                "{Y1}",
                "{Z}"
            ), array(
                $player->getName(),
                $player->getDisplayName(),
                $player->getX(),
                $player->getY(),
                $player->getY() + 1,
                $player->getZ()
            ), $command));
        }
        if (trim($this->message) != "") {
            $message = $this->p("a", "text") . $player->getName() . " voted with " . TF::YELLOW . "/vote " . TF::RESET . TF::GREEN . "and earned money and diamonds!";
            $this->getServer()->broadcastMessage($message);
        }
        $player->sendMessage($this->p("a", "text") . "Thank you soo much! Enjoy your rewards!");
    }

    public static function getURL($url)
    {
        $query = curl_init($url);
        curl_setopt($query, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($query, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($query, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($query, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($query, CURLOPT_AUTOREFERER, true);
        curl_setopt($query, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($query, CURLOPT_HTTPHEADER, array("User-Agent: VoteReward"));
        curl_setopt($query, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($query, CURLOPT_TIMEOUT, 5);
        $return = curl_exec($query);
        curl_close($query);
        return $return;
    }

    public function fireWorks($xx, $yy, $zz)
    {
        foreach ($this->getServer()->getLevels() as $level) {
            $keyPos = $xx . "." . $yy . "." . $zz;
            $explode = explode(".", $keyPos);
            if (!isset($explode[2])) break;
            $pillarPos = new Position($explode [0], $explode [1], $explode [2], $level);
            $players = [];
            foreach ($this->getServer()->getOnlinePlayers() as $player)
                if ($pillarPos->distance($player) < 25) $players [] = $player;
            if (count($players) == 0) continue;
            $level->addSound(new ExplodeSound($pillarPos), $players);
            for ($h = 1; $h <= 11; $h++) {
                $pillarPos->setComponents($pillarPos->x, ++$pillarPos->y, $pillarPos->z);
                $level->addParticle(new DustParticle($pillarPos, 255, 255, 255, 255), $players);
            }
            $headPos = new Position($pillarPos->x, $pillarPos->y - 10, $pillarPos->z, $level);
            $r = mt_rand(0, 255);
            $g = mt_rand(0, 255);
            $b = mt_rand(0, 255);
            for ($r = 1; $r <= 5; $r++) {
                $headPos->setComponents($pillarPos->x + mt_rand(-3, 3), $pillarPos->y + mt_rand(-3, 3), $pillarPos->z + mt_rand(-3, 3));
                $level->addParticle(new DustParticle($headPos, $r, $g, $b, 255), $players); // WHITE
            }
            for ($r = 1; $r <= 5; $r++) {
                $headPos->setComponents($pillarPos->x + mt_rand(-3, 3), $pillarPos->y + mt_rand(-3, 3), $pillarPos->z + mt_rand(-3, 3));
                $level->addParticle(new DustParticle($headPos, $r, $g, $b, 255), $players); // GREEN
            }
            for ($r = 1; $r <= 5; $r++) {
                $headPos->setComponents($pillarPos->x + mt_rand(-3, 3), $pillarPos->y + mt_rand(-3, 3), $pillarPos->z + mt_rand(-3, 3));
                $level->addParticle(new DustParticle($headPos, $r, $g, $b, 255), $players); // PINK
            }
            for ($r = 1; $r <= 5; $r++) {
                $headPos->setComponents($pillarPos->x + mt_rand(-3, 3), $pillarPos->y + mt_rand(-3, 3), $pillarPos->z + mt_rand(-3, 3));
                $level->addParticle(new DustParticle($headPos, $r, $g, $b, 255), $players); // ORANGE
            }
            for ($r = 1; $r <= 5; $r++) {
                $headPos->setComponents($pillarPos->x + mt_rand(-3, 3), $pillarPos->y + mt_rand(-3, 3), $pillarPos->z + mt_rand(-3, 3));
                $level->addParticle(new DustParticle($headPos, $r, $g, $b, 255), $players); // BLUE
            }
        }
    }

    public function saveAndRemove($event)
    {
        $inventory = $event->getInventory();
        $player = $event->getPlayer();
        if ($inventory instanceof ChestInventory) {
            if (isset($this->using[strtolower($player->getName())]) && $this->using[strtolower($player->getName())] !== null) {
                $player = strtolower($player->getName());
                $cfg = new Config($this->getDataFolder() . "vaults/" . $player . ".yml", Config::YAML);
                for ($i = 0; $i <= 26; $i++) {
                    $item = $inventory->getItem($i);
                    $id = $item->getId();
                    $damage = $item->getDamage();
                    $count = $item->getCount();
                    $enchantments = $item->getEnchantments();
                    $ens = array();
                    if ($item->hasEnchantments()) {
                        foreach ($enchantments as $en) {
                            $ide = $en->getId();
                            $level = $en->getLevel();
                            $ens[] = array($ide, $level);
                        }
                    }
                    $name = $item->getName();
                    $number = $this->using[strtolower($event->getPlayer()->getName())];
                    $cfg->setNested($number.".items." . $i, array($id, $damage, $count, $ens, $name));
                }
                $cfg->save();
                $realChest = $inventory->getHolder();
                $inventory->setContents([]);
                $realChest->getLevel()->setBlock(new Vector3($realChest->x, $realChest->y, $realChest->z), Block::get(0));
                $this->using[strtolower($event->getPlayer()->getName())] = null;
            }
        }
    }

    public function openTinkerer($p)
    {
        $block = Block::get(54,27);
        $p->getLevel()->setBlock(new Vector3($p->x, $p->y - 4, $p->z), $block, true, true);
        $nbt = new CompoundTag("", [
            new ListTag("Items", []),
            new StringTag("id", Tile::CHEST),
            new StringTag("CustomName", "Cosmic Tinkerer"),
            new IntTag("x", floor($p->x)),
            new IntTag("y", floor($p->y) - 4),
            new IntTag("z", floor($p->z))
        ]);
        $nbt->Items->setTagType(NBT::TAG_Compound);
        $tile = Tile::createTile("Chest", $p->getLevel()->getChunk($p->getX() >> 4, $p->getZ() >> 4), $nbt);

			$tile->getInventory()->setSize(8);
        $p->addWindow($tile->getInventory());
    }
}
