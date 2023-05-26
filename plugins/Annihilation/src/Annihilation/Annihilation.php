<?php

namespace Annihilation;

use Annihilation\Arena\Object\Team;
use Annihilation\Arena\Tile\EnderBrewingStand;
use Annihilation\Arena\Tile\EnderFurnace;
use Annihilation\Entity\SlapperHuman;
use Annihilation\MySQL\BuyKitQuery;
use Annihilation\MySQL\JoinQuery;
use Annihilation\MySQL\StatsQuery;
use MTCore\MessageTask;
use MTCore\MTCore;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use Annihilation\Entity\IronGolem;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Tile;
use pocketmine\utils\TextFormat;
use Annihilation\Arena\Arena;
use pocketmine\math\Vector3;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class Annihilation extends PluginBase implements Listener{
    public $arenas = [];

    /** @var  MySQLManager */
    public $mysql;
    public $maps = [];

    /** @var Arena[] */
    public $ins = [];

    /** @var  Level $level */
    public $level;
    public $mainLobby;
    /** @var  MTCore $mtcore */
    public $mtcore;

    /** @var  Annihilation */
    private static $instance;

    public function onLoad(){
        self::$instance = $this;

        if(!file_exists($this->getServer()->getDataPath()."worlds/annihilation")){
            WorldManager::xcopy("/root/worlds/", $this->getServer()->getDataPath());
        }
    }

    public function onEnable() {
        Entity::registerEntity(IronGolem::class);
        Entity::registerEntity(SlapperHuman::class);

        Tile::registerTile(EnderFurnace::class);
        Tile::registerTile(EnderBrewingStand::class);

        $this->level = $this->getServer()->getDefaultLevel();
        $this->mtcore = $this->getServer()->getPluginManager()->getPlugin("MTCore");
        $this->getLogger()->info(TextFormat::GREEN."Annihilation enabled");
        $this->mysql = new MySQLManager($this);
        $this->mysql->createMySQLConnection();
        $this->mainLobby = $this->level->getSpawnLocation();
        $this->level->setTime(5000);
        $this->level->stopTime;
        $this->setMapsData();
        $this->setArenasData();
        $this->registerArena("anni-1");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        MessageTask::$messages[count(MessageTask::$messages)] = TextFormat::AQUA."Use ender furnace and ender chest to prevent stealing your items!";
        MessageTask::$messages[count(MessageTask::$messages)] = TextFormat::AQUA."Brew potions and defeat other teams faster!";
        MessageTask::$messages[count(MessageTask::$messages)] = TextFormat::AQUA."Kill the boss and get a rare item!";
        MessageTask::$messages[count(MessageTask::$messages)] = TextFormat::AQUA."Use different kits for better experience of the game!";
        MessageTask::$messages[count(MessageTask::$messages)] = TextFormat::AQUA."Change your kit using /class command!";
    }

    public function onDisable() {
        $this->getLogger()->info(TextFormat::RED."Annihilation disabled");
    }

    public function registerArena($arena){
        $a = new Arena($arena, $this);
        $this->getServer()->getPluginManager()->registerEvents($a, $this);
        $this->ins[$arena] = $a;
    }

    public function onJoin(PlayerJoinEvent $e){
        $p = $e->getPlayer();

        new JoinQuery($this, $p->getName());
    }

    public static function getPrefix(){
        return "§l§6[Annihilation]§r§f ".TextFormat::RESET.TextFormat::WHITE;
    }

    public function setArenasData(){
        $this->arenas = ['anni-1' => ['sign' => new Vector3(125, 20, 172),
            '1sign' => new Vector3(488, 21, 493),
            '2sign' => new Vector3(488, 21, 491),
            '3sign' => new Vector3(490, 21, 489),
            '4sign' => new Vector3(492, 21, 489),
            'lobby' => new Vector3(528, 20, 497)
        ]];
    }

    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
        if($sender instanceof Player){

            $arena = $this->getPlayerArena($sender);

            switch(strtolower($cmd->getName())){
                case 'class':
                    if($arena === false){
                        $sender->sendMessage($cmd->getPermissionMessage());
                        break;
                    }
                    if(!isset($args[0])){
                        $sender->sendMessage(self::getPrefix().TextFormat::RED."use /class <kit>");
                        break;
                    }
                    $kits = array_keys($arena->kitManager->kits);

                    switch(strtolower($args[0])){
                        case "help":
                            $msg = self::getPrefix().TextFormat::GREEN."Available kits: ".TextFormat::YELLOW;

                            foreach($kits as $kit){
                                $msg .= $kit.TextFormat::GRAY.", ";
                            }

                            $sender->sendMessage(substr($msg, 0, -2));
                            break;
                        default:
                            if(!in_array(strtolower($args[0]), $kits)){
                                $sender->sendMessage(self::getPrefix().TextFormat::RED."This class doesn't exist. Use /class help for list of classes");
                                break;
                            }

                            $arena->kitManager->onKitChange($sender, strtolower($args[0]));
                            break;
                    }
                    break;
                case 'blue':
                    if($arena === false){
                        break;
                    }
                    $arena->joinTeam($sender, 1);
                    break;
                case 'red':
                    if($arena === false){
                        break;
                    }
                    $arena->joinTeam($sender, 2);
                    break;
                case 'yellow':
                    if($arena === false){
                        break;
                    }
                    $arena->joinTeam($sender, 3);
                    break;
                case 'green':
                    if($arena === false){
                        break;
                    }
                    $arena->joinTeam($sender, 4);
                    break;
                case 'lobby':
                    if($arena){
                        $arena->handlePlayerQuit($sender);
                    }
                    else{
                        $sender->teleport($this->mainLobby);
                    }
                    $sender->getInventory()->clearAll();
                    break;
                case 'stats':
                    new StatsQuery($this, $sender->getName());
                    break;
                case 'vote':
                    if($arena === false){
                        break;
                    }
                    if(isset($args[1]) || !isset($args[0])){
                        $sender->sendMessage($this->getPrefix().TextFormat::GRAY."use /vote [map]");
                        break;
                    }
                    $arena->votingManager->onVote($sender, strtolower($args[0]));
                    break;
                case 'start':
                    if($sender->isOp()){
                        if($arena === false || $arena->phase >= 1){
                            break;
                        }
                        $arena->selectMap(true);
                        //$arena->startGame(true);
                    }
                    break;
            }
        }
    }

    /**
     * @param Player $p
     * @return Arena
     */
    public function getPlayerArena(Player $p){
        foreach($this->ins as $arena){
            if($arena->inArena($p)){
                return $arena;
            }
        }
        return false;
    }

    public function setMapsData(){
        $this->maps['Canyon'] = ["1Spawn" => new Vector3(-108, 76, -121),
            "2Spawn" => new Vector3(-121, 76, 233),
            "3Spawn" => new Vector3(233, 76, 246),
            "4Spawn" => new Vector3(246, 76, -108),
            "1Nexus" => new Vector3(-113, 70, -114),
            "2Nexus" => new Vector3(-114, 70, 238),
            "3Nexus" => new Vector3(238, 70, 239),
            "4Nexus" => new Vector3(239, 70, -113),
            "1Chest" => new Vector3(-102, 73, -112),
            "2Chest" => new Vector3(-112, 73, 227),
            "3Chest" => new Vector3(227, 73, 237),
            "4Chest" => new Vector3(237, 73, -102),
            "1Furnace" => new Vector3(-103, 73, -112),
            "2Furnace" => new Vector3(-112, 73, 228),
            "3Furnace" => new Vector3(228, 73, 237),
            "4Furnace" => new Vector3(237, 73, -103),
            "1EnderBrewing" => new Vector3(-100, 73, -112),
            "2EnderBrewing" => new Vector3(-112, 73, 225),
            "3EnderBrewing" => new Vector3(225, 73, 237),
            "4EnderBrewing" => new Vector3(237, 73, -100),
            //signs
            "1Brewing" => new Vector3(-122, 81, -126),
            "1Weapons" => new Vector3(-126, 81, -122),
            "2Brewing" => new Vector3(-126, 81, 247),
            "2Weapons" => new Vector3(-122, 81, 251),
            "3Brewing" => new Vector3(247, 81, 251),
            "3Weapons" => new Vector3(251, 81, 247),
            "4Brewing" => new Vector3(251, 81, -122),
            "4Weapons" => new Vector3(247, 81, -126),
            //diamonds
            "diamonds" => [ new Vector3(82, 67, 52), new Vector3(80, 67, 59), new Vector3(71, 71, 57),
                new Vector3(58, 67, 62), new Vector3(57, 66, 65), new Vector3(61, 64, 61), new Vector3(70, 70, 75),
                new Vector3(73, 67, 82), new Vector3(44, 68, 48), new Vector3(49, 68, 58), new Vector3(39, 67, 63),
                new Vector3(44, 68, 73)],
            "bosses" => [1 => ['name' => '§bFerwin', 'pos' => new Vector3(61, 15, -52), 'chest' => new Vector3(62, 15, -42)], 2 => ['name' => '§cCelariel', 'pos' => new Vector3(64, 15, 177), 'chest' => new Vector3(63, 15, 167)]],
            "corner2" => new Vector3(254, 128, 254),
            "corner1" => new Vector3(-129, 0, -129)
        ];

        $this->maps['Hamlet'] = ["1Spawn" => new Vector3(-190, 44, 193),
            "2Spawn" => new Vector3(95, 44, -100),
            "3Spawn" => new Vector3(-194, 44, -96),
            "4Spawn" => new Vector3(99, 44, 189),
            "1Nexus" => new Vector3(-216, 39, 210),
            "2Nexus" => new Vector3(121, 39, -117),
            "3Nexus" => new Vector3(-211, 39, -122),
            "4Nexus" => new Vector3(116, 39, 215),
            "1Chest" => new Vector3(-217, 44, 215),
            "2Chest" => new Vector3(122, 44, -122),
            "3Chest" => new Vector3(-216, 44, -123),
            "4Chest" => new Vector3(121, 44, 216),
            "1Furnace" => new Vector3(-217, 44, 214),
            "2Furnace" => new Vector3(122, 44, -121),
            "3Furnace" => new Vector3(-215, 44, -123),
            "4Furnace" => new Vector3(120, 44, 216),
            "1EnderBrewing" => new Vector3(-217, 45, 214),
            "2EnderBrewing" => new Vector3(122, 45, -121),
            "3EnderBrewing" => new Vector3(-215, 45, -123),
            "4EnderBrewing" => new Vector3(120, 45, 216),
            //signs
            "1Brewing" => new Vector3(-217, 46, 214),
            "1Weapons" => new Vector3(-217, 46, 210),
            "2Brewing" => new Vector3(122, 46, -121),
            "2Weapons" => new Vector3(122, 46, -117),
            "3Brewing" => new Vector3(-215, 46, -123),
            "3Weapons" => new Vector3(-211, 46, -123),
            "4Brewing" => new Vector3(120, 46, 216),
            "4Weapons" => new Vector3(117, 46, 216),
            //diamonds
            "diamonds" => [ new Vector3(-52, 43, 50), new Vector3(-51, 43, 42), new Vector3(-57, 42, 50),
                new Vector3(-40, 43, 48), new Vector3(-47, 43, 44), new Vector3(-44, 43, 42), new Vector3(-47, 42, 36),
                new Vector3(-75, 45, 21), new Vector3(-72, 45, 76), new Vector3(-20, 45, 71), new Vector3(-23, 45, 18),
                new Vector3(-42, 42, 55)],
            "bosses" => [1 => ['name' => '§bFerwin', 'pos' => new Vector3(-204, 42, 47), 'chest' => new Vector3(-218, 44, 47)], 2 => ['name' => '§cCelariel', 'pos' => new Vector3(111, 42, 46), 'chest' => new Vector3(124, 44, 46)]],
            "corner2" => new Vector3(127, 128, 221),
            "corner1" => new Vector3(-222, 0, -128)
        ];

        $this->maps['Amazon'] = ["1Spawn" => new Vector3(246, 24, 260),
            "2Spawn" => new Vector3(14, 24, 260),
            "3Spawn" => new Vector3(14, 24, 462),
            "4Spawn" => new Vector3(246, 24, 462),
            "1Nexus" => new Vector3(262, 44, 218),
            "2Nexus" => new Vector3(-2, 44, 218),
            "3Nexus" => new Vector3(-2, 44, 504),
            "4Nexus" => new Vector3(262, 44, 504),
            "1Chest" => new Vector3(245, 24, 252),
            "2Chest" => new Vector3(15, 24, 252),
            "3Chest" => new Vector3(15, 24, 470),
            "4Chest" => new Vector3(245, 24, 470),
            "1Furnace" => new Vector3(245, 24, 251),
            "2Furnace" => new Vector3(15, 24, 251),
            "3Furnace" => new Vector3(15, 24, 471),
            "4Furnace" => new Vector3(245, 24, 471),
            "1EnderBrewing" => new Vector3(241, 24, 253),
            "2EnderBrewing" => new Vector3(19, 24, 253),
            "3EnderBrewing" => new Vector3(19, 24, 469),
            "4EnderBrewing" => new Vector3(241, 24, 471),
            //signs
            "1Brewing" => new Vector3(248, 25, 230),
            "1Weapons" => new Vector3(248, 26, 230),
            "2Brewing" => new Vector3(122, 25, -121),
            "2Weapons" => new Vector3(122, 26, -117),
            "3Brewing" => new Vector3(-215, 25, -123),
            "3Weapons" => new Vector3(-211, 26, -123),
            "4Brewing" => new Vector3(120, 25, 216),
            "4Weapons" => new Vector3(117, 26, 216),
            //diamonds
            "diamonds" => [ new Vector3(131, 32, 352), new Vector3(126, 31, 357), new Vector3(123, 31, 363),
                new Vector3(129, 32, 370), new Vector3(134, 31, 365), new Vector3(139, 32, 361), new Vector3(129, 28, 362),
                new Vector3(133, 29, 360)],
            "bosses" => [1 => ['name' => '§bFerwin', 'pos' => new Vector3(-57, 21, 361), 'chest' => new Vector3(-49, 22, 361)], 2 => ['name' => '§cCelariel', 'pos' => new Vector3(317, 21, 361), 'chest' => new Vector3(309, 22, 361)]],
            "corner2" => new Vector3(329, 128, 558),
            "corner1" => new Vector3(-69, 0, 164)
        ];

        $this->maps['Andorra'] = ["1Spawn" => new Vector3(183, 80, 0),
            "2Spawn" => new Vector3(0, 80, -183),
            "3Spawn" => new Vector3(0, 80, 183),
            "4Spawn" => new Vector3(-183, 80, 0),
            "1Nexus" => new Vector3(199, 101, 0),
            "2Nexus" => new Vector3(0, 101, -99),
            "3Nexus" => new Vector3(0, 101, 199),
            "4Nexus" => new Vector3(-199, 101, 0),
            "1Chest" => new Vector3(189, 80, 18),
            "2Chest" => new Vector3(18, 80, -189),
            "3Chest" => new Vector3(-18, 80, 189),
            "4Chest" => new Vector3(-189, 80, -18),
            "1Furnace" => new Vector3(189, 81, 10),
            "2Furnace" => new Vector3(10, 81, -189),
            "3Furnace" => new Vector3(-10, 81, 189),
            "4Furnace" => new Vector3(-189, 81, -10),
            "1Brewing" => new Vector3(190, 81, 12),
            "2Brewing" => new Vector3(12, 81, -190),
            "3Brewing" => new Vector3(-12, 81, 190),
            "4Brewing" => new Vector3(-190, 81, -112),
            //diamonds
            "diamonds" => [ new Vector3(9, 69, 11), new Vector3(11, 69, -9), new Vector3(-9, 69, -11),
                new Vector3(-11, 69, 9), new Vector3(-2, 85, 2), new Vector3(2, 85, -2), new Vector3(40, 78, 40),
                new Vector3(40, 78, -40), new Vector3(-40, 78, -40), new Vector3(-40, 78, -40), new Vector3(0, 69, 0)],
            "bosses" => [1 => ['name' => '§bFerwin', 'pos' => new Vector3(-163, 91, -163), 'chest' => new Vector3(-163, 91, -156)], 2 => ['name' => '§cCelariel', 'pos' => new Vector3(163, 91, 163), 'chest' => new Vector3(163, 91, 156)]],
            "corner2" => new Vector3(200, 128, 200),
            "corner1" => new Vector3(-200, 0, -200)
        ];

        $this->maps['Planities'] = ["1Spawn" => new Vector3(917, 72, -1213),
            "2Spawn" => new Vector3(1214, 72, -1223),
            "3Spawn" => new Vector3(1204, 72, -1520),
            "4Spawn" => new Vector3(907, 72, -1510),
            "1Nexus" => new Vector3(895, 53, -1201),
            "2Nexus" => new Vector3(1226, 53, -1201),
            "3Nexus" => new Vector3(1226, 53, -1532),
            "4Nexus" => new Vector3(895, 53, -1532),
            "1Chest" => new Vector3(915, 73, -1197),
            "2Chest" => new Vector3(1230, 73, -1221),
            "3Chest" => new Vector3(1206, 73, -1536),
            "4Chest" => new Vector3(891, 73, -1512),
            "1Furnace" => new Vector3(914, 73, -1197),
            "2Furnace" => new Vector3(1230, 73, -1220),
            "3Furnace" => new Vector3(1207, 73, -1536),
            "4Furnace" => new Vector3(891, 73, -1513),
            "1EnderBrewing" => new Vector3(913, 72, -1198),
            "2EnderBrewing" => new Vector3(1229, 72, -1219),
            "3EnderBrewing" => new Vector3(1208, 72, -1535),
            "4EnderBrewing" => new Vector3(892, 72, -1514),
            //signs
            "1Brewing" => new Vector3(904, 73, -1203),
            "1Weapons" => new Vector3(904, 73, -1207),
            "2Brewing" => new Vector3(1224, 73, -1210),
            "2Weapons" => new Vector3(1220, 73, -1210),
            "3Brewing" => new Vector3(1217, 73, -1530),
            "3Weapons" => new Vector3(1217, 73, -1526),
            "4Brewing" => new Vector3(897, 73, -1523),
            "4Weapons" => new Vector3(901, 73, -1523),
            //diamonds
            "diamonds" => [ new Vector3(1055, 72, -1354), new Vector3(1052, 71, -1366), new Vector3(1057, 68, -1372),
                new Vector3(1067, 70, -1376), new Vector3(1068, 68, -1369), new Vector3(1074, 69, -1361), new Vector3(1065, 68, -1362),
                new Vector3(1081, 55, -1361), new Vector3(1066, 58, -1358), new Vector3(1055, 56, -1377), new Vector3(1066, 58, -1371)],
            "bosses" => [1 => ['name' => '§bFerwin', 'pos' => new Vector3(1199, 37, -1367), 'chest' => new Vector3(1199, 38, -1363)], 2 => ['name' => '§cCelariel', 'pos' => new Vector3(922, 37, -1366), 'chest' => new Vector3(922, 38, -1370)]],
            "corner2" => new Vector3(1235, 128, -1192),
            "corner1" => new Vector3(886, 0, -1541)
        ];

        $this->maps['Cavern'] = ["1Spawn" => new Vector3(-152, 31, -155),
            "2Spawn" => new Vector3(-137, 31, 152),
            "3Spawn" => new Vector3(170, 31, 137),
            "4Spawn" => new Vector3(155, 31, -170),
            "1Nexus" => new Vector3(-171, 44, -174),
            "2Nexus" => new Vector3(-156, 44, 171),
            "3Nexus" => new Vector3(189, 44, 156),
            "4Nexus" => new Vector3(174, 44, -189),
            "1Chest" => new Vector3(-167, 38, -170),
            "2Chest" => new Vector3(-152, 38, 167),
            "3Chest" => new Vector3(185, 38, 152),
            "4Chest" => new Vector3(170, 38, -185),
            "1Furnace" => new Vector3(-166, 38, -170),
            "2Furnace" => new Vector3(-152, 38, 166),
            "3Furnace" => new Vector3(184, 38, 152),
            "4Furnace" => new Vector3(170, 38, -184),
            "1EnderBrewing" => new Vector3(-164, 37, -170),
            "2EnderBrewing" => new Vector3(-152, 37, 164),
            "3EnderBrewing" => new Vector3(182, 37, 152),
            "4EnderBrewing" => new Vector3(170, 37, -182),
            //signs
            "1Brewing" => new Vector3(-164, 38, -170),
            "1Weapons" => new Vector3(-167, 38, -167),
            "2Brewing" => new Vector3(-152, 38, 164),
            "2Weapons" => new Vector3(-149, 38, 167),
            "3Brewing" => new Vector3(182, 38, 152),
            "3Weapons" => new Vector3(185, 38, 149),
            "4Brewing" => new Vector3(170, 38, -182),
            "4Weapons" => new Vector3(167, 38, -185),
            //diamonds
            "diamonds" => [ new Vector3(10, 2, -10), new Vector3(9, 6, -8), new Vector3(8, 4, -9),
                new Vector3(10, 6, -10), new Vector3(9, 1, -9), new Vector3(10, 5, -8)],
            "bosses" => [1 => ['name' => '§bFerwin', 'pos' => new Vector3(87, 83, -90), 'chest' => new Vector3(88, 85, -84)], 2 => ['name' => '§cCelariel', 'pos' => new Vector3(90, 83, 69), 'chest' => new Vector3(84, 85, 70)]],
            "corner2" => new Vector3(193, 128, 193),
            "corner1" => new Vector3(-193, 0, -193)
        ];

        $this->maps['Kingdoms'] = ["1Spawn" => new Vector3(3, 36, -153),
            "2Spawn" => new Vector3(1, 36, 175),
            "3Spawn" => new Vector3(-162, 36, 10),
            "4Spawn" => new Vector3(166, 36, 12),
            "1Nexus" => new Vector3(3, 46, -177),
            "2Nexus" => new Vector3(1, 46, 199),
            "3Nexus" => new Vector3(-186, 46, 10),
            "4Nexus" => new Vector3(190, 46, 12),
            "1Chest" => new Vector3(-10, 36, -151),
            "2Chest" => new Vector3(14, 36, 173),
            "3Chest" => new Vector3(-160, 36, 23),
            "4Chest" => new Vector3(164, 36, -1),
            "1Furnace" => new Vector3(-6, 36, -156),
            "2Furnace" => new Vector3(10, 36, 178),
            "3Furnace" => new Vector3(-165, 36, 19),
            "4Furnace" => new Vector3(169, 36, 3),
            "1EnderBrewing" => new Vector3(-103, 73, -112),
            "2EnderBrewing" => new Vector3(-112, 73, 228),
            "3EnderBrewing" => new Vector3(228, 73, 237),
            "4EnderBrewing" => new Vector3(237, 73, -103),
            //signs
            "1Brewing" => new Vector3(248, 25, 230),
            "1Weapons" => new Vector3(248, 26, 230),
            "2Brewing" => new Vector3(122, 25, -121),
            "2Weapons" => new Vector3(122, 26, -117),
            "3Brewing" => new Vector3(-215, 25, -123),
            "3Weapons" => new Vector3(-211, 26, -123),
            "4Brewing" => new Vector3(120, 25, 216),
            "4Weapons" => new Vector3(117, 26, 216),
            //diamonds
            "diamonds" => [ new Vector3(-3, 6, 2), new Vector3(11, 6, 16), new Vector3(0, 2, 17),
                new Vector3(-5, 3, 15), new Vector3(-3, 6, 20), new Vector3(-5, 5, 4), new Vector3(8, 2, 6),
                new Vector3(12, 5, 7), new Vector3(9, 2, 15), new Vector3(-40, 78, -40)],
            "bosses" => [1 => ['name' => '§bFerwin', 'pos' => new Vector3(-146, 13, 156), 'chest' => new Vector3(-146, 12, 162)], 2 => ['name' => '§cCelariel', 'pos' => new Vector3(150, 13, -134), 'chest' => new Vector3(150, 12, -140)]],
            "corner2" => new Vector3(202, 128, 211),
            "corner1" => new Vector3(-198, 0, -189)
        ];

        $this->maps['Cliffs'] = ["1Spawn" => new Vector3(195, 50, 211),
            "2Spawn" => new Vector3(212, 50, -67),
            "3Spawn" => new Vector3(-68, 50, -84),
            "4Spawn" => new Vector3(-86, 50, 195),
            "1Nexus" => new Vector3(220, 64, 227),
            "2Nexus" => new Vector3(227, 64, -93),
            "3Nexus" => new Vector3(-93, 64, -100),
            "4Nexus" => new Vector3(-102, 64, 220),
            "1Chest" => new Vector3(219, 50, 200),
            "2Chest" => new Vector3(200, 50, -92),
            "3Chest" => new Vector3(-75, 50, 219),
            "4Chest" => new Vector3(-94, 50, 198),
            "1Furnace" => new Vector3(197, 50, 219),
            "2Furnace" => new Vector3(219, 50, -70),
            "3Furnace" => new Vector3(-70, 50, -92),
            "4Furnace" => new Vector3(-94, 50, 197),
            "1EnderBrewing" => new Vector3(218, 50, 199),
            "2EnderBrewing" => new Vector3(199, 50, -91),
            "3EnderBrewing" => new Vector3(-91, 50, -72),
            "4EnderBrewing" => new Vector3(-74, 50, 218),
            //signs
            "1Brewing" => new Vector3(218, 51, 200),
            "1Weapons" => new Vector3(220, 51, 200),
            "2Brewing" => new Vector3(200, 51, -91),
            "2Weapons" => new Vector3(200, 51, -93),
            "3Brewing" => new Vector3(-75, 51, 218),
            "3Weapons" => new Vector3(-75, 51, 220),
            "4Brewing" => new Vector3(120, 51, 216),
            "4Weapons" => new Vector3(117, 51, 216),
            //diamonds
            "diamonds" => [ new Vector3(44, 47, 79), new Vector3(42, 45, 76), new Vector3(52, 43, 75),
                new Vector3(50, 47, 86), new Vector3(41, 45, 63), new Vector3(47, 46, 58), new Vector3(53, 43, 64),
                new Vector3(52, 45, 54), new Vector3(64, 45, 55), new Vector3(65, 43, 66), new Vector3(70, 47, 59),
                new Vector3(72, 46, 65), new Vector3(73, 46, 76), new Vector3(68, 44, 80), new Vector3(62, 43, 76),
                new Vector3(63, 49, 88)],
            "bosses" => [1 => ['name' => '§bFerwin', 'pos' => new Vector3(49, 14, 213), 'chest' => new Vector3(49, 13, 219)], 2 => ['name' => '§cCelariel', 'pos' => new Vector3(63, 15, -79), 'chest' => new Vector3(58, 15, -79)]],
            "corner2" => new Vector3(255, 128, 255),
            "corner1" => new Vector3(-128, 0, -128)
        ];

        $this->maps['Solumque'] = ["1Spawn" => new Vector3(115, 30, 125),
            "2Spawn" => new Vector3(-122, 30, 121),
            "3Spawn" => new Vector3(119, 30, -112),
            "4Spawn" => new Vector3(-118, 30, -116),
            "1Nexus" => new Vector3(113, 16, 119),
            "2Nexus" => new Vector3(-116, 16, 119),
            "3Nexus" => new Vector3(113, 16, -110),
            "4Nexus" => new Vector3(-116, 16, -110),
            "1Chest" => new Vector3(114, 30, 134),
            "2Chest" => new Vector3(-131, 30, 120),
            "3Chest" => new Vector3(128, 30, -111),
            "4Chest" => new Vector3(-117, 30, -125),
            "1Furnace" => new Vector3(114, 30, 133),
            "2Furnace" => new Vector3(-130, 30, 120),
            "3Furnace" => new Vector3(127, 30, -111),
            "4Furnace" => new Vector3(-117, 30, -124),
            "1EnderBrewing" => new Vector3(111, 30, 133),
            "2EnderBrewing" => new Vector3(-130, 30, 117),
            "3EnderBrewing" => new Vector3(127, 30, -108),
            "4EnderBrewing" => new Vector3(-114, 30, -124),
            //signs
            "1Brewing" => new Vector3(126, 29, 130),
            "1Weapons" => new Vector3(124, 29, 132),
            "2Brewing" => new Vector3(-127, 29, 132),
            "2Weapons" => new Vector3(-129, 29, 130),
            "4Brewing" => new Vector3(-129, 29, -121),
            "4Weapons" => new Vector3(-127, 29, -123),
            "3Brewing" => new Vector3(124, 29, -123),
            "3Weapons" => new Vector3(126, 29, -121),
            //diamonds
            "diamonds" => [ new Vector3(-2, 13, 15), new Vector3(-11, 14, 6), new Vector3(-2, 12, -2),
                new Vector3(7, 14, 3), new Vector3(-4, 11, 4), new Vector3(-1, 11, 8), new Vector3(-27, 21, -25),
                new Vector3(-31, 21, 30), new Vector3(24, 21, 34), new Vector3(28, 21, -21)],
            "bosses" => [1 => ['name' => '§bFerwin', 'pos' => new Vector3(-116, 34, 4), 'chest' => new Vector3(-116, 35, -2)], 2 => ['name' => '§cCelariel', 'pos' => new Vector3(113, 34, 5), 'chest' => new Vector3(113, 35, 11)]],
            "corner2" => new Vector3(130, 128, 136),
            "corner1" => new Vector3(-133, 0, -127)
        ];

        $this->maps['Districts'] = ["1Spawn" => new Vector3(314, 87, -54),
            "2Spawn" => new Vector3(-55, 87, -43),
            "3Spawn" => new Vector3(-44, 87, 326),
            "4Spawn" => new Vector3(325, 87, 315),
            "1Nexus" => new Vector3(331, 100, -54),
            "2Nexus" => new Vector3(-55, 100, -60),
            "3Nexus" => new Vector3(-61, 100, 326),
            "4Nexus" => new Vector3(325, 100, 332),
            "1Chest" => new Vector3(317, 88, -47),
            "2Chest" => new Vector3(-48, 88, -46),
            "3Chest" => new Vector3(-47, 88, 319),
            "4Chest" => new Vector3(318, 88, 318),
            "1Furnace" => new Vector3(317, 88, -48),
            "2Furnace" => new Vector3(-49, 88, -46),
            "3Furnace" => new Vector3(-47, 88, 320),
            "4Furnace" => new Vector3(319, 88, 318),
            "1EnderBrewing" => new Vector3(317, 88, -49),
            "2EnderBrewing" => new Vector3(-50, 88, -46),
            "3EnderBrewing" => new Vector3(-47, 88, 321),
            "4EnderBrewing" => new Vector3(320, 88, 318),
            //signs
            "1Brewing" => new Vector3(310, 83, -47),
            "1Weapons" => new Vector3(306, 83, -47),
            "2Brewing" => new Vector3(-48, 83, -39),
            "2Weapons" => new Vector3(-48, 83, -35),
            "4Brewing" => new Vector3(318, 83, 268),
            "4Weapons" => new Vector3(318, 83, 307),
            "3Brewing" => new Vector3(-40, 83, 319),
            "3Weapons" => new Vector3(-36, 83, 319),
            //diamonds
            "diamonds" => [ new Vector3(149, 81, 136), new Vector3(157, 78, 158), new Vector3(113, 78, 158),
                new Vector3(113, 78, 114), new Vector3(157, 78, 114), new Vector3(135, 80, 122), new Vector3(121, 79, 136),
                new Vector3(131, 80, 140), new Vector3(135, 81, 135), new Vector3(142, 85, 136), new Vector3(139, 87, 137),
                new Vector3(134, 92, 135), new Vector3(129, 90, 137), new Vector3(131, 96, 136)],
            "bosses" => [1 => ['name' => '§bFerwin', 'pos' => new Vector3(-7, 77, 122), 'chest' => new Vector3(-7, 77, 117)], 2 => ['name' => '§cCelariel', 'pos' => new Vector3(227, 77, 150), 'chest' => new Vector3(277, 77, 155)]],
            "corner2" => new Vector3(335, 128, 336),
            "corner1" => new Vector3(-65, 0, -64)
        ];

        /*$this->maps['Coastal'] = ["1Spawn" => new Vector3(-190, 44, 193),
            "2Spawn" => new Vector3(95, 44, -100),
            "3Spawn" => new Vector3(-194, 44, -96),
            "4Spawn" => new Vector3(99, 44, 189),
            "1Nexus" => new Vector3(-216, 39, 210),
            "2Nexus" => new Vector3(121, 39, -117),
            "3Nexus" => new Vector3(-211, 39, -122),
            "4Nexus" => new Vector3(116, 39, 215),
            "1Chest" => new Vector3(-217, 44, 215),
            "2Chest" => new Vector3(122, 44, -122),
            "3Chest" => new Vector3(-216, 44, -123),
            "4Chest" => new Vector3(121, 44, 216),
            "1Furnace" => new Vector3(-217, 44, 214),
            "2Furnace" => new Vector3(122, 44, -121),
            "3Furnace" => new Vector3(-215, 44, -123),
            "4Furnace" => new Vector3(120, 44, 216),
            //signs
            "1Brewing" => new Vector3(-217, 46, 214),
            "1Weapons" => new Vector3(-217, 46, 210),
            "2Brewing" => new Vector3(122, 46, -121),
            "2Weapons" => new Vector3(122, 46, -117),
            "3Brewing" => new Vector3(-215, 46, -123),
            "3Weapons" => new Vector3(-211, 46, -123),
            "4Brewing" => new Vector3(120, 46, 216),
            "4Weapons" => new Vector3(117, 46, 216),
            //diamonds
            "diamonds" => [ new Vector3(-52, 43, 50), new Vector3(-51, 43, 42), new Vector3(-57, 42, 50),
                new Vector3(-40, 43, 48), new Vector3(-47, 43, 44), new Vector3(-44, 43, 42), new Vector3(-47, 42, 36),
                new Vector3(-75, 45, 21), new Vector3(-72, 45, 76), new Vector3(-20, 45, 71), new Vector3(-23, 45, 18),
                new Vector3(-42, 42, 55)],
            "bosses" => [1 => ['name' => '§bFerwin', 'pos' => new Vector3(-204, 42, 47), 'chest' => new Vector3(-218, 44, 47)], 2 => ['name' => '§cCelariel', 'pos' => new Vector3(111, 42, 46), 'chest' => new Vector3(124, 44, 46)]],
            "corner2" => new Vector3(127, 128, 221),
            "corner1" => new Vector3(-222, 0, -128)
        ];*/
    }

    public static $kits = [
        "miner" =>
        "         ".TextFormat::GOLD.TextFormat::BOLD."You are the backbone.".TextFormat::RESET."\n"
        .TextFormat::GRAY."You support the war effort by gathering\n".TextFormat::GRAY."the raw materials your soldiers' gear needs.",
        "archer" =>
            "         ".TextFormat::GOLD.TextFormat::BOLD."You are the arrow.".TextFormat::RESET."\n"
            .TextFormat::GRAY."The last word in ranged combat, deal +1 damage with any bow.",
        //"spy" => "         ".TextFormat::GOLD.TextFormat::BOLD."You are the deceiver.".TextFormat::RESET."\n".TextFormat::GRAY."Vanish into thin air when still and sneaking!",
        "acrobat" =>
            "         ".TextFormat::GOLD.TextFormat::BOLD."You are the feather.".TextFormat::RESET."\n"
            .TextFormat::GRAY."You take no fall damage at all.",
        "operative" =>
            "         ".TextFormat::GOLD.TextFormat::BOLD."You are the <something>".TextFormat::RESET."\n"
            .TextFormat::GRAY."Carry out your plans for\n".TextFormat::GRAY."offense and safely escape!",
        "berserker" =>
            "         ".TextFormat::GOLD.TextFormat::BOLD."You are the anger.".TextFormat::RESET."\n".
            TextFormat::GRAY."Killing players will give an additional heart,\n"
            .TextFormat::GRAY."until you reach 15 total hearts.\n"
            .TextFormat::GRAY."Dying resets your hearts back to 7.",
        "lumberjack" =>
            "         ".TextFormat::GOLD.TextFormat::BOLD."You are the wedge.".TextFormat::RESET."\n"
            .TextFormat::GRAY."Gather wood with an efficiency axe and\n".TextFormat::GRAY."with the chance of gaining double yield,\n"
            .TextFormat::GRAY."ensuring quick work of any trees in your way!",
        "warrior" =>
            "         ".TextFormat::GOLD.TextFormat::BOLD."You are the Sword.".TextFormat::RESET."\n"
            .TextFormat::GRAY."You do +1 damage with any melee weapon",
        "handyman" =>"         ". TextFormat::GOLD.TextFormat::BOLD."You are the fixer.".TextFormat::RESET."\n"
            .TextFormat::GRAY."Every hit you get on an opposing team's nexus\n".TextFormat::GRAY."has a chance of repairing your nexus!\n"
            .TextFormat::GRAY."Phase 2: 20%\n"
            .TextFormat::GRAY."Phase 3: 15%\n"
            .TextFormat::GRAY."Phase 4: 10%\n"
            .TextFormat::GRAY."Phase 5: 7%",
        "civilian" =>
            "         ". TextFormat::GOLD.TextFormat::BOLD."You are the worker.".TextFormat::RESET."\n"
            .TextFormat::GRAY."You may not have the special abilities of the other classes, but don't worry!\n"
            .TextFormat::GRAY."Civilians fuel the war effort with their set of stone tools.\n".TextFormat::GRAY."Get to work!",
        "scout" =>
            "         ". TextFormat::GOLD.TextFormat::BOLD."You are the feet.".TextFormat::RESET."\n"
            .TextFormat::GRAY."Use your grapple to climb obstacles\n"
            .TextFormat::GRAY."grapple to climb obstacles"
    ];

    public function onHit($e){
        if(!$e instanceof EntityDamageByEntityEvent){
            return;
        }
        $entity = $e->getEntity();

        if(!$entity instanceof Human){
            return;
        }

        $name = null;
        foreach (self::$kits as $nick => $desc){
            if (stripos($entity->getNameTag(), $nick) !== false){
                $name = $nick;
                break;
            }
        }
        if ($name === null){
            return;
        }

        /** @var Item $item */
        $item = $e->getDamager()->getInventory()->getItemInHand();

        if($item->getId() === Item::GOLD_INGOT){
            new BuyKitQuery($this, $e->getDamager()->getName(), $name, BuyKitQuery::ACTION_BUY);
            /*foreach($this->ins as $arena){
                $arena->kitManager->buyKit($e->getDamager(), $name);
                break;
            }*/
        }else{
            /*$e->getDamager()->sendMessage(self::$kits[$name]);
            foreach($this->ins as $arena){
                if (\strtolower($name) != "spy" and in_array(\strtolower($this->mtcore->mysqlmgr->getRank(\strtolower($e->getDamager()->getName()))), ["vip", "vip+", "extra", "co-owner", "owner", "youtuber"])){
                    $e->getDamager()->sendMessage(TextFormat::GREEN."» You have already purchased this kit");
                }
                if ($arena->kitManager->hasKit($e->getDamager(), $name) or (in_array(\strtolower($this->mtcore->mysqlmgr->getRank(\strtolower($e->getDamager()->getName()))), ["vip+", "extra", "co-owner", "owner", "youtuber"]))){
                    $e->getDamager()->sendMessage(TextFormat::GREEN."» You have already purchased this kit");
                    break;
                }
                else {
                    $e->getDamager()->sendMessage(TextFormat::YELLOW."» To buy this kit use a gold ingot");
                    break;
                }

            }*/

            new BuyKitQuery($this, $e->getDamager()->getName(), $name, BuyKitQuery::ACTION_INFO);

        }
    }

    public static function getInstance(){
        return self::$instance;
    }

}