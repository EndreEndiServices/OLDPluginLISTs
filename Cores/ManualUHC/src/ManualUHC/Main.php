<?php



/**

 * This is EntirelyQuartz property.

 *

 * Copyright (C) 2016 EntirelyQuartz

 *

 * This is private software, you cannot redistribute it and/or modify any way

 * unless otherwise given permission to do so. If you have not been given explicit

 * permission to view or modify this software you should take the appropriate actions

 * to remove this software from your device immediately.

 *

 * @author EntirelyQuartz

 * @twitter EntirelyQuartz

 *

 */



namespace ManualUHC;



use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\utils\TextFormat;
use pocketmine\level\Position;




class Main extends PluginBase {



    /** @var EventListener */

    private $eventListener;



    public function onEnable() {

        $this->setEventListener();

        $this->registerCommand();

        $this->getLogger()->info("ManualUHC by @EntirelyQuartz was enabled");

    }



    public function onDisable() {

        $this->getLogger()->info("ManualUHC by @EntirelyQuartz was disabled");

    }



    /**

     * @return EventListener

     */

    public function getEventListener() {

        return $this->eventListener;

    }



    public function setEventListener() {

        $this->eventListener = new EventListener($this);

    }



    public function registerCommand() {

        $this->getServer()->getCommandMap()->register("uhc", new UHCCommand($this));

            }
////Mute////
    public function onChat(PlayerChatEvent $event)
    {
        $player = $event->getPlayer();
        if ($player->getGamemode() === 3) {
            $player->sendMessage(TextFormat::GREEN . "§8[§4Deadkills§8]§7 You can't chat while you are in spectator mode!");
            $event->setCancelled(true);
            return;
             }
   public function onPlayerDeath(PlayerDeathEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getName();
      
        if ($player instanceof Player) {
            $cause = $player->getLastDamageCause();
            if ($cause instanceof EntityDamageByEntityEvent) {
                $killer = $cause->getDamager();
                }
                $pos = new V($player->x+0.5, $player->y+1, $player->z+0.5);
                $player->setSpawn($pos);
                $event->setDeathMessage(TextFormat::RED . $player->getName() . " was killed by " . $killer->getName() . ".");
                $player->setGamemode(3);
                $player->sendMessage(TextFormat::RED . "You were eliminated from this UHC! You can spectate the game but you can't chat.");
                    $this->getServer()->removeWhitelist(strtolower($name));
                    $this->getServer()->removeWhitelist($name);

    }



}