<?php
namespace FunnyRain;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\CallbackTask;

use pocketmine\event\Listener;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\block\BlockBreakEvent;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as F;
use pocketmine\math\Vector3;
use pocketmine\item\Item;
use pocketmine\entity\Entity;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\level\Sound;
use pocketmine\block\Block;
use pocketmine\entity\Effect;

use pocketmine\level\particle\SmokeParticle;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Smoke extends PluginBase implements Listener{

	public $stime;
	
	public function onEnable()
	{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
}
	
    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
        switch($cmd->getName()){

	case "smoke":
	        if(isset($this->stime[$sender->getName()])){
		    if($this->stime[$sender->getName()] == 1){
			$sender->sendMessage("§8(§cSmoke§8) §fВы уже использовали эту команду!");
			}
			elseif($this->stime[$sender->getName()] == 2){
			$sender->sendMessage("§8(§cSmoke§8) §fВы курнули.. (текст можно изменить!)");
$sender->addEffect(Effect::getEffect(1)->setAmplifier(2)->setDuration(100)->setVisible(true));
$sender->addEffect(Effect::getEffect(5)->setAmplifier(2)->setDuration(100)->setVisible(true));
			/*    smoke. 
*/
         $player = $sender->getPlayer();
        $level=$player->getLevel();
        $x = $player->getX();
        $y = $player->getY();
        $z = $player->getZ();
        $r = 2000;
        $g = 2000;
        $b = 2000;
        $center = new Vector3($x, $y, $z);
        $radius = 200000;
        $count = 100000;
//      if($this->getParticle($this->getPlayerParticle($event->getPlayer()), new Vector3($event->getPlayer()->x, $event->getPlayer()->y, $event->getPlayer()->z)) !== null){
        $particle = new SmokeParticle($center, $r, $g, $b, 1);
          for($yaw = 1000, $y = $center->y; $y < $center->y + 1; $yaw += (M_PI * 2) / 50, $y += 1 / 50){
              $x = cos($yaw) + $center->x;
              $z = cos($yaw) + $center->z;
              $particle->setComponents($x, $y, $z);
              $level->addParticle($particle);


			$this->stime[$sender->getName()] = 1;
			break;
		           }
	             }
               }
             }
          }
             
           

public function onJoin(PlayerJoinEvent $e){
		$this->stime[$e->getPlayer()->getName()] = 2;
	}
	}