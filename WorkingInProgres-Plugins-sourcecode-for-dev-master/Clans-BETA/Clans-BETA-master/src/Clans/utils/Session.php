<?php
namespace Clans\utils;
use Clans\Main;
use pocketmine\Player;
use Clans\Clan;
class Session
{
	private $player;
	private $plugin;
	private $faction;
	private $rank;
	private $invite;
	
	public function __construct(Clan $main, Player $player)
	{
		$this->plugin = $main;
		$this->player = $player;
		$this->updateClan();
		$this->updateTag();
		if($this->plugin->devModeEnabled())
		{
			$this->plugin->getServer()->getLogger()->info($this->plugin->formatMessage($player->getName() . " session initialized!", true));
		}
	}
	
	public function registerInvite(ClanInvite $invite)
	{
		$this->invite = $invite;
	}
	
	public function deregisterInvite()
	{
		$this->invite = null;
	}
	
	public function joinClan(Clan $clan)
	{
		$clan->addPlayer($this->getPlayer(), "Member");
		$this->updateClan();
		$this->updateTag();
	}
	
	public function hasInvite()
	{
		return $this->invite != null;
	}
	
	public function getInvite()
	{
		return $this->invite;
	}
	
	public function updateClan()
	{
		foreach($this->plugin->getClans() as $clan)
		{
			if($clan->hasPlayer($this->player))
			{
				$this->clan = $clan->getName();
				$this->updateRank();
				return true;
			}
			$this->clan = null;
		}
	}
	
	public function leaveClan()
	{
		$this->getClan()->removePlayer($this->getPlayer());
		$this->clan = null;
		$this->rank = null;
	}
	
	public function updateTag()
	{
		$this->updateClan();
		if(!$this->inClan() || !$this->plugin->prefs->get("Clans In Overhead Nametag")) {
			$this->getPlayer()->setNameTag($this->getPlayer()->getName());
		} elseif($this->isLeader()) {
			$this->getPlayer()->setNameTag($this->plugin->prefs->get("Leader Identifier") . "[" . $this->getClanName() . "] " . $this->getPlayer()->getName());
		} elseif($this->isOfficer()) {
			$this->getPlayer()->setNameTag($this->plugin->prefs->get("Officer Identifier") . "[" . $this->getClanNameameName() . "] " . $this->getPlayer()->getName());
		} elseif($this->isMember()) {
			$this->getPlayer()->setNameTag("[" . $this->getClanName() . "] " . $this->getPlayer()->getName());
		}
	}
	
	public function getPlayer() { return $this->player; }
	
	public function updateRank()
	{
		if($this->inFaction())
		{
			$this->rank = $this->plugin->getClan($this->clan)->getRank($this->player);
			return true;
		}
		$this->rank = false;
	}
	
	public function getClanName()
	{
		if($this->clan == null) { return null; }
		return $this->clan;
	}
	
	public function getClan()
	{
		return $this->plugin->getClan($this->getClanName());
	}
	
	public function inClan()
	{
		return $this->clan != null;
	}
	
	public function isLeader() { return $this->rank == "Leader"; }
	public function isMember() { return $this->rank == "Member"; }
	public function isOfficer() { return $this->rank == "Officer"; }
	
	
}
