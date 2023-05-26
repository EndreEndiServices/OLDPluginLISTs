<?php

namespace FTeleport;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\Listener;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\permission\Permissible;
use pocketmine\IPlayer;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\utils\TextFormat as F;
use pocketmine\utils\Config;

class FTeleport extends PluginBase  implements CommandExecutor, Listener {
    private $db2;
    public $username;
    public $world;
    public $home_loc;
    public $warp_loc;
    public $params;
    public $death_loc;
    public $config;
    public $player_cords;
    public $tp_sender;
    public $tp_reciver;
    public $result;
    public $prepare;

    public function fetchall(){
        $row = array();

        $i = 0;

        while($res = $this->result->fetchArray(SQLITE3_ASSOC)){

            $row[$i] = $res;
            $i++;

        }
        return $row;
    }
	
    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
        switch($cmd->getName())
		{
            case 'tpa':
                if ($sender instanceof Player)
                {
                    if((count($args) != 0) && (count($args) < 2))
                    {
                        if(trim(strtolower($sender->getName())) == trim(strtolower($args[0]))){$sender->sendMessage(F::RED."§7(§aТелепортация§7) Ты не можешь телепортироваться к себе.");return true;}
                        $this->tp_sender = $sender->getName();
                        $this->tp_reciver = $args[0];
                        if($this->getServer()->getPlayer($args[0]) instanceof Player)
                        {
							$sender->sendMessage("§7(§aТелепортация§7)§f Ты §aотправил §fзапрос на телепортацию!");
                            $this->getServer()->getPlayer($args[0])->sendMessage("§7(§aТелепортация§7)§f Игрок ".F::AQUA.$this->tp_sender.F::WHITE." отправил(а) запрос на телепортацию к тебе");
                            $this->getServer()->getPlayer($args[0])->sendMessage("§7(§aТелепортация§7)§f Напиши: ".F::AQUA."/tpaccept".F::WHITE." или ".F::AQUA."/tpc".F::WHITE.", чтобы принять запрос");
                            $this->prepare = $this->db2->prepare("INSERT INTO tp_requests (player, player_from, type, time, status) VALUES (:name, :name_from, :type, :time, :status)");
                            $this->prepare->bindValue(":name", trim(strtolower($args[0])), SQLITE3_TEXT);
                            $this->prepare->bindValue(":name_from", trim(strtolower($this->tp_sender)), SQLITE3_TEXT);
                            $this->prepare->bindValue(":type", 'tpa', SQLITE3_TEXT);
                            $this->prepare->bindValue(":time", time(), SQLITE3_TEXT);
                            $this->prepare->bindValue(":status", 0, SQLITE3_TEXT);
                            $this->result = $this->prepare->execute();
                            return true;
                        }
                        else
                        {
                            $sender->sendMessage("§7(§aТелепортация§7)§c Игрок не онлайн или ты ввел(а) неверный ник.");
                            return true;
                        }
                    }
                    else
                    {
                        $sender->sendMessage("§7(§aТелепортация§7)§f Используй: ".F::AQUA."/tpa <ник>");
                        return false;
                    }
                }
                else
                {
                    $sender->sendMessage(TextFormat::RED."Команда используется только в игре!");
                    return true;
                }
                break;
            case 'tpaccept':
			case 'tpc':
                if ($sender instanceof Player)
                {
                    $this->prepare = $this->db2->prepare("SELECT id,player, player_from, type, time, status FROM tp_requests WHERE time > :time AND player = :player AND status = 0");
                    $this->prepare->bindValue(":time", (time() - $this->config->get("tpa-here-cooldown")), SQLITE3_TEXT);
                    $this->prepare->bindValue(":player", trim(strtolower($sender->getName())), SQLITE3_TEXT);
                    $this->result = $this->prepare->execute();
                    $sql          = $this->fetchall();
                    if(count($sql) > 0)
                    {
                       $sql = $sql[0];
                      switch($sql['type'])
                      {
                          case 'tpa':
                              if($this->getServer()->getPlayer($sql['player_from']) instanceof Player)
                              {
								  $sender->sendMessage("§7(§aТелепортация§7)§f Ты принял запрос на телепортацию, от игрока ".F::AQUA.$this->getServer()->getPlayer($sql['player_from'])->getName());
								  $this->getServer()->getPlayer($sql['player_from'])->sendMessage("§7(§aТелепортация§7)§f Игрок принял твой запрос. ".F::AQUA."Телепортация..");
                                  $this->getServer()->getPlayer($sql['player_from'])->teleport($sender->getPosition());
                                  $this->prepare = $this->db2->prepare("UPDATE tp_requests SET status = 1 WHERE id = :id");
                                  $this->prepare->bindValue(":id", $sql['id'], SQLITE3_INTEGER);
                                  $this->result = $this->prepare->execute();
                                  return true;
                              }
                              else
                              {
                                  $sender->sendMessage(TextFormat::RED.'');
                                  return true;
                              }
                              break;
                          default:
                              return false;
                      }
                    }
                    else
                    {
                        $sender->sendMessage("§7(§aТелепортация§7)§f У вас нет §cактивных §fзапросов");
                        $this->prepare = $this->db2->prepare("DELETE FROM tp_requests WHERE time < :time AND player = :player AND status = 0");
                        $this->prepare->bindValue(":time", (time() - $this->config->get("tpa-here-cooldown")), SQLITE3_TEXT);
                        $this->prepare->bindValue(":player", trim(strtolower($sender->getName())), SQLITE3_TEXT);
                        $this->result = $this->prepare->execute();
                        return true;
                    }
                }
                 else
                {
                    $sender->sendMessage(TextFormat::RED."This command can only be used in the game.");
                    return true;
                }
                break;
            case 'tpdeny':
			case 'tpd':
                if ($sender instanceof Player)
                {
                    $sender->sendMessage("§7(§aТелепортация§7)§f У вас нет §cактивных §fзапросов");
                    $this->prepare = $this->db2->prepare("DELETE FROM tp_requests WHERE player = :player AND status = 0");
                    $this->prepare->bindValue(":player", trim(strtolower($sender->getName())), SQLITE3_TEXT);
                    $this->result = $this->prepare->execute();
                    return true;
                }
                else
                {
                    $sender->sendMessage(TextFormat::RED."This command can only be used in the game.");
                    return true;
                }
                break;
            default:
                return false;
            }
        }
    public function create_db(){
        $this->prepare = $this->db2->prepare("SELECT * FROM sqlite_master WHERE type='table' AND name='homes'");
        $this->result = $this->prepare->execute();
        $sql = $this->fetchall();
        $count = count($sql);
        if ($count == 0){
            $this->prepare = $this->db2->prepare("CREATE TABLE homes (
                      id INTEGER PRIMARY KEY,
                      player TEXT,
                      x TEXT,
                      y TEXT,
                      z TEXT,
                      title TEXT,
                      world TEXT)");
            $this->result = $this->prepare->execute();
            $this->getLogger()->info(TextFormat::AQUA."essentialsTP+ Homes database created!");
        }
        $this->prepare = $this->db2->prepare("SELECT * FROM sqlite_master WHERE type='table' AND name='tp_requests'");
        $this->result = $this->prepare->execute();
        $sql2 = $this->fetchall();
        $count2 = count($sql2);
        if ($count2 == 0){
            $this->prepare = $this->db2->prepare("CREATE TABLE tp_requests (
                      id INTEGER PRIMARY KEY,
                      player TEXT,
                      player_from TEXT,
                      type TEXT,
                      time TEXT,
                      status TEXT)");
            $this->result = $this->prepare->execute();
            $this->getLogger()->info(TextFormat::AQUA."essentialsTP+ request database created!");
        }
        $this->prepare = $this->db2->prepare("SELECT * FROM sqlite_master WHERE type='table' AND name='warps'");
        $this->result = $this->prepare->execute();
        $sql3 = $this->fetchall();
        $count3 = count($sql3);
        if($count3 == 0){
            $this->prepare = $this->db2->prepare("CREATE TABLE warps (
                      id INTEGER PRIMARY KEY,
                      x TEXT,
                      y TEXT,
                      z TEXT,
                      world TEXT,
                      title TEXT)");
            $this->result = $this->prepare->execute();
            $this->getLogger()->info(TextFormat::AQUA."essentialsTP+ warps database created!");
        }
        $this->prepare = $this->db2->prepare("SELECT * FROM sqlite_master WHERE type='table' AND name='spawns'");
        $this->result = $this->prepare->execute();
        $sql4 = $this->fetchall();
        $count4 = count($sql4);
        if($count4 == 0){
            $this->prepare = $this->db2->prepare("CREATE TABLE spawns (
                      id INTEGER PRIMARY KEY,
                      x TEXT,
                      y TEXT,
                      z TEXT,
                      world TEXT
                      )");
            $this->result = $this->prepare->execute();
            $this->getLogger()->info(TextFormat::AQUA."essentialsTP+ Spawns database created!");
        }
        $this->prepare = $this->db2->prepare("SELECT * FROM sqlite_master WHERE type='table' AND name='cooldowns'");
        $this->result = $this->prepare->execute();
        $sql5 = $this->fetchall();
        $count5 = count($sql5);
        if($count5 == 0){
            $this->prepare = $this->db2->prepare("CREATE TABLE cooldowns (
                      id INTEGER PRIMARY KEY,
                      home INTEGER,
                      warp INTEGER,
                      spawn INTEGER,
                      player TEXT
                      )");
            $this->result = $this->prepare->execute();
            $this->getLogger()->info(TextFormat::AQUA."essentialsTP+ cooldown database created!");
        }
    }
    public function check_config(){

        $this->config = new Config($this->getDataFolder()."config.yml", Config::YAML, array());
        $this->config->set('plugin-name',"essentalsTp+");
        $this->config->save();

        if(!$this->config->get("sqlite-dbname"))
        {
            $this->config->set("sqlite-dbname", "essentials_tp");
            $this->config->save();
        }

        if($this->config->get("tpa-here-cooldown") == false)
        {
            $this->config->set("tpa-here-cooldown", "30");
            $this->config->save();
        }
        if($this->config->get("tp-home-cooldown") == false)
        {
            $this->config->set("tp-home-cooldown", "5");
            $this->config->save();
        }
        if($this->config->get("tp-warp-cooldown") == false)
        {
            $this->config->set("tp-warp-cooldown", "5");
            $this->config->save();
        }
        if($this->config->get("tp-spawn-cooldown") == false)
        {
            $this->config->set("tp-spawn-cooldown", "5");
            $this->config->save();
        }
        if($this->config->get("MOTD") == false)
        {
            $this->config->set("MOTD", "EssintialsTP+ Welcomes you please change this motd in config");
            $this->config->save();
        }
        if($this->config->get("wild-MaxX") == false)
        {
            $this->config->set("wild-MaxX", "300");
            $this->config->save();
        }
        if($this->config->get("wild-MaxY") == false)
        {
            $this->config->set("wild-MaxY", "300");
            $this->config->save();
        }
    }
    public function onEnable(){
        @mkdir($this->getDataFolder());
        $this->check_config();
        try{
            if(!file_exists($this->getDataFolder().$this->config->get("sqlite-dbname").'.db')){
                $this->db2 = new \SQLite3($this->getDataFolder().$this->config->get("sqlite-dbname").'.db', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
            }else{
                $this->db2 = new \SQLite3($this->getDataFolder().$this->config->get("sqlite-dbname").'.db', SQLITE3_OPEN_READWRITE);
            }
        }
        catch (Exception $e)
        {
            echo $e->getMessage();
            die();
        }
        $this->create_db();
        $this->tpa_cooldown = time() - $this->config->get("tpa-here-cooldown");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

    }
    public function onDisable(){
        $this->prepare->close();
    }
}
