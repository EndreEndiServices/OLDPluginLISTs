<?php
namespace MCFTWARS\team;

use MCFTWARS;
abstract class Team {
	/**
	 * 
	 * @var \MCFTWARS\MCFTWARS
	 */
	public $plugin;
	
	public function __construct(\MCFTWARS\MCFTWARS $plugin) {
		$this->plugin = $plugin;
	}
	abstract function getTeamName();
}