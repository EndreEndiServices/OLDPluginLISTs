<?php

namespace Saisana299\banitemplus;

use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat as TEXTFORMAT;
use pocketmine\utils\Config;

class BanItemPlus extends PluginBase {

    public function onEnable() {
    	$this->getLogger()->info("§6BanItemPlusを読み込みました by Saisana299");
        $this->banned = new Config($this->getDataFolder() . "banned.yml", Config::YAML);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
        switch (strtolower($command->getName())) {
            case "banitem":
            if(!isset($args[0])){
                $sender->sendMessage("[BanItemPlus] 使い方：/banitem <ban/unban/whiteworld/list>"); 
                return true;
            }
                switch ($args[0]) {
                    case "ban":
                        if(!isset($args[1])){
                            $sender->sendMessage("[BanItemPlus] 使い方：/banitem ban <アイテムID> <アイテムMETA値 (任意)>");
                            return true;
                        }
                        if(!preg_match("/^[0-9]+$/", $args[1])){
                            $sender->sendMessage("[BanItemPlus] アイテムIDは数字で入力してください");
                            return true;
                        }
                        if(isset($args[2])){
                        	if(!preg_match("/^[0-9]+$/", $args[2])){
                            	$sender->sendMessage("[BanItemPlus] アイテムのMETA値は数字で入力してください");
                            	return true;
                        	}
                        	if(!$this->banned->exists($args[1].":".$args[2])){
                            	$sender->sendMessage("[BanItemPlus] アイテム ".$args[1].":".$args[2]." をbanしました");
                            	$this->banned->set($args[1].":".$args[2], ["whiteworlds"=>array()]);
                        	}else{
                            	$sender->sendMessage("[BanItemPlus] 既にbanされています");
                        	}
                        }else{
                        	if(!$this->banned->exists($args[1])){
                            	$sender->sendMessage("[BanItemPlus] アイテム ".$args[1]." をbanしました");
                            	$this->banned->set($args[1], ["whiteworlds"=>array()]);
                        	}else{
                            	$sender->sendMessage("[BanItemPlus] 既にbanされています");
                        	}
                        }
                    break;

                    case "unban":
                        if(!isset($args[1])){
                            $sender->sendMessage("[BanItemPlus] 使い方：/banitem unban <アイテムID> <アイテムMETA値 (任意)>");
                            return true;
                        }
                        if(!preg_match("/^[0-9]+$/", $args[1])){
                            $sender->sendMessage("[BanItemPlus] アイテムIDは数字で入力してください");
                            return true;
                        }
                        if(isset($args[2])){
                        	if(!preg_match("/^[0-9]+$/", $args[2])){
                            	$sender->sendMessage("[BanItemPlus] アイテムのMETA値は数字で入力してください");
                            	return true;
                        	}
                        	if($this->banned->exists($args[1].":".$args[2])){
                            	$sender->sendMessage("[BanItemPlus] アイテム ".$args[1].":".$args[2]." のbanを解除しました");
                            	$this->banned->remove($args[1].":".$args[2]);
                        	}else{
                            	$sender->sendMessage("[BanItemPlus] このアイテムはbanされていません");
                        	}
                    	}else{
                    		if($this->banned->exists($args[1])){
                            	$sender->sendMessage("[BanItemPlus] アイテム ".$args[1]." のbanを解除しました");
                            	$this->banned->remove($args[1]);
                        	}else{
                            	$sender->sendMessage("[BanItemPlus] このアイテムはbanされていません");
                        	}
                    	}
                    break;

                    case "whiteworld":
                        if(!isset($args[1])){
                            $sender->sendMessage("[BanItemPlus] 使い方：/banitem whiteworld <WORLD名> <アイテムID> <アイテムMETA値 (任意)>");
                            return true;
                        }
                        if(!isset($args[2])){
                            $sender->sendMessage("[BanItemPlus] アイテムIDを入力してください");
                            return true;
                        }
                        if(!preg_match("/^[0-9]+$/", $args[2])){
                            $sender->sendMessage("[BanItemPlus] アイテムIDは数字で入力してください");
                            return true;
                        }
                        if(isset($args[3])){
                        	if(!preg_match("/^[0-9]+$/", $args[3])){
                            	$sender->sendMessage("[BanItemPlus] アイテムのMETA値は数字で入力してください");
                            	return true;
                        	}
                        	if($this->banned->exists($args[2].":".$args[3])){
                            	$worlds = $this->banned->getAll()[$args[2].":".$args[3]]["whiteworlds"];
                            	if(!in_array($args[1], $worlds)){
                                	array_push($worlds, $args[1]);
                                	$this->banned->set($args[2].":".$args[3], ["whiteworlds" => $worlds]);
                                	$sender->sendMessage("[BanItemPlus] アイテム ".$args[2].":".$args[3]." のwhiteworldを追加しました");
                            	}else{
                                	$world = array_diff($worlds, [$args[1]]);
                                	$world = array_values($world);
                                	$this->banned->set($args[2].":".$args[3], ["whiteworlds" => $world]);
                                	$sender->sendMessage("[BanItemPlus] アイテム ".$args[2].":".$args[3]." のwhiteworldを消去しました");
                            	}
                        	}else{
                            	$sender->sendMessage("[BanItemPlus] アイテムはbanされていません");
                        	}
                    	}else{
                        	if($this->banned->exists($args[2])){
                            	$worlds = $this->banned->getAll()[$args[2]]["whiteworlds"];
                            	if(!in_array($args[1], $worlds)){
                                	array_push($worlds, $args[1]);
                                	$this->banned->set($args[2], ["whiteworlds" => $worlds]);
                                	$sender->sendMessage("[BanItemPlus] アイテム ".$args[2]." のwhiteworldを追加しました");
                            	}else{
                                	$world = array_diff($worlds, [$args[1]]);
                                	$world = array_values($world);
                                	$this->banned->set($args[2], ["whiteworlds" => $world]);
                                	$sender->sendMessage("[BanItemPlus] アイテム ".$args[2]." のwhiteworldを消去しました");
                            	}
                        	}else{
                            	$sender->sendMessage("[BanItemPlus] アイテムはbanされていません");
                        	}
                    	}
                    break;

                    case "list":
                        $alldata = $this->banned->getAll();
                        $sender->sendMessage("[BanItemPlus] BanItemリスト (アイテムID <使用可能ワールド>)");
                        foreach ($alldata as $key => $value) {
                            $worlds = "";
                            foreach ($value as $key2 => $value2) {
                                foreach ($value2 as $world) {
                                    $worlds .= " ".$world;
                                } 
                            }
                            if($worlds === ""){
                                $sender->sendMessage("{$key}");
                            }else{
                                $sender->sendMessage("{$key} <{$worlds} >");
                            }
                        }
                    break;
                    
                    default:
                       $sender->sendMessage("[BanItemPlus] 使い方：/banitem <ban/unban/whiteworld/list>"); 
                    break;
                }
            break;
        }
        return true;
    }

    public function onDisable(){
        $this->banned->save();
    }

}
