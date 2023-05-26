<?php

namespace Clans\utils;
use pocketmine\Player;
class ClanInvite
{
	private $invited;
	private $invitedby;
	private $timeout;
	
	public function __construct(Session $invited, Session $invitedby)
	{
		$this->timeout = time() + 30;
		$this->invited = $invited;
		$this->invitedby = $invitedby;
		$this->invited->registerInvite($this);
	}
	
	public function getInvited() { return $this->invited->getPlayer(); }
	public function getInvitedby() { return $this->invitedby->getPlayer(); }
	public function getTimeout() { return $this->timeout; }
	public function getFaction() { return $this->invitedby->getClan()
	}
