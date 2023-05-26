<?php

namespace SkyZoneMC\PlayerCloud;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\event\Listener;

class PlayerCloud extends PluginBase implements Listener{
    public $cfg;
    public $sf;
    public $mode;
    public $name;
    
    public function onEnable() {
        @mkdir($this->getDataFolder());
        if(!file_exists($this->getDataFolder().'config.yml')){
            $this->initConfig();
        }
        $this->cfg = new Config($this->getDataFolder().'config.yml', Config::YAML);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
    
    public function onQuery(QueryRegenerateEvent $event){
        $servers = $this->cfg->get('servers');
        $count = count($this->getServer()->getOnlinePlayers());
        foreach ($servers as $server){
            $ipport = explode(':', $server);
            
            $playercount = \pocketmine\utils\Utils::getURL('http://minecraft-api.com/api/query/playeronline.php?ip='.$ipport['0'].'&port='.$ipport['1']);
            if(is_numeric($playercount)){
                $count = $count + $playercount;
            }
        }
        $event->setPlayerCount($count);
        $event->setMaxPlayerCount($count + 1);
    }
    
    public function initConfig(){
        $this->cfg = new Config($this->getDataFolder().'config.yml', Config::YAML);
        $this->cfg->set('servers', array(
            "play.lbsg.net:19132",
            "multilabs.net:19132"
        ));
        $this->cfg->save();
    }
}