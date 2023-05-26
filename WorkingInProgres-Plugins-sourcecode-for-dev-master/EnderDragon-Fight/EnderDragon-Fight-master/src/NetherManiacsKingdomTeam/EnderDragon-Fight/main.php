<?php

namespace NetherManiacsKingdom\EnderDragon-Fight;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;

class main extends PluginBase {
  
  public function onEnable(){
    $this->getLogger()->info(TextFormat::GREEN."EnderDragon-Fight has been Enabled");
  }
  public function onDisable(){
    $this->getLogger()->info(TextFormat::RED. "EnderDragon-Fight has been Disabled");
  }
  }
