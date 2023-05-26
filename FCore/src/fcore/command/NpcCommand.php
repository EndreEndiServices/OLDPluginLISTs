<?php

declare(strict_types = 1);

namespace fcore\command;

use fcore\FCore;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class NpcCommand extends Command {

	/** @var FCore $plugin */
	public $plugin;

	/** @var array $rm */
	public $rm = [];

	/**
	 * NpcCommand constructor.
	 * @param FCore $plugin
	 */
	public function __construct(FCore $plugin){
		$this->plugin = $plugin;
		parent::__construct("npc", "Npc commands", null, []);
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 * @return mixed|void
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		// REMOVED DUE TO BULLSHITERY API
	}
}