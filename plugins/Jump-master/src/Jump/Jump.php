<?php

namespace Jump;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;

use pocketmine\level\sound\BlazeShootSound;
use pocketmine\level\particle\MobSpawnParticle;


class Jump extends PluginBase implements Listener{

    public $rank = [];

	public function onEnable(){
    	$this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->exec();

    }

    public function exec(){
        $this->getLogger()->info(TextFormat::YELLOW."(Only-Serv) Working...");

    }

    public function onJoin(PlayerJoinEvent $event){
        $rank = 0;

        if($this->getServer()->getPluginManager()->getPlugin("Rank")){
            $rank = $this->getServer()->getPluginManager()->getPlugin("Rank")->getRank($event->getPlayer());

        }else{
            $event->getPlayer()->sendMessage("Critical error #Jump_Rank report on twitter.");

        }

        if($rank != 0){
            $this->rank[$event->getPlayer()->getName()] = $rank;

        }

    }

    public function onQuit(PlayerQuitEvent $event){
        $player = $event->getPlayer();

        if(isset($this->rank[$player->getName()])){
            unset($this->rank[$player->getName()]);

        }

    }

    public function onMove(PlayerMoveEvent $event){
        $player = $event->getPlayer();

        if($player->isImmobile()){
            return;

        }

        if(!$player->isOnGround()){
            return;

        }

        if($player->getGamemode() == Player::CREATIVE){
            return;

        }

        if(intval($event->getTo()->getY()) < intval($event->getFrom()->getY())){
            return;

        }

        if(isset($this->rank[$player->getName()])){
            $player->setAllowFlight(true);

        }else{
            $player->setAllowFlight(false);

        }

    }

    public function onToggle(PlayerToggleFlightEvent $event){
        $player = $event->getPlayer();

        if($player->isImmobile()){
            return;

        }

        if($player->getGamemode() == Player::CREATIVE){
            return;

        }

        if(isset($this->rank[$player->getName()]) && $this->rank[$player->getName()] >= 3){
            return;

        }

        $event->setCancelled(true);

        foreach($this->getServer()->getLevels() as $level){
            $level->addParticle(new MobSpawnParticle(new Vector3($player->getX() + 0.5, $player->getY(), $player->getZ() + 0.5), 1, 1));
            $level->addSound(new BlazeShootSound(new Vector3($player->getX() + 0.5, $player->getY(), $player->getZ() + 0.5)));

        }

        $player->setAllowFlight(false);
            $player->setFlying(false);

        $player->setMotion(new Vector3(0, 0.9, 0));

    }

    public function Fall(EntityDamageEvent $event){
        $player = $event->getEntity();

        if($player instanceof Player && $event->getCause() === EntityDamageEvent::CAUSE_FALL){
            $event->setCancelled(true); 

        }

    }


}
