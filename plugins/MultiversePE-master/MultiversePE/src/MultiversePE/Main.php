<?php
namespace MultiversePE;

use MultiversePE\commands\MultiversePE;
use MultiversePE\commands\TPWorld;

use pocketmine\plugin\PluginBase;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\command\Command;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\command\ConsoleCommandExecutor;
use pocketmine\Player;

class Main extends PluginBase implements CommandExecutor{
    public function onEnable(){
        $this->saveDefaultConfig();
        $this->getResource("config.yml");
		$this->registerCommands();
        $this->getLogger()->info("MultiversePE Loaded!");
    }
    public function registerCommands(){
        $this->getServer()->getCommandMap()->registerAll("multiversepe", [
			new MultiversePE($this),
			new TPWorld($this),
		]);
    }

    public function onDisable(){
        $this->getLogger()->info("MultiversePE Unloaded!");
    }
}
?>
