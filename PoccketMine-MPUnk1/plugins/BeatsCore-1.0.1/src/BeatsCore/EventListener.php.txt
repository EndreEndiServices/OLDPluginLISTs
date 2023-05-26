<?php

declare(strict_types=1);

namespace BeatsCore;

use BeatsCore\commands\HUDCommand;
use BeatsCore\Core;
use BeatsCore\Stacker\StackFactory;
use BeatsCore\tasks\{
    ChatCooldownTask, TitleTask
};
use BeatsCore\tasks\HUDTask;
use pocketmine\entity\Living;
use pocketmine\event\Listener;
use pocketmine\event\entity\{
    EntityDamageByEntityEvent, EntityDamageEvent, EntityMotionEvent, EntitySpawnEvent
};
use pocketmine\event\player\{
    PlayerChatEvent, PlayerInteractEvent, PlayerJoinEvent, PlayerQuitEvent, PlayerRespawnEvent
};
use pocketmine\item\Item;
use pocketmine\Player;

class EventListener implements Listener{

    /** @var Core */
    private $plugin;

    public function __construct(Core $plugin){
        $this->plugin = $plugin;
    }

    public function onChat(PlayerChatEvent $event) : void{
        $player = $event->getPlayer();
        if(isset($this->plugin->chat[$player->getName()])){
            $event->setCancelled(true);
            $player->sendMessage("§l§dBeats§bChat §8»§r §cPlease wait before chatting again!");
        }

        if(!$player->hasPermission("beats.bypass.chat")){
            $this->plugin->chat[$player->getName()] = true;
            $this->plugin->getServer()->getScheduler()->scheduleDelayedTask(new ChatCooldownTask($this->plugin, $player), 100);
        }
    }

    public function onInteract(PlayerInteractEvent $event) : void{
        $player = $event->getPlayer();
        $item = $event->getItem();
        switch($item->getId()){
            case 340:
                if($item->getDamage() == 1){
                    $form = $this->plugin->getServer()->getPluginManager()->getPlugin("FormAPI")->createCustomForm(function (Player $player, array $data){
                        $result = $data[0];
                    });

                    $form->setTitle("§l§dBeats§bPE §aChangelog§r");
                    $form->addLabel(file_get_contents($this->plugin->getDataFolder() . "changelog.txt"));
                    $form->sendToPlayer($player);
                    $player->getInventory()->removeItem($item);
                }
                break;
        }
    }

    public function onJoin(PlayerJoinEvent $event) : void{
        $player = $event->getPlayer();
        $event->setJoinMessage("§8[§a+§8] §b{$player->getName()} §ejoined the server!");
        $player->sendMessage("§2=====================================\n -        §l§dBeats§bPE §cOP §3Factions!§r\n§2 -         §eBeatsPE.ddns.net 19132\n§2 - \n§2 - §l§aSTORE:§r BeatsNetworkPE.buycraft.net\n§2 - §l§6VOTE:§r is.gd/VoteBeatsPE\n§2 - §l§cFOURMS:§r COMING SOON!\n§2 - \n§2 - §aWelcome, §6{$player->getName()} §ato §l§dBeats§bPE§r§a!§r\n§2 - \n§2 - §7You're playing on OP Factions!\n§2=====================================");
        $this->plugin->getServer()->getScheduler()->scheduleDelayedTask(new TitleTask($this->plugin, $player), 20);
        $book = Item::get(340, 1, 1);
        $book->setCustomName("§l§aChangelog\n§r§7See what's new!");
        $player->getInventory()->addItem($book);
        array_push($this->plugin->hud, $player->getName());
        $this->plugin->getServer()->getScheduler()->scheduleRepeatingTask(new HUDTask($this->plugin, $player), 20);
    }

    public function onQuit(PlayerQuitEvent $event){
        $player = $event->getPlayer();
        $event->setQuitMessage("§8[§c-§8] §b{$player->getName()} §eleft the server!");
    }


    public function onDamage(EntityDamageEvent $event) : void{
        $entity = $event->getEntity();
        if($entity instanceof Player){
            if($event->getCause() === EntityDamageEvent::CAUSE_FALL){
                $event->setCancelled(true);
            }
            if($event->getCause() !== $event::CAUSE_FALL){
                if(!$entity instanceof Player) return;
                if($entity->isCreative()) return;
                if($entity->getAllowFlight() == true){
                    $entity->setFlying(false);
                    $entity->setAllowFlight(false);
                    $entity->sendMessage("§cDisabled Flight since your in combat.");
                }
            }
        }
    }

    public function onMotion(EntityMotionEvent $event) : void{
        $entity = $event->getEntity();
        if($entity instanceof Living && !$entity instanceof Player){
            $event->setCancelled(true);
        }
    }

    public function onRespawn(PlayerRespawnEvent $event) : void{
        $player = $event->getPlayer();
        $title = "§l§cYOU DIED!";
        $subtitle = "§aRespawning...";
        $player->addTitle($title, $subtitle);
    }
}