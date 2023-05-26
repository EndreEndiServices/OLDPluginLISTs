<?php
namespace MultiversePE;

use pocketmine\command\Command;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\event\Listener;

abstract class BaseCommand extends Command implements Listener, PluginIdentifiableCommand {
	private $api;
	
	public function __construct(Main $plugin, $name, $description = null, $usage = null, array $aliases = []){
		parent::__construct($name, $description, $usage, $aliases);
		$this->api = $plugin;
	}
	
	public function getAPI(){
		return $this->api;
	}
	
	public function getPlugin(){
		return $this->api;
	}
}
?>
