<?php 



namespace HB;







use pocketmine\Server;



use pocketmine\Player;



use pocketmine\scheduler\PluginTask;



use pocketmine\Plugin;







class Checker extends PluginTask {

	public $t;
	public $color;
	public $index;
	public function __construct($plugin){



		$this->plugin = $plugin;
		$this->t = 1;
		$this->color = array("§5","§d");

		parent::__construct($plugin); 



	}







	public function onRun($tick){



	 foreach($this->getOwner()->getServer()->getOnlinePlayers() as $p) {



$player = $p;

if($p->getLevel()->getName() == "world"){
	$color = array("§5","§d");

                     $group = $this->getOwner()->getServer()->getPluginManager()->getPlugin("PurePerms")->getUserDataMgr()->getGroup($p);
                    // $group = [PurePerms $plugin]->getGroup($groupName);

        $groupname = $group->getName();
  if($groupname == "member"){
    $p->setNameTag("§8-+ §eMEMBER§8 +-§8\n[§cMember§8]§5".$p->getDisplayName());
  }
  if($groupname == "gamer"){
    $p->setNameTag("§8[§cGamer§8]§5".$p->getDisplayName());
  }
    if($groupname == "guest"){
      $p->setNameTag("§8-+ §aNEW-USER§8 +-\n§8[§cNew-User§8]§5".$p->getDisplayName());
  }
    if($groupname == "user"){
      $p->setNameTag("§8[§cUser§8]§5".$p->getDisplayName());
  }
    if($groupname == "leadadmin"){
      $p->setNameTag("§eGOD ADMIN\n§8[§cLead-Admin§8]§5".$p->getDisplayName());
  }
    if($groupname == "helper"){
      $p->setNameTag("§8[§cHelper§8]§5".$p->getDisplayName());
  }
    if($groupname == "owner"){
      $p->setNameTag("§8[§cOwner§8]§5".$p->getDisplayName());
  }
    if($groupname == "mod"){
      $p->setNameTag("§8[§cModerator§8]§5".$p->getDisplayName());
  }
        if($groupname == "youtube"){
          $p->setNameTag("§8[§fYou§cTube§8]§5".$p->getDisplayName());
  }
      if($groupname == "yt1"){
        $p->setNameTag("§8-+ §fYou§4Tube§8 +-\n§8[§fYou§cTube§a+§8]§5".$p->getDisplayName());
  }
        if($groupname == "admin"){
        $p->setNameTag("§8[§cAdministrator§8]§5".$p->getDisplayName());
  }
          if($groupname == "vip"){
        $p->setNameTag("§8[§eVIP§8]§6".$p->getDisplayName());
  }
            if($groupname == "vip+"){
        $p->setNameTag("§8[§eVIP+§8]§6".$p->getDisplayName());
  }
            if($groupname == "membervip"){
        $p->setNameTag("§8-+ §eMEMBER VIP §8+-\n§8[§eVIP§a+§eMEMBER§8]§6".$p->getDisplayName());
  }
                  

//$p->setNametag("§5".$p->getDisplayName());
switch($this->index){
case 1:
$p->sendTip("§aWelcome to this server!\n   §5-= §7[ §d".count($this->getOwner()->getServer()->getOnlinePlayers())."§7/§d".$this->getOwner()->getServer()->getMaxPlayers()." §7]§5 =-");
break;
case 2:
$p->sendTip("§aWelcome to this server!\n   §5-= §7[ §d".count($this->getOwner()->getServer()->getOnlinePlayers())."§7/§d".$this->getOwner()->getServer()->getMaxPlayers()." §7]§5 =-");
break;
}
	
}else{

$color = array("§5","§5", "§5");

                    $rand = array_rand($color);

	                   $group = $this->getOwner()->getServer()->getPluginManager()->getPlugin("PurePerms")->getUserDataMgr()->getGroup($p);
  $groupname = $group->getName();
   if($groupname == "member"){
    $p->setNameTag("§8-+ §eMEMBER§8 +-§8\n§8[§cMember§8]§5".$p->getDisplayName()."\n§aHealth: §r§f§c".(round($player->getHealth()))."§7/§c".$player->getMaxHealth());
  }
  if($groupname == "gamer"){
    $p->setNameTag("§8[§cGamer§8]§5".$p->getDisplayName()."\n§aHealth: §r§f§c".(round($player->getHealth()))."§7/§c".$player->getMaxHealth());
  }
    if($groupname == "guest"){
      $p->setNameTag("§8-+ §aNEW-USER§8 +-\n§8[§cNew-User§8]§5".$p->getDisplayName()."\n§aHealth: §r§f§c".(round($player->getHealth()))."§7/§c".$player->getMaxHealth());
  }
    if($groupname == "user"){
      $p->setNameTag("§8[§cUser§8]§5".$p->getDisplayName()."\n§aHealth: §r§f§c".(round($player->getHealth()))."§7/§c".$player->getMaxHealth());
  }
    if($groupname == "leadadmin"){
      $p->setNameTag("§eGOD ADMIN\n§8[§cLead-Admin§8]§5".$p->getDisplayName()."\n§aHealth: §r§f§c".(round($player->getHealth()))."§7/§c".$player->getMaxHealth());
  }
    if($groupname == "helper"){
      $p->setNameTag("§8[§cHelper§8]§5".$p->getDisplayName()."\n§aHealth: §r§f§c".(round($player->getHealth()))."§7/§c".$player->getMaxHealth());
  }
    if($groupname == "owner"){
      $p->setNameTag("§8[§cOwner§8]§5".$p->getDisplayName()."\n§aHealth: §r§f§c".(round($player->getHealth()))."§7/§c".$player->getMaxHealth());
  }
    if($groupname == "mod"){
      $p->setNameTag("§8[§cModerator§8]§5".$p->getDisplayName()."\n§aHealth: §r§f§c".(round($player->getHealth()))."§7/§c".$player->getMaxHealth());
  }
        if($groupname == "youtube"){
          $p->setNameTag("§8[§fYou§cTube§8]§5".$p->getDisplayName()."\n§aHealth: §r§f§c".(round($player->getHealth()))."§7/§c".$player->getMaxHealth());
  }
      if($groupname == "yt1"){
        $p->setNameTag("§8-+ §fYou§4Tube§8 +-\n§8[§fYou§cTube§a+§8]§5".$p->getDisplayName()."\n§aHealth: §r§f§c".(round($player->getHealth()))."§7/§c".$player->getMaxHealth());
  }
          if($groupname == "admin"){
        $p->setNameTag("§8[§cAdministrator§8]§5".$p->getDisplayName()."\n§aHealth: §r§f§c".(round($player->getHealth()))."§7/§c".$player->getMaxHealth());
  }
            if($groupname == "vip"){
        $p->setNameTag("§8[§eVIP§8]§6".$p->getDisplayName()."\n§aHealth: §r§f§c".(round($player->getHealth()))."§7/§c".$player->getMaxHealth());
  }
              if($groupname == "vip+"){
        $p->setNameTag("§8[§eVIP+§8]§6".$p->getDisplayName()."\n§aHealth: §r§f§c".(round($player->getHealth()))."§7/§c".$player->getMaxHealth());
  }
            if($groupname == "membervip"){
        $p->setNameTag("§8-+ §eMEMBER VIP §8+-\n§8[§eVIP§a+§eMEMBER§8]§6".$p->getDisplayName()."\n§aHealth: §r§f§c".(round($player->getHealth()))."§7/§c".$player->getMaxHealth());
  }



	//$p->setNameTag("{$color[$rand]}".$p->getDisplayName()."\n§aHealth: §r§f§c".(round($player->getHealth()))."§7/§c".$player->getMaxHealth());



//}

}
$this->t++;
$this->index++;
if($this->t >= count($this->color)){
	$this->t = 1;
}
if($this->index > 2){
	$this->index = 1;
}
}





	}



}



