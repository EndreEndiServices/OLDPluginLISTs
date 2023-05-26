<?php

namespace MXSB\GreenWix;

use MXSB\GreenWix\commands\SBcommand;
use MXSB\GreenWix\generator\SkyBlockGenerator;
use MXSB\GreenWix\island\{Island, IslandManager};
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\level\{Level, Position};
use pocketmine\lang\BaseLang;
use pocketmine\level\generator\GeneratorManager;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\{Config, TextFormat};

class Main extends PluginBase
{


    // Функция, вызываемая при готовности плагина
    public function onEnable()
    {
        //$this->lang = new BaseLang("rus", $this->getDataFolder()."languages/");
        $this->getServer()->getCommandMap()->register("sb", new SBcommand($this));
        $this->islandM = new IslandManager($this);
        $this->listener = new EventListener($this);
        $this->data = new Config($this->getDataFolder() . "XY.yml", Config::YAML);
        $this->gen = GeneratorManager::addGenerator(SkyBlockGenerator::class, "sb");
        $this->getServer()->loadLevel("sev");
        $this->db = new \SQLite3("/root/Aw/plugins/island.db");
        $this->db->exec("CREATE TABLE IF NOT EXISTS islands(owner TEXT PRIMARY KEY, islandX INT NOT NULL, islandZ INT NOT NULL, point varchar(255), spawn varchar(255), members varchar(255), locked varchar(5));");
        $this->db->exec("CREATE TABLE IF NOT EXISTS users(nickname TEXT PRIMARY KEY, island TEXT); ");
        
    }






    // Функция, вызываемая при отправке команды OLD
    /*public function onCommand( CommandSender $player, Command $cmd, $label, array $args ) : bool{
        switch($cmd->getName()){
            case "sb":
            Server::getInstance()->generateLevel($args[0],
                (int) round(rand(0, (int) (round(time() / memory_get_usage(true)) * (int) str_shuffle("127469453645108") / (int) str_shuffle("12746945364"))))
                , SkyBlockGenerator::class , []);
            Server::getInstance()->loadLevel($args[0]);
            $player->teleport(new Position(264, 256, 264, Server::getInstance()->getLevelByName($args[0])));
            break;
        }
    }*/
    public function test($name, $player)
    {
        Server::getInstance()->generateLevel($name,
            (int)round(rand(0, (int)(round(time() / memory_get_usage(true)) * (int)str_shuffle("127469453645108") / (int)str_shuffle("12746945364"))))
            , SkyBlockGenerator::class, []);
        Server::getInstance()->loadLevel($name);
        $player->teleport(new Position(8, 130, 8, Server::getInstance()->getLevelByName($name)));
    }
}
