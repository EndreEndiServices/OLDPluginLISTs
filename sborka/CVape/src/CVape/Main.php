<?php
namespace CVape;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\entity\Effect;
use pocketmine\level\Sound;
use pocketmine\Server;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\level\Position;
use pocketmine\plugin\PluginBase;
use pocketmine\level\particle\MobSpawnParticle;
use pocketmine\math\Vector3;
use pocketmine\block\Block;

class Main extends PluginBase implements Listener {

  public function onEnable() {
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
}

      public function SoundPop(Player $sender){
		$sounds = "pocketmine\\level\\sound\\TNTPrimeSound";
		$sender->getLevel()->addSound(new $sounds($sender));
	}
      
        public function  onCommand(CommandSender $sender, Command $cmd, $label, array $args){
      	switch($cmd->getName()){
            case "vape":
             if(!isset($this->vape[$sender->getName()])){
                $this->vape[$sender->getName()] = 1;
                $sender->addEffect(Effect::getEffect(Effect::REGENERATION)->setAmplifier(2)->setDuration(20 * 9));
                $sender->addEffect(Effect::getEffect(Effect::SPEED)->setAmplifier(3)->setDuration(20 * 15));
                $sender->addEffect(Effect::getEffect(Effect::JUMP)->setAmplifier(2)->setDuration(20 * 17));
                $this->SoundPop($sender);
          for($x=0;$x<=15;$x++) {
                $pos = new Vector3($sender->getX()+rand(0,0.55), $sender->getY() + 1, $sender->getZ()+rand(0,0.55));
                $random = rand(0,3);
                $paricle = new MobSpawnParticle($pos, $random); 
                $sender->getLevel()->addParticle($paricle, array($sender)); 
                     }
                $sender->sendMessage("§8(§aВейп§8)§f Вы §bуспешно §fвыкурили вейп.");
                $sender->sendMessage("§8(§aВейп§8)§f Дымлю§e где хочу!");
            }else{
                $sender->sendMessage("§8(§aВейп§8)§f Много курить §cнельзя, §fиначе вы умрете!");
                $sender->sendMessage("§8(§aВейп§8)§f После§e перезагрузки §fвы сможете подыметь еще раз.");
         }
      }
   }
}