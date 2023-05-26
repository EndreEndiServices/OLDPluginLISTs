<?php

namespace  mcg76\plugin\worldedit\commands;

use mcg76\plugin\worldedit\WorldEditPlugIn;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\utils\TextFormat;
use pocketmine\Player;

/**
 * MCG76 RemoveBlocksCommand 
 *
 * Copyright (C) 2015 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 * @author MCG76
 *
 */
class RemoveBlocksCommand extends Command implements PluginIdentifiableCommand {
	
	public function __construct(WorldEditPlugIn $plugin, $name, $description) {
		$this->plugin = $plugin;		
		parent::__construct ( $name, $description );
		$this->setPermission("mcg76.plugin.worldeditor");
	}
	
	public function execute(CommandSender $sender, $label, array $args) {
		if(!$this->testPermission($sender)) {
			return false;
		}
		$output = "";
		if (!$sender instanceof Player) {
			$output .= "Please run this command in-game.\n";
			$sender->sendMessage ( $output );
			return;
		}
		$session = &$this->getEditor()->session ( $sender );	
		$count = $this->getEditor()->countBlocks ( $session ["selection"], $startX, $startY, $startZ );
		$blocks = $this->getBuilder()->W_remove ( $session ["selection"], $output );
		if (count ( $blocks ) > 0) {
			$offset = array (
					$startX - $sender->x - 0.5,
					$startY - $sender->y,
					$startZ - $sender->z - 0.5 
			);
			$session ["clipboard"] = array (
					$offset,
					$blocks 
			);
		}
		$sender->sendMessage ( $output );		
		$this->getPlugin()->getLogger()->info($output);
		return true;
	}
	
	public function getPlugin() {
		return $this->plugin;
	}	
	public function getEditor() {
		return $this->plugin->weCommands;
	}	
	public function getBuilder() {
		return $this->plugin->weBuilder;
	}
	
}