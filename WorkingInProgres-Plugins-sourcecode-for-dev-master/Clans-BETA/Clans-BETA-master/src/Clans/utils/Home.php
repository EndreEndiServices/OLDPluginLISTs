<?php

namespace Clans\utils;

use Clans\Clan;
use pocketmine\level\Position;

class Home 
{
	private $x;
	private $y;
	private $z;
	private $level;
	private $plugin;
	
	public function __construct($x, $y, $z, $level, $pg)
	{
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
		$this->level = $level;
		$this->plugin = $pg;
	}
	
	public function get()
	{
		return new Position($this->x, $this->y, $this->z, $this->plugin->getServer()->getLevelByName($this->level));
	}
	
	public function export()
	{
		return "" . $this->x . "_" . $this->y . "_" . $this->z . "_" . $this->level;
	}
}
