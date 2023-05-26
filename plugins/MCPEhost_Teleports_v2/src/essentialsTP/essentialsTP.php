<?php
/**
 * author: advocaite aka serverkart_rod
 */

namespace essentialsTP;

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
use pocketmine\utils\Config;



class essentialsTP extends PluginBase  implements CommandExecutor, Listener {
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

    public function onLoad(){

    }

    public function update_cooldown($name, $time, $type){
        $this->prepare = $this->db2->prepare("SELECT home,warp,spawn,player FROM cooldowns WHERE player =:name");
        $this->prepare->bindValue(":name", $name, SQLITE3_TEXT);
        $this->result = $this->prepare->execute();
        $sql = $this->fetchall();
        if (count($sql) > 0){

            switch ($type){
                case 'home':
                     $this->prepare = $this->db2->prepare("UPDATE cooldowns SET home = :time WHERE player = :name");
                    $this->prepare->bindValue(":name", $name, SQLITE3_TEXT);
                    $this->prepare->bindValue(":time", time(), SQLITE3_INTEGER);
                    $this->result = $this->prepare->execute();
                    return true;
                    break;
                case 'warp':
                    $this->prepare = $this->db2->prepare("UPDATE cooldowns SET warp = :time WHERE player = :name");
                    $this->prepare->bindValue(":name", $name, SQLITE3_TEXT);
                    $this->prepare->bindValue(":time", time(), SQLITE3_INTEGER);
                    $this->result = $this->prepare->execute();
                    return true;
                    break;
                case 'spawn':
                    $this->prepare = $this->db2->prepare("UPDATE cooldowns SET spawn = :time WHERE player = :name");
                    $this->prepare->bindValue(":name", $name, SQLITE3_TEXT);
                    $this->prepare->bindValue(":time", time(), SQLITE3_INTEGER);
                    $this->result = $this->prepare->execute();
                    return true;
                    break;
                default:
                    return false;
            }
        }
        else
        {

            switch ($type){
                case 'home':
                    $this->prepare = $this->db2->prepare("INSERT INTO cooldowns (home, warp, spawn, player) VALUES (:time, 0, 0, :name)");
                    $this->prepare->bindValue(":time", time(), SQLITE3_INTEGER);
                    $this->prepare->bindValue(":name", $name, SQLITE3_TEXT);

                    $this->result = $this->prepare->execute();
                    return true;
                    break;
                case 'warp':
                    $this->prepare = $this->db2->prepare("INSERT INTO cooldowns (home, warp, spawn, player) VALUES (0, :time, 0, :name)");
                    $this->prepare->bindValue(":time", time(), SQLITE3_INTEGER);
                    $this->prepare->bindValue(":name", $name, SQLITE3_TEXT);

                    $this->result = $this->prepare->execute();
                    return true;
                    break;
                case 'spawn':
                    $this->prepare = $this->db2->prepare("INSERT INTO cooldowns (home, warp, spawn, player) VALUES (0, 0, :time, :name)");
                    $this->prepare->bindValue(":time", time(), SQLITE3_INTEGER);
                    $this->prepare->bindValue(":name", $name, SQLITE3_TEXT);

                    $this->result = $this->prepare->execute();
                    return true;
                    break;
                default:
                    return false;
            }
        }
    }

