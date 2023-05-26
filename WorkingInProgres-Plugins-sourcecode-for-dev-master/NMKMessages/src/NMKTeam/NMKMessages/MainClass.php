<?php
namespace NMKTeam\NMKMessages;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class MainClass extends PluginBase implements Listener
{
	
	public function onEnable()
	{
		$this->getServer()->getLogger()->info(TextFormat::BLUE."[NMKMessages]Plugin Enabled!");
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
	}
	public function onDisable()
	{
		$this->getServer()->getLogger()->info(TextFormat::BLUE."[NMKMessages]Plugin Disabled!");
	}
 	public function onCommand(CommandSender $sender, Command $command, $label, array $args)
 	{
 	   if (count($args) == 1) {
  		$name = $sender->getName();
             	if ($command->getName() == "NMKM") 
                {
                $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
                $cfg = $config->getConfig()->getAll();
                $msg0 = $cfg["1"];
                $msg1 = $cfg["2"];
                $msg2 = $cfg["3"];
                $msg3 = $cfg["4"];
                $msg4 = $cfg["5"];
                $msg5 = $cfg["6"];
                $msg6 = $cfg["7"];
                $msg7 = $cfg["8"];
                $msg8 = $cfg["9"];
                $msg9 = $cfg["10"];
                $enabled = $cfg["enabled"];
                	if ($enabled == true)
                	{
        			 $selector = $args[0];
        			 $this->getServer()->broadcastMessage($msg . $selector);
                        }
                        else
                        {
                        	$this->getServer()->getLogger()->info("Sorry NMKMessages is not enabled. Check your config.");
                        }
                }
 	    }
 	}

}


