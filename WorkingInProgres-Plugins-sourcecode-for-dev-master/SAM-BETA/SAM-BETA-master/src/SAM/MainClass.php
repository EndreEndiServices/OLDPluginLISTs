<?php
namespace SAM;

use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\Listener;

class MainClass extends PluginBase implements Listener{
    public function onEnable()
    {
        $this->getLogger()->info("[SAM] SAM v0.0.1 enabled");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onDisable()
    {
        $this->getLogger()->info("[SAM] SAM v0.0.1 disabled");
    }

    function onJoin(PlayerJoinEvent $event)
    {
        $name = $event->getPlayer()->getDisplayName();    
        if ($registered = FALSE){
        $name->sendMessage("[{$this->getConfig()->get("prefix")}] Hello " . $name . " this server use SAM by NetherManiacsKingdom. You must register at " .          $url . " to play");  
    }
    if ($registered = TRUE){
        //$registered is whether or not the player is registered. Set it to retrieve the players info from the $url
        $name->sendMessage("[{$this->getConfig()->get("prefix")}] Hello" . $name . " To play you must login with /login (your password)");  
       //$url is the url of the users website
    }
  }
}
