<?php
namespace CRCore\core\utils;
use CRCore\core\Loader;
class ServerUtils{
        /**
         * 
         * @param string $reason
         * 
         */
        public static function killAllPlayers($reason = ""){
                foreach(PrestigeSocietyCore::getInstance()->getServer()->getOnlinePlayers() as $p){
                        $p->kick($reason);
                }
        }

        /**
         * 
         * @param string $reason
         * 
         */
        public static function ballAllPlayers($reason = ""){
                foreach(PrestigeSocietyCore::getInstance()->getServer()->getOnlinePlayers() as $p){
                        PrestigeSocietyCore::getInstance()->getServer()->getNameBans()->addBan($p->getName(), $reason);
                }
        }

        /**
         * 
         * @param string $reason
         * 
         */
        public static function kickAndShutDown($reason = ""){
                self::killAllPlayers($reason);
                PrestigeSocietyCore::getInstance()->getServer()->shutdown();
        }

        /**
         * 
         * @param $msg
         * 
         */
        public static function bcMessage($msg){
                foreach(PrestigeSocietyCore::getInstance()->getServer()->getOnlinePlayers() as $p){
                        $p->sendMessage($msg);
                }
        }

        /**
         * 
         * @return \pocketmine\level\Level[]
         * 
         */
        public static function getLevels(){
                return PrestigeSocietyCore::getInstance()->getServer()->getLevels();
        }

        /**
         * 
         * @param $msg
         * 
         */
        public static function bcToOps($msg){
                foreach(self::getOnOps() as $p){
                        $p->sendMessage($msg);
                }
        }

        /**
         * 
         * @return \pocketmine\Player[]
         * 
         */
        public static function getOnOps(){
                $ops = [];
                foreach(self::getOnPlayers() as $p){
                        if($p->hasPermission("core.helpop")){
                                $ops[] = $p;
                        }
                }
                return $ops;
        }

        /**
         * 
         * @return \pocketmine\Player[]
         * 
         */
        public static function getOnPlayers(){
                return Loader::getInstance()->getServer()->getOnlinePlayers();
        }
}