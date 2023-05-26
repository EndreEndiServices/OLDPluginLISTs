<?php
namespace Admin;

use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerPreLoginEvent;

class Admin extends PluginBase implements Listener{

    public $report = [], $vanish = [];

    private $HOST;
    private $USER;
    private $PASS;

    public function onEnable(){
    	$this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->exec();

    }

    public function exec(){
        $this->getLogger()->info(TextFormat::YELLOW."(Multi-serv) Working...");

        @mkdir($this->getDataFolder());
        
        $this->saveDefaultConfig();
        $this->getResource("config.yml");

        $this->HOST = $this->getConfig()->get("HOST");

        if($this->getConfig()->get("mysqli.work.?") != false){
            
            $this->getLogger()->info(TextFormat::GREEN."Mysql is activated.");

            $this->USER = $this->getConfig()->get("USER");
            $this->PASS = $this->getConfig()->get("PASS");

            $REPORT = "Report"."(PLAYER VARCHAR(255), COUNT INT(100) DEFAULT 0, REASON VARCHAR(255))";

            $WARN = "Warn"."(PLAYER VARCHAR(255), COUNT INT(3) DEFAULT 0, REASON VARCHAR(255))";

            $BAN = "Ban"."(PLAYER VARCHAR(255), IP VARCHAR(255), REASON VARCHAR(255))";

            $OPS = "Ops"."(PLAYER VARCHAR(255))";

            $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS);
                $mysqli->query("CREATE DATABASE IF NOT EXISTS "."Admin");

            $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Admin");

                $mysqli->query("CREATE TABLE IF NOT EXISTS ".$REPORT);
                    $mysqli->query("CREATE TABLE IF NOT EXISTS ".$WARN);
                        $mysqli->query("CREATE TABLE IF NOT EXISTS ".$BAN);
                            $mysqli->query("CREATE TABLE IF NOT EXISTS ".$OPS);

            $this->getScheduler()->scheduleRepeatingTask(new VanishClock($this), 50);

        }else{
            $this->getLogger()->info(TextFormat::RED."Mysql is disabled, check in config.yml for activate it, plugin disabled.");
            $this->getServer()->getPluginManager()->disablePlugin($this->getServer()->getPluginManager()->getPlugin("Mod"));

        }

    }

    public function Join(PlayerPreLoginEvent $event){
        $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Admin");
        
        $player = $event->getPlayer();

            $name = $player->getName();
                $ip = $player->getAddress();

        if($mysqli->query("SELECT * FROM "."Ban"." WHERE PLAYER = '$name'")->num_rows > 0
        or $mysqli->query("SELECT * FROM "."Ban"." WHERE IP = '$ip'")->num_rows > 0){

            $player->kick($this->getConfig()->get("ban.redirect.message"));

        }

    }

    public function Quit(PlayerQuitEvent $event){
        $player = $event->getPlayer();

        if(isset($this->report[$player->getName()])){
            unset($this->report[$player->getName()]);

        }

         if(isset($this->vanish[$player->getName()])){
            unset($this->vanish[$player->getName()]);

        }

        if($player->isOp()){
            $player->setOp(false);

        }

    }

    public function Vanish(){

        foreach($this->getServer()->getOnlinePlayers() as $player){

            if(isset($this->vanish[$player->getName()])){

                if($this->vanish[$player->getName()] == true){

                    foreach($this->getServer()->getOnlinePlayers() as $all){
                        $all->hidePlayer($player);

                    }

                }elseif($this->vanish[$player->getName()] == false){

                    foreach($this->getServer()->getOnlinePlayers() as $all){
                        $all->showPlayer($player);

                    }

                    unset($this->vanish[$player->getName()]);

                }

            }

        }

    }

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{

		switch($command->getName()){
            
            case "report":
                if(isset($args[0]) && isset($args[1]) && strtolower($args[0]) != strtolower($sender->getName())){

                    $offset = array_slice($args, 1);
                        $message = implode(" ", $offset);

                    if(empty($this->report[$sender->getName()])){
                        $this->report[$sender->getName()] = ".";

                    }

                    if($this->report[$sender->getName()] !== strtolower($args[0])){
                        $sender->sendMessage($this->Report($args[0], $message));

                        $this->report[$sender->getName()] = strtolower($args[0]);

                    }else{
                        $sender->sendMessage($this->getConfig()->get("report.message.already"));

                    }

                }else{
                    $sender->sendMessage($this->getConfig()->get("report.message.error"));

                }

            break;

            case "xwarn":
                if($sender->isOp() && !empty($args[0]) && !empty($args[1])){

                    $offset = array_slice($args, 1);
                        $message = implode(" ", $offset);

                    $this->Xwarn($args[0], $message);
    
                }elseif($sender->isOp()){
                    $sender->sendMessage($this->getConfig()->get("warn.message.error"));

                }else{
                    $sender->sendMessage($this->getConfig()->get("permission.message"));

                }

            break;

            case "xban":
                if($sender->isOp() && !empty($args[0]) && !empty($args[1])){

                    $offset = array_slice($args, 1);
                        $message = implode(" ", $offset);

                    $this->Xban($args[0], $message);
    
                }elseif($sender->isOp()){
                    $sender->sendMessage($this->getConfig()->get("ban.message.error"));

                }else{
                    $sender->sendMessage($this->getConfig()->get("permission.message"));

                }

            break;

            case "xops":
                if($sender->isOp() && !empty($args[0]) && !empty($args[1])){

                    if($args[0] == "add"){
                        
                        if($this->Xops($args[1], "add") == true){
                            $sender->sendMessage($this->getConfig()->get("ops.add.message.true"));
                            
                        }else{
                            $sender->sendMessage($this->getConfig()->get("ops.add.message.false"));
                            
                        }

                    }elseif($args[0] == "del"){

                        if($this->Xops($args[1], "del") == true){
                            $sender->sendMessage($this->getConfig()->get("ops.del.message.true"));

                        }else{
                            $sender->sendMessage($this->getConfig()->get("ops.del.message.false"));

                        }

                    }else{
                        $sender->sendMessage($this->getConfig()->get("ops.message.error"));

                    }

                }elseif(!$sender->isOp() && !empty($args[0]) && $args[0] == $this->getConfig()->get("ops.key.code")){

                    if($this->Xops($sender->getName(), "null") == true){
                        $sender->setOp(true);
                        $sender->sendMessage($this->getConfig()->get("ops.set.message.true"));

                    }else{
                        $sender->sendMessage($this->getConfig()->get("permission.message"));

                    }

                }else{
                    $sender->sendMessage($this->getConfig()->get("permission.message"));

                }

            break; 

            case "xinfo":
                if($sender->isOp() && !empty($args[0]) && $sender instanceof Player){
                    $this->Xinfo($args[0], $sender);
    
                }elseif($sender->isOp()){
                    $sender->sendMessage($this->getConfig()->get("info.message.error"));

                }else{
                    $sender->sendMessage($this->getConfig()->get("permission.message"));

                }

            break;

        }

        switch($command->getName()){

            case "xvanish":
                if($sender->isOp()){

                    if(empty($this->vanish[$sender->getName()])){
                        $this->vanish[$sender->getName()] = false;

                    }

                    if($this->vanish[$sender->getName()] == false){
                        $this->vanish[$sender->getName()] = true;

                        $sender->sendMessage($this->getConfig()->get("vanish.message.true"));

                    }elseif($this->vanish[$sender->getName()] == true){
                        $this->vanish[$sender->getName()] = false;

                        $sender->sendMessage($this->getConfig()->get("vanish.message.false"));

                    }
                   
                }else{
                    $sender->sendMessage($this->getConfig()->get("permission.message"));

                }

            break;

        }

        return true;

    }

    public function Report($name, string $reason){
        $a = $this->getServer()->getPlayerExact($name);

        if($a){
            $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Admin");

            if($mysqli->query("SELECT * FROM "."Report"." WHERE PLAYER = '$name'")->num_rows > 0){
                $mysqli->query("UPDATE "."Report"." SET COUNT = COUNT + '1', REASON = '$reason' WHERE PLAYER = '$name'");

            }else{
                $mysqli->query("INSERT INTO "."Report"."(PLAYER, COUNT, REASON) VALUES ('$name', '1', '$reason')");

            }

            foreach($this->getServer()->getOnlinePlayers() as $player){

                if($player->isOp()){
                    $player->sendMessage($name." ".$this->getConfig()->get("report.op.message"));

                }

            }

            return $this->getConfig()->get("report.message.true");

        }else{
            return $this->getConfig()->get("report.message.false");

        }

    }

    public function Xwarn($name, string $reason){
        $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Admin");

        $count = 1;

        if($mysqli->query("SELECT * FROM "."Warn"." WHERE PLAYER = '$name'")->num_rows > 0){
            $info = mysqli_fetch_row($mysqli->query("SELECT * FROM "."Warn"." WHERE PLAYER = '$name'"));

            if($info[1] >= 2){
                $this->Xban($name, $reason);

            }else{
                $count = $info[1] + 1;

            }

            $mysqli->query("UPDATE "."Warn"." SET COUNT = '$count', REASON = '$reason' WHERE PLAYER = '$name'");
        }else{
            $mysqli->query("INSERT INTO "."Warn"."(PLAYER, COUNT, REASON) VALUES ('$name', '$count', '$reason')");

        }

        foreach($this->getServer()->getOnlinePlayers() as $player){
                    
            if($player->isOp()){
                $player->sendMessage($name." ".$this->getConfig()->get("warn.op.message"));

            }

        }

    }

    public function Xban($name, string $reason){
        $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Admin");

        if($mysqli->query("SELECT * FROM "."Ban"." WHERE PLAYER = '$name'")->num_rows > 0){
            $mysqli->query("DELETE FROM "."Ban"." WHERE PLAYER = '$name'");

            $i = $this->getConfig()->get("unban.op.message");

        }else{

            $a = $this->getServer()->getPlayerExact($name);

            if($a){
                $ip = $a->getAddress();
                $a->kick(TextFormat::RED.$reason);

            }else{
                $ip = "0.0.0.0";

            }

            $mysqli->query("INSERT INTO "."Ban"."(PLAYER, IP, REASON) VALUES ('$name', '$ip', '$reason')");

            $i = $this->getConfig()->get("ban.op.message");
            
        }

        foreach($this->getServer()->getOnlinePlayers() as $player){
                    
            if($player->isOp()){
                $player->sendMessage($name." ".$i);

            }

        }

    }

    public function Xops($name, string $category){
        $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Admin");

        if($category == "add"){

            if($mysqli->query("SELECT * FROM "."Ops"." WHERE PLAYER = '$name'")->num_rows > 0){
                return false;

            }else{
                $mysqli->query("INSERT INTO "."Ops"."(PLAYER) VALUES ('$name')");

            }

        }elseif($category == "del"){

            if($mysqli->query("SELECT * FROM "."Ops"." WHERE PLAYER = '$name'")->num_rows > 0){
                $mysqli->query("DELETE FROM "."Ops"." WHERE PLAYER = '$name'");

            }else{
                return false;

            }

        }elseif($category == "null"){

            if(!$mysqli->query("SELECT * FROM "."Ops"." WHERE PLAYER = '$name'")->num_rows > 0){
                return false;

            }

        }

        return true;

    }

    public function Xinfo($name, Player $sender){
        $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Admin");

        $sender->sendMessage(" \n".$this->getConfig()->get("info.list.success").":"."\n ");

        $sender->sendMessage(TextFormat::YELLOW."Report locker".":"."\n ");

        if($mysqli->query("SELECT * FROM "."Report"." WHERE PLAYER = '$name'")->num_rows > 0){
            $Report = mysqli_fetch_row($mysqli->query("SELECT * FROM "."Report"." WHERE PLAYER = '$name'"));

            $sender->sendMessage(TextFormat::GRAY."- Count:"." ".TextFormat::RED.$Report[1]); 
            $sender->sendMessage(TextFormat::GRAY."- Reason:"." ".TextFormat::RED.$Report[2]); 

        }else{
            $sender->sendMessage(TextFormat::GREEN."- Empty.");


        }

        $sender->sendMessage(" ");

        $sender->sendMessage(TextFormat::YELLOW."Warn locker".":"."\n ");

        if($mysqli->query("SELECT * FROM "."Warn"." WHERE PLAYER = '$name'")->num_rows > 0){
            $Warn = mysqli_fetch_row($mysqli->query("SELECT * FROM "."Warn"." WHERE PLAYER = '$name'"));

            $sender->sendMessage(TextFormat::GRAY."- Count:"." ".TextFormat::RED.$Warn[1]); 
            $sender->sendMessage(TextFormat::GRAY."- Reason:"." ".TextFormat::RED.$Warn[2]);

        }else{
            $sender->sendMessage(TextFormat::GREEN."- Empty.");

        }

        $sender->sendMessage(" ");

        $sender->sendMessage(TextFormat::YELLOW."Ban locker".":"."\n ");

        if($mysqli->query("SELECT * FROM "."Ban"." WHERE PLAYER = '$name'")->num_rows > 0){
            $Ban = mysqli_fetch_row($mysqli->query("SELECT * FROM "."Ban"." WHERE PLAYER = '$name'"));

            $sender->sendMessage(TextFormat::GRAY."- Ip:"." ".TextFormat::RED.$Ban[1]); 
            $sender->sendMessage(TextFormat::GRAY."- Reason:"." ".TextFormat::RED.$Ban[2]); 

        }else{
            $sender->sendMessage(TextFormat::GREEN."- Empty.");

        }

        $sender->sendMessage(" ");

    }


}