    public function onPlayerDeath(PlayerDeathEvent $event)
    {
        $player = $event->getEntity();
        $this->death_loc[$player->getName()] = new Position(
            round($player->getX()),
            round($player->getY()),
            round($player->getZ()),
            $player->getLevel()
        );
    }
    public function onPlayerRespawn(PlayerRespawnEvent $event){
        $player = $event->getPlayer();
        if ( isset($this->death_loc[$player->getName()]) ){
            $this->username = $player->getName();
            $this->prepare = $this->db2->prepare("SELECT player,x,y,z,title,world FROM homes WHERE player =:name AND title =:bed");
            $this->prepare->bindValue(":name", $this->username, SQLITE3_TEXT);
            $this->prepare->bindValue(":bed", 'bed', SQLITE3_TEXT);
            $this->result = $this->prepare->execute();
            $sql = $this->fetchall();
            if (count($sql) > 0){
                $sql = $sql[0];
                foreach($player->getServer()->getLevels() as $aval_world => $curr_world)
                {
                    if ($sql['world'] == $curr_world->getName())
                    {
                        $pos = new Position((int) $sql['x'], (int) $sql['y'], (int) $sql['z'], $curr_world);

                        $player->teleport($pos);
                        $this->update_cooldown($this->username, time(), 'home');
                        $player->sendMessage(" §4➥ §bТелепортация домой прошла успешно .");
                        return true;
                    }
                }
            }
            else
            {
                    $this->world = $player->getLevel()->getName();
                    $this->prepare = $this->db2->prepare("SELECT x,y,z,world FROM spawns WHERE world = :world");
                    $this->prepare->bindValue(":world", $this->world, SQLITE3_TEXT);
                    $this->result = $this->prepare->execute();
                    $sql          = $this->fetchall();
                    if( count($sql) > 0 ) {
                        $sql = $sql[0];
                        foreach($player->getServer()->getLevels() as $aval_world => $curr_world)
                        {
                            if ($sql['world'] == $curr_world->getName())
                            {
                                $pos = new Position((int) $sql['x'], (int) $sql['y'], (int) $sql['z'], $curr_world);
                                $player->teleport($pos);
                                $this->update_cooldown($player->getName(), time(), 'spawn');
                                $player->sendMessage(" §4➥ §bТелепортация на точку возрождения прошла успешно .");
                                return true;

                            }
                        }
                    }
                    else
                    {
                        $player->teleport($player->getLevel()->getSpawnLocation());
                        $this->update_cooldown($player->getName(), time(), 'spawn');
                        $player->sendMessage(" §4➥ §bТелепортация на точку возрождения прошла успешно .");
                        return true;
                    }
            }
        }

    }

