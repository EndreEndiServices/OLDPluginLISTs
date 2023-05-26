<?php

namespace mcg76\plugin\worldedit;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\level\Explosion;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\utils\Utils;
use pocketmine\block\Block;
use mcg76\plugin\worldedit\commands\RemoveBlocksCommand;

/**
 * MCPE World Edit - Made by minecraftgenius76
 *
 * You're allowed to use for own usage only "as-is".
 * you're not allowed to republish or resell or for any commercial purpose.
 *
 * Thanks for your cooperate!
 *
 * Copyright (C) 2015 minecraftgenius76
 *
 * Web site: http://www.minecraftgenius76.com/
 * YouTube : http://www.youtube.com/user/minecraftgenius76
 *
 * @author minecraftgenius76
 *
 */


class WorldEditPlugIn extends PluginBase implements CommandExecutor {
	public $weCommands;
	public $weBuilder;
	public $peblock;
	public $sessions = [ ];
	public $pos_display_flag = 0;
	/**
	 * Implement plugin onLoad
	 */
	public function onLoad() {
		$this->peblock = new PEBlock ();
		$this->weCommands = new WorldEditCommand ( $this );
		$this->weBuilder = new WorldEditBuilder ( $this );
		$this->sessions = array ();
	}
	
	/**
	 * Implement plugin onEnable function
	 */
	public function onEnable() {
		$this->sessions = [ ];
		$this->replacementBlocks = [ ];
		if (! file_exists ( $this->getDataFolder () . "config.yml" )) {
			@mkdir ( $this->getDataFolder () );
			file_put_contents ( $this->getDataFolder () . "config.yml", $this->getResource ( "config.yml" ) );
		}
		$this->getConfig ()->getAll ();		
		$wanditem = $this->getConfig ()->get ( "wand-item" );
		$limit = $this->getConfig ()->get ( "block-limit" );

		//alternative remove command
		$commandMap = $this->getServer ()->getCommandMap ();		
		$commandMap->register ( "/wremove", new RemoveBlocksCommand($this, "/wremove", "Remove Selected Blocks Command" ) );			
		$this->enabled = true;
		$this->getServer()->getPluginManager()->registerEvents(new WorldEditListener($this), $this);				
		$this->log ( TextFormat::GREEN . "-MCG76 WorldEdit Enabled" );
	}
	
	/**
	 * Implement plugin onDisabled function
	 */
	public function onDisable() {
		$this->enabled = false;
		$this->log ( TextFormat::RED . "- MCG76 WorldEdit Disable" );
	}
	
	/**
	 * Handle plugin commands
	 */
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		$this->weCommands->onCommand ( $sender, $command, $label, $args );
	}

	private function log($msg) {
		$this->getLogger ()->info ( $msg );
	}
}