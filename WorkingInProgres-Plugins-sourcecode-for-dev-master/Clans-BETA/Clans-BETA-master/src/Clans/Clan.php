<?php

namespace Clans;

use pocketmine\Player;
use pocketmine\level\Position;
use pocketmine\level\pocketmine\level;
use Clans\utils\Home;

class Clan
{
	private $name;
	private $members = array();
	private $plugin;
	private $description;
	private $home;
	
	public function __construct($plugin, $name, $leader)
	{
		$this->addPlayer($leader, "Leader");
		$this->name = $name;
		$this->description = "Description not set";
		$this->plugin = $plugin;
		$this->plugin->addClan($this);
		$this->home = null;
	}
	
	public static function import($string, $plugin)
	{
		$name = strstr($string, "$", true);
		$members = explode(",", substr(strstr(strstr($string, "$"), "#", true), 1));
		$desc = str_replace("|", " ", strstr(strstr($string, "#"), "%", true));
		$leaderID = 0;
		foreach($members as $num => $text)
		{
			if(strpos($text, ":Leader"))
			{
				$leaderID = $num;
			}
		}
		$leader = strstr($members[$leaderID], ":", true);
		$clan = new self($plugin, $name, $leader);
		if(strcmp(substr(strstr($string, "%"), 1), "null") != 0)
		{
			$home_raw = str_replace("%", "", strstr($string, "%"));
			$home_array = explode("_", $home_raw);
			$x = $home_array[0];
			$y = $home_array[1];
			$z = $home_array[2];
			$levelName = $home_array[3];
			$level = $plugin->getServer()->getLevelByName($levelName);
			$clan->sethome(new Position($x, $y, $z, $level));
		}
			unset($members[$leaderID]);
			foreach($members as $num => $text)
		{
			$player = strstr($text, ":", true);
			$rank = substr(strstr($text, ":"), 1);
			$clan->addPlayer($player, $rank);
		}
		$clan->setDescription($desc);
		if($plugin->prefs->get("Developer Mode"))
		{
			$plugin->getServer()->getLogger()->info($plugin->formatMessage("[X] $name", true));
		}
	}
	
	public function addPlayer($player, $rank)
	{
		if($rank != "Leader" && $rank != "Officer" && $rank != "Member")
		{
			return false;
		}
		if($player instanceof Player)
		{
			$this->members[strtolower($player->getName())] = $rank;
			return true;
		} else {
			$this->members[strtolower($player)] = $rank;
			return true;
		}
	}
	
	public function removePlayer(Player $player)
	{
		if(isset($this->members[$player->getName()]))
		{
			unset($this->members[$player->getName()]);
			return true;
		}
		return false;
	}
	
	public function removePlayer_string($playerName)
	{
		if(isset($this->members[$playerName]))
		{
			unset($this->members[$playerName]);
			return true;
		}
		return false;
	}
	
	public function sethome(Position $position)
	{
		$this->home = new Home($position->x, $position->y, $position->z, $position->level->getName(), $this->plugin);
	}
	
	public function unsethome()
	{
		$this->home = null;
	}
	
	public function hasHome()
	{
		return $this->home != null;
	}
	
	public function getHome()
	{
		return $this->home->get();
	}
	
	public function delete()
	{
		foreach($this->members as $name => $rank)
		{
			if($this->plugin->getServer()->getPlayer($name) instanceof Player)
			{
				$this->plugin->getSession($this->plugin->getServer()->getPlayer($name))->leaveClan();
				$this->plugin->getServer()->getPlayer($name)->sendMessage($this->plugin->formatMessage("Your clan has been disbanded"));
			}
		}
		$this->plugin->removeClan($this);
	}
	
	public function export()
	{
		if($this->hasHome()) { return "" . $this->name . "$" . $this->exportMembers() . "#" . str_replace(" ", "|", $this->getDescription()) . "%" . $this->home->export();
		} else {
			return "" . $this->name . "$" . $this->exportMembers() . "#" . str_replace(" ", "|", $this->getDescription()) . "%null";
		}
	}
	
	public function setDescription($desc)
	{
		$this->description = $desc;
	}
	
	public function getDescription()
	{
		return $this->description;
	}
	
	public function setRank(Player $player, $rank)
	{
		$this->members[$player->getName()] = $rank;
	}
	
	public function setRank_string($player, $rank)
	{
		$this->members[$player] = $rank;
	}
	
	public function exportMembers()
	{
		$export = "";
		foreach($this->members as $member => $rank)
		{
			$export = $export . "" . strtolower($member) .":$rank,";
		}
		return substr($export, 0, -1);
	}
	
	public function hasPlayer(Player $player)
	{
		foreach($this->members as $name => $rank)
		{
			if(strtolower($player->getName()) == strtolower($name)) { return true; }
		}
		return false;
	}
	
	public function hasPlayer_string($playerName)
	{
		foreach($this->members as $name => $rank)
		{
			$this->plugin->getServer()->getLogger()->info($playerName . " " . $name);
			if(strcmp(strtolower($playerName), strtolower($name)) == 0) { return true; }
		}
		$this->plugin->getServer()->getLogger()->info("IT'S FALSE");
		return false;
	}
	
	public function getRank(Player $player)
	{
		return $this->members[strtolower($player->getName())];
	}
	
	public function getRank_string($player)
	{
		if($this->hasPlayer_string($player))
		{
			return $this->members[$player];
		}
	}
	
	public function getLeader() // returns name as string
	{
		foreach($this->members as $member => $rank)
		{
			if($rank == "Leader")
			{
				return $member;
			}
		}
	}
	
	public function isFull()
	{
		return $this->getNumberMembers() >= $this->plugin->prefs->get("Maximum Players Per Clan");
	}
	
	public function getNumberMembers()
	{
		return count($this->members);
	}
	
	public function getName()
	{
		return $this->name;
	}
}