    public function onPlayerJoin(PlayerJoinEvent $event){
        $player = $event->getPlayer();
        //lets make sure user have
        $this->update_cooldown( $player->getName(), 0, 'home');
        $this->update_cooldown( $player->getName(), 0, 'warp');
        $this->update_cooldown( $player->getName(), 0, 'spawn');
    }

    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
        switch($cmd->getName()){
            case 'motd':
                $sender->sendMessage(TextFormat::GOLD.'[MOTD] '.TextFormat::WHITE.$this->config->get("MOTD"));
                return true;
                break;
            case 'home':
                if ($sender instanceof Player)
                {
                    $this->username = $sender->getName();
                    if (count($args) == 0)
                    {
                        $this->prepare = $this->db2->prepare("SELECT player,x,y,z,title,world FROM homes WHERE player =:name");
                        $this->prepare->bindValue(":name", $this->username, SQLITE3_TEXT);
                        $this->result = $this->prepare->execute();
                        $sql = $this->fetchall();
                        $home_list = null;
                        foreach ($sql as $ptu)
                        {
                            $home_list .= '['.TextFormat::GOLD.$ptu['title'].TextFormat::WHITE.'] ';
                        }
                        if($home_list != null){
                            $sender->sendMessage(" §4➥ §bВаши точки дома : ".$home_list);
                            return true;
                        }
                        else
                        {
                            $sender->sendMessage(TextFormat::RED." §4➥ §bУ Вас нет точек дома на данный момент .");
                            return true;
                        }

                    }else{
                        $this->prepare = $this->db2->prepare("SELECT home,warp,spawn,player FROM cooldowns WHERE player =:name AND home < :time");
                        $this->prepare->bindValue(":name", $this->username, SQLITE3_TEXT);
                        $this->prepare->bindValue(":time", (time() - $this->config->get("tp-home-cooldown")), SQLITE3_INTEGER);
                        $this->result = $this->prepare->execute();
                        $cool_sql = $this->fetchall();
                        if (count($cool_sql) > 0){
                            $this->home_loc = $args[0];
                            $this->prepare = $this->db2->prepare("SELECT player,title,x,y,z,world FROM homes WHERE player = :name AND title = :title");
                            $this->prepare->bindValue(":name", $this->username, SQLITE3_TEXT);
                            $this->prepare->bindValue(":title", $this->home_loc, SQLITE3_TEXT);
                            $this->result = $this->prepare->execute();
                            $sql = $this->fetchall();
                            if( count($sql) > 0 ) {
                                $sql = $sql[0];
                                foreach($this->getServer()->getLevels() as $aval_world => $curr_world)
                                {
                                    if ($sql['world'] == $curr_world->getName())
                                    {
                                        $pos = new Position((int) $sql['x'], (int) $sql['y'], (int) $sql['z'], $curr_world);

                                        $sender->teleport($pos);
                                        $this->update_cooldown($this->username, time(), 'home');
                                        $sender->sendMessage(" §4➥ §bТелепортация домой прошла успешно .");
                                        return true;
                                    }
                                }
                            }
                            else
                            {
                                $sender->sendMessage(TextFormat::RED." §4➥ §bУ Вас нет точки дома с таким именем .");
                                return true;
                            }
                        }
                        else
                        {
                            $sender->sendMessage(TextFormat::RED." §4➥ §bВы слишком часто отправляете запросы .");
                            return true;
                        }
                    }
                }
                else
                {
                    $sender->sendMessage(TextFormat::RED."This command can only be used in the game.");
                    return true;
                }
                break;
            case 'sethome':
                if ($sender instanceof Player)
                {
                    if((count($args) != 0) && (count($args) < 2))
                    {
                        $this->player_cords = array('x' => (int) $sender->getX(),'y' => (int) $sender->getY(),'z' => (int) $sender->getZ());
                        $this->username = $sender->getName();
                        $this->world = $sender->getLevel()->getName();
                        $this->home_loc = $args[0];
                        $this->prepare = $this->db2->prepare("SELECT player,title,x,y,z,world FROM homes WHERE player = :name AND title = :title");
                        $this->prepare->bindValue(":name", $this->username, SQLITE3_TEXT);
                        $this->prepare->bindValue(":title", $this->home_loc, SQLITE3_TEXT);
                        $this->result = $this->prepare->execute();
                        $sql          = $this->fetchall();
                        if( count($sql) > 0 )
                        {
                            $this->prepare = $this->db2->prepare("UPDATE homes SET world = :world, title = :title, x = :x, y = :y, z = :z WHERE player = :name AND title = :title");
                            $this->prepare->bindValue(":name", $this->username, SQLITE3_TEXT);
                            $this->prepare->bindValue(":title", $this->home_loc, SQLITE3_TEXT);
                            $this->prepare->bindValue(":world", $this->world, SQLITE3_TEXT);
                            $this->prepare->bindValue(":x", $this->player_cords['x'], SQLITE3_TEXT);
                            $this->prepare->bindValue(":y", $this->player_cords['y'], SQLITE3_TEXT);
                            $this->prepare->bindValue(":z", $this->player_cords['z'], SQLITE3_TEXT);
                            $this->result = $this->prepare->execute();

                        }
                        else
                        {
                            $this->prepare = $this->db2->prepare("INSERT INTO homes (player, title, world, x, y, z) VALUES (:name, :title, :world, :x, :y, :z)");
                            $this->prepare->bindValue(":name", $this->username, SQLITE3_TEXT);
                            $this->prepare->bindValue(":title", $this->home_loc, SQLITE3_TEXT);
                            $this->prepare->bindValue(":world", $this->world, SQLITE3_TEXT);
                            $this->prepare->bindValue(":x", $this->player_cords['x'], SQLITE3_TEXT);
                            $this->prepare->bindValue(":y", $this->player_cords['y'], SQLITE3_TEXT);
                            $this->prepare->bindValue(":z", $this->player_cords['z'], SQLITE3_TEXT);
                            $this->result = $this->prepare->execute();

                        }
                        $sender->sendMessage("[✔] Точка дома успешно установлена .");
                        return true;
                    }
                    else
                    {
                        $sender->sendMessage(TextFormat::RED."INVALID USAGE:");
                        return false;
                    }
                }
                else
                {
                    $sender->sendMessage(TextFormat::RED."This command can only be used in the game.");
                    return true;
                }
                break;
            case 'delhome':
                if ($sender instanceof Player)
                {
                    if((count($args) != 0) && (count($args) < 2))
                    {
                        $this->username = $sender->getName();
                        $this->home_loc = $args[0];
                        $this->prepare = $this->db2->prepare("SELECT * FROM homes WHERE player = :name AND title = :title");
                        $this->prepare->bindValue(":name", $this->username, SQLITE3_TEXT);
                        $this->prepare->bindValue(":title", $this->home_loc, SQLITE3_TEXT);
                        $this->result = $this->prepare->execute();
                        $sql          = $this->fetchall();
                        if( count($sql) > 0 )
                        {
                            $this->prepare = $this->db2->prepare("DELETE FROM homes WHERE player = :name AND title = :title");
                            $this->prepare->bindValue(":name", $this->username, SQLITE3_TEXT);
                            $this->prepare->bindValue(":title", $this->home_loc, SQLITE3_TEXT);
                            $this->result = $this->prepare->execute();
                            $sender->sendMessage("[✔] Точка дома ".TextFormat::GOLD.$this->home_loc.TextFormat::WHITE." успешно удалена .");
                            return true;
                        }
                        else
                        {
                            $sender->sendMessage(TextFormat::RED."[✘] Точки дома с таким названием не существует .");
                            return true;
                        }
                    }
                    else
                    {
                        $sender->sendMessage(TextFormat::RED."INVALID USAGE:");
                        return false;
                    }
                }
                else
                {
                    $sender->sendMessage(TextFormat::RED."This command can only be used in the game.");
                    return true;
                }
                break;
            case 'tpa':
                if ($sender instanceof Player)
                {
                    if((count($args) != 0) && (count($args) < 2))
                    {
                        if(trim(strtolower($sender->getName())) == trim(strtolower($args[0]))){$sender->sendMessage(TextFormat::RED.'[✘] Вы не можете телепортироваться к себе .');return true;}
                        $this->tp_sender = $sender->getName();
                        $this->tp_reciver = $args[0];
                        if($this->getServer()->getPlayer($this->tp_reciver) instanceof Player)
                        {
                            $this->getServer()->getPlayer($this->tp_reciver)->sendMessage(TextFormat::GOLD.$this->tp_sender.TextFormat::WHITE.' Отправить запрос на телепортацию .');
                            $this->getServer()->getPlayer($this->tp_reciver)->sendMessage('[✘] Используй : '.TextFormat::GOLD.'/tpaccept'.TextFormat::WHITE.' - Принять запрос на телепортацию .');
                            $this->getServer()->getPlayer($this->tp_reciver)->sendMessage('[✘] Используй : '.TextFormat::GOLD.'/tpdecline'.TextFormat::WHITE.' - Отклонить запрос на телепортацию .');
                            $this->getServer()->getPlayer($this->tp_reciver)->sendMessage('[✘] Подождите еще '.TextFormat::GOLD.'30 Секунд'.TextFormat::WHITE.' перед отправкой запроса .');
                            $this->prepare = $this->db2->prepare("INSERT INTO tp_requests (player, player_from, type, time, status) VALUES (:name, :name_from, :type, :time, :status)");
                            $this->prepare->bindValue(":name", trim(strtolower($this->tp_reciver)), SQLITE3_TEXT);
                            $this->prepare->bindValue(":name_from", trim(strtolower($this->tp_sender)), SQLITE3_TEXT);
                            $this->prepare->bindValue(":type", 'tpa', SQLITE3_TEXT);
                            $this->prepare->bindValue(":time", time(), SQLITE3_TEXT);
                            $this->prepare->bindValue(":status", 0, SQLITE3_TEXT);
                            $this->result = $this->prepare->execute();
                            return true;
                        }
                        else
                        {
                            $sender->sendMessage(TextFormat::RED.'[✘] Игрок с таким ником не найден .');
                            return true;
                        }
                    }
                    else
                    {
                        $sender->sendMessage(TextFormat::RED."INVALID USAGE:");
                        return false;
                    }
                }
                else
                {
                    $sender->sendMessage(TextFormat::RED."This command can only be used in the game.");
                    return true;
                }
                break;
            case 'tpahere':
                if ($sender instanceof Player)
                {
                    if((count($args) != 0) && (count($args) < 2))
                    {
                        if(trim(strtolower($sender->getName())) == trim(strtolower($args[0]))){$sender->sendMessage(TextFormat::RED.'[✘] Вы не можете телепортироваться к себе .');return true;}
                        $this->tp_sender = $sender->getName();
                        $this->tp_reciver = $args[0];
                        if($this->getServer()->getPlayer($this->tp_reciver) instanceof Player)
                        {
                            $this->getServer()->getPlayer($this->tp_reciver)->sendMessage(TextFormat::GOLD.$this->tp_sender.TextFormat::WHITE.' - Отправить запрос на телепортацию к Вам .');
                            $this->getServer()->getPlayer($this->tp_reciver)->sendMessage('[✘] Используй : '.TextFormat::GOLD.'/tpaccept'.TextFormat::WHITE.' -  Принять запрос на телепортацию .');
                            $this->getServer()->getPlayer($this->tp_reciver)->sendMessage('[✘] Используй : '.TextFormat::GOLD.'/tpdecline'.TextFormat::WHITE.' -  Отклонить запрос на телепортацию .');
                            $this->getServer()->getPlayer($this->tp_reciver)->sendMessage('[✘] Подождите еще '.TextFormat::GOLD.'30 Секунд'.TextFormat::WHITE.' перед отправкой запроса .');
                            $this->prepare = $this->db2->prepare("INSERT INTO tp_requests (player, player_from, type, time, status) VALUES (:name, :name_from, :type, :time, :status)");
                            $this->prepare->bindValue(":name", trim(strtolower($this->tp_reciver)), SQLITE3_TEXT);
                            $this->prepare->bindValue(":name_from", trim(strtolower($this->tp_sender)), SQLITE3_TEXT);
                            $this->prepare->bindValue(":type", 'tpahere', SQLITE3_TEXT);
                            $this->prepare->bindValue(":time", time(), SQLITE3_TEXT);
                            $this->prepare->bindValue(":status", 0, SQLITE3_TEXT);
                            $this->result = $this->prepare->execute();
                            return true;
                        }
                        else
                        {
                            $sender->sendMessage(TextFormat::RED.'[✘] Игрок с таким ником не найден .');
                            return true;
                        }
                    }
                    else
                    {
                        $sender->sendMessage(TextFormat::RED."INVALID USAGE:");
                        return false;
                    }
                }
                else
                {
                    $sender->sendMessage(TextFormat::RED."This command can only be used in the game.");
                    return true;
                }
                break;
            case 'tpaccept':
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
                                  $this->getServer()->getPlayer($sql['player_from'])->teleport($sender->getPosition());
                                  $this->prepare = $this->db2->prepare("UPDATE tp_requests SET status = 1 WHERE id = :id");
                                  $this->prepare->bindValue(":id", $sql['id'], SQLITE3_INTEGER);
                                  $this->result = $this->prepare->execute();
                                  return true;
                              }
                              else
                              {
                                  $sender->sendMessage(TextFormat::RED.'[✘] Игрок с таким ником не найден .');
                                  return true;
                              }
                              break;
                          case 'tpahere':
                              if($this->getServer()->getPlayer($sql['player_from']) instanceof Player)
                              {
                                  $sender->teleport($this->getServer()->getPlayer($sql['player_from'])->getPosition());
                                  $this->prepare = $this->db2->prepare("UPDATE tp_requests SET status = 1 WHERE id = :id");
                                  $this->prepare->bindValue(":id", $sql['id'], SQLITE3_INTEGER);
                                  $this->result = $this->prepare->execute();
                                  return true;
                              }
                              else
                              {
                                  $sender->sendMessage(TextFormat::RED.'[✘] Игрок с таким ником не найден .');
                                  return true;
                              }
                              break;
                          default:
                              return false;
                      }
                    }
                    else
                    {
                        $sender->sendMessage(TextFormat::RED."[✘] На данный момент у Вас нет активных запросов .");
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
                if ($sender instanceof Player)
                {
                    $sender->sendMessage(TextFormat::RED."[✘] На данный момент у Вас нет активных запросов .");
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
            case 'warp':
                if ($sender instanceof Player)
                {
                    if (count($args) == 0)
                    {
                        $this->prepare = $this->db2->prepare("SELECT x,y,z,world,title FROM warps");
                        $this->result = $this->prepare->execute();
                        $sql          = $this->fetchall();
                        $warp_list = null;
                        foreach ($sql as $ptu)
                        {
                            $warp_list .= '['.TextFormat::GOLD.$ptu['title'].TextFormat::WHITE.'] ';
                        }
                        if($warp_list != null){
                            $sender->sendMessage("[✔] Досутупные точки варпов : ".$warp_list);
                            return true;
                        }else{
                            $sender->sendMessage(TextFormat::RED."[✘] На данном сервере еще нет варпов .");
                            return true;
                        }

                    }else{
                        $this->prepare = $this->db2->prepare("SELECT home,warp,spawn,player FROM cooldowns WHERE player =:name AND warp < :time");
                        $this->prepare->bindValue(":name", $sender->getName(), SQLITE3_TEXT);
                        $this->prepare->bindValue(":time", ( time() - $this->config->get("tp-warp-cooldown")), SQLITE3_TEXT);
                        $this->result = $this->prepare->execute();
                        $cool_sql          = $this->fetchall();
                        if (count($cool_sql) > 0){
                            $this->warp_loc = $args[0];
                            $this->prepare = $this->db2->prepare("SELECT title,x,y,z,world FROM warps WHERE title = :title");
                            $this->prepare->bindValue(":title", $this->warp_loc, SQLITE3_TEXT);
                            $this->result = $this->prepare->execute();
                            $sql          = $this->fetchall();
                            if( count($sql) > 0 ) {
                                $sql = $sql[0];
                                foreach($this->getServer()->getLevels() as $aval_world => $curr_world)
                                {
                                    if ($sql['world'] == $curr_world->getName())
                                    {
                                        $pos = new Position((int) $sql['x'], (int) $sql['y'], (int) $sql['z'], $curr_world);

                                        $sender->teleport($pos);
                                        $this->update_cooldown($sender->getName(), time(), 'warp');
                                        $sender->sendMessage("[✔] Телепортация на варп ".TextFormat::GOLD.$sql['title']." прошла успешно .");
                                        return true;
                                    }
                                }
                            } else {
                                $sender->sendMessage(TextFormat::RED."[✘] Варпа с таким названием не существует .");
                                return true;
                            }
                        }
                        else
                        {
                            $sender->sendMessage(TextFormat::RED."[✘] Вы слишком часто отправляете запросы .");
                            return true;
                        }
                    }
                }
                else
                {
                    $sender->sendMessage(TextFormat::RED."This command can only be used in the game.");
                    return true;
                }
                break;
            case 'setwarp':
                if ($sender instanceof Player)
                {
                    if((count($args) != 0) && (count($args) < 2))
                    {
                        $this->player_cords = array('x' => (int) $sender->getX(),'y' => (int) $sender->getY(),'z' => (int) $sender->getZ());
                        $this->world = $sender->getLevel()->getName();
                        $this->warp_loc = $args[0];
                        $this->prepare = $this->db2->prepare("SELECT title,x,y,z,world FROM warps WHERE title = :title");
                        $this->prepare->bindValue(":title", $this->warp_loc, SQLITE3_TEXT);
                        $this->result = $this->prepare->execute();
                        $sql          = $this->fetchall();
                        if( count($sql) > 0 )
                        {
                            $sql = $sql[0];
                            $this->prepare = $this->db2->prepare("UPDATE warps SET world = :world, title = :title, x = :x, y = :y, z = :z WHERE title = :title");
                            $this->prepare->bindValue(":title", $this->warp_loc, SQLITE3_TEXT);
                            $this->prepare->bindValue(":world", $this->world, SQLITE3_TEXT);
                            $this->prepare->bindValue(":x", $this->player_cords['x'], SQLITE3_TEXT);
                            $this->prepare->bindValue(":y", $this->player_cords['y'], SQLITE3_TEXT);
                            $this->prepare->bindValue(":z", $this->player_cords['z'], SQLITE3_TEXT);
                            $this->result = $this->prepare->execute();

                        }
                        else
                        {
                            $this->prepare = $this->db2->prepare("INSERT INTO warps (title, world, x, y, z) VALUES (:title, :world, :x, :y, :z)");
                            $this->prepare->bindValue(":title", $this->warp_loc, SQLITE3_TEXT);
                            $this->prepare->bindValue(":world", $this->world, SQLITE3_TEXT);
                            $this->prepare->bindValue(":x", $this->player_cords['x'], SQLITE3_TEXT);
                            $this->prepare->bindValue(":y", $this->player_cords['y'], SQLITE3_TEXT);
                            $this->prepare->bindValue(":z", $this->player_cords['z'], SQLITE3_TEXT);
                            $this->result = $this->prepare->execute();

                        }

                        $sender->sendMessage("[✔] Точка варпа успешно установлена .");
                        return true;
                    }
                    else
                    {
                        $sender->sendMessage(TextFormat::RED."INVALID USAGE:");
                        return false;
                    }
                }
                else
                {
                    $sender->sendMessage(TextFormat::RED."This command can only be used in the game.");
                    return true;
                }
                break;
            case 'delwarp':
                if ($sender instanceof Player)
                {
                    if((count($args) != 0) && (count($args) < 2))
                    {

                        $this->warp_loc = $args[0];
                        $this->prepare = $this->db2->prepare("SELECT * FROM warps WHERE title = :title");
                        $this->prepare->bindValue(":title", $this->warp_loc, SQLITE3_TEXT);
                        $this->result = $this->prepare->execute();
                        $sql          = $this->fetchall();
                        if( count($sql) > 0 )
                        {
                            $this->prepare = $this->db2->prepare("DELETE FROM warps WHERE title = :title");
                            $this->prepare->bindValue(":title", $this->warp_loc, SQLITE3_TEXT);
                            $this->result = $this->prepare->execute();
                            $sender->sendMessage("[✔] Удаление точки варпа ".TextFormat::GOLD.$this->warp_loc.TextFormat::WHITE." прошло успешно .");
                            return true;
                        }
                        else
                        {
                            $sender->sendMessage(TextFormat::RED."[✘] Варпа с таким названием не существует .");
                            return true;
                        }
                    }
                    else
                    {
                        $sender->sendMessage(TextFormat::RED."INVALID USAGE:");
                        return false;
                    }
                }
                else
                {
                    $sender->sendMessage(TextFormat::RED."This command can only be used in the game.");
                    return true;
                }
                break;
            case 'wild':
                if ($sender instanceof Player)
                {
                    $this->world = $sender->getLevel()->getName();
                    foreach($this->getServer()->getLevels() as $aval_world => $curr_world)
                    {
                        if ($this->world == $curr_world->getName())
                        {
                            $pos = $sender->getLevel()->getSafeSpawn(new Vector3(rand('-'.$this->config->get("wild-MaxX"), $this->config->get("wild-MaxX")),rand(4,100),rand('-'.$this->config->get("wild-MaxY"), $this->config->get("wild-MaxY"))));
                           $sender->getLevel()->loadChunk($pos->getX(),$pos->getZ());

                            if($pos->getY() != 0)
                            {
                                $sender->teleport($pos);
                                $sender->sendMessage("[✔] Телепортация в случайное место прошла успешно .");
                                return true;
                            }
                            else
                            {
                                $sender->sendMessage("[✘] Невозможно телепортироваться .");
                                return true;
                            }

                        }
                    }

                }
                else
                {
                    $sender->sendMessage(TextFormat::RED."This command can only be used in the game.");
                    return true;
                }
                break;
            case 'back':
                if ($sender instanceof Player)
                {
                    if($this->death_loc[$sender->getName()] instanceof Position){
                        $sender->teleport($this->death_loc[$sender->getName()]);
                        $sender->sendMessage("[✔] Вы вернулись на точку Вашей смерти .");
                        unset($this->death_loc[$sender->getName()]);
                        return true;
                    }else{
                        $sender->sendMessage(TextFormat::RED."[✘] Для телепортации необходимо умереть .");
                        return true;
                    }
                }
                else
                {
                    $sender->sendMessage(TextFormat::RED."This command can only be used in the game.");
                    return true;
                }
                break;
            case 'spawn':
                if ($sender instanceof Player)
                {
                    $this->prepare = $this->db2->prepare("SELECT home,warp,spawn,player FROM cooldowns WHERE player =:name AND spawn < :time");
                    $this->prepare->bindValue(":name", $sender->getName(), SQLITE3_TEXT);
                    $this->prepare->bindValue(":time", ( time() - $this->config->get("tp-spawn-cooldown")), SQLITE3_TEXT);
                    $this->result = $this->prepare->execute();
                    $cool_sql          = $this->fetchall();
                    if (count($cool_sql) > 0){
                        $this->world = $sender->getLevel()->getName();
                        $this->prepare = $this->db2->prepare("SELECT x,y,z,world FROM spawns WHERE world = :world");
                        $this->prepare->bindValue(":world", $this->world, SQLITE3_TEXT);
                        $this->result = $this->prepare->execute();
                        $sql          = $this->fetchall();
                        if( count($sql) > 0 ) {
                            $sql = $sql[0];
                            foreach($this->getServer()->getLevels() as $aval_world => $curr_world)
                            {
                                if ($sql['world'] == $curr_world->getName())
                                {
                                    $pos = new Position((int) $sql['x'], (int) $sql['y'], (int) $sql['z'], $curr_world);

                                    $sender->teleport($pos);
                                    $this->update_cooldown($sender->getName(), time(), 'spawn');
                                    $sender->sendMessage("[✔] Возвращение на точку возрождения прошло успешно .");
                                    return true;

                                }
                            }
                        }
                        else
                        {
                            $sender->teleport($sender->getLevel()->getSpawnLocation());
                            $this->update_cooldown($sender->getName(), time(), 'spawn');
                            $sender->sendMessage("[✔] Возвращение на точку возрождения прошло успешно .");
                            return true;
                        }
                    }
                    else
                    {
                        $sender->sendMessage(TextFormat::RED."[✘] Произошла ошибка телепортации .");
                        return true;
                    }
                }
                else
                {
                    $sender->sendMessage(TextFormat::RED."This command can only be used in the game.");
                    return true;
                }
                break;
            case 'setspawn':
                if ($sender instanceof Player)
                {
                    if(count($args) == 0)
                    {
                        $this->player_cords = array('x' => (int) $sender->getX(),'y' => (int) $sender->getY(),'z' => (int) $sender->getZ());
                        $this->world = $sender->getLevel()->getName();
                        $this->prepare = $this->db2->prepare("SELECT x,y,z,world FROM spawns WHERE world = :world");
                        $this->prepare->bindValue(":world", $this->world, SQLITE3_TEXT);
                        $this->result = $this->prepare->execute();
                        $sql          = $this->fetchall();
                        if( count($sql) > 0 )
                        {
                            $this->prepare = $this->db2->prepare("UPDATE spawns SET world = :world, x = :x, y = :y, z = :z WHERE world = :world");
                            $this->prepare->bindValue(":world", $this->world, SQLITE3_TEXT);
                            $this->prepare->bindValue(":x", $this->player_cords['x'], SQLITE3_TEXT);
                            $this->prepare->bindValue(":y", $this->player_cords['y'], SQLITE3_TEXT);
                            $this->prepare->bindValue(":z", $this->player_cords['z'], SQLITE3_TEXT);
                            $this->result = $this->prepare->execute();
                        }
                        else
                        {
                            $this->prepare = $this->db2->prepare("INSERT INTO spawns (world, x, y, z) VALUES (:world, :x, :y, :z)");
                            $this->prepare->bindValue(":world", $this->world, SQLITE3_TEXT);
                            $this->prepare->bindValue(":x", $this->player_cords['x'], SQLITE3_TEXT);
                            $this->prepare->bindValue(":y", $this->player_cords['y'], SQLITE3_TEXT);
                            $this->prepare->bindValue(":z", $this->player_cords['z'], SQLITE3_TEXT);
                            $this->result = $this->prepare->execute();
                        }

                        $sender->sendMessage("[✔] Точка возрождения установлена .");
                        return true;
                    }
                    else
                    {
                        $sender->sendMessage(TextFormat::RED."[✘] Произошла ошибка .");
                        return false;
                    }
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

        $this->getLogger()->info(TextFormat::GREEN."essentialsTP+ loading...");
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
        $this->getLogger()->info(TextFormat::GREEN."[INFO] loading [".TextFormat::GOLD."config.yml".TextFormat::GREEN."]....");
        $this->tpa_cooldown = time() - $this->config->get("tpa-here-cooldown");
        $this->getLogger()->info(TextFormat::GREEN."[INFO] loading [".TextFormat::GOLD."config.yml".TextFormat::GREEN."] DONE");
        $this->getLogger()->info(TextFormat::GREEN."essentialsTP+ loaded!");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

    }

    public function onDisable(){
        $this->prepare->close();
        $this->getLogger()->info("essentialsTP+ Disabled");
    }



}
