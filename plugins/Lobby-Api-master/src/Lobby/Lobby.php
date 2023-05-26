<?php
namespace Lobby;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\network\mcpe\protocol\TransferPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Lobby extends PluginBase implements Listener{

    public function onEnable(){
    	$this->getServer()->getPluginManager()->registerEvents($this, $this);
    	$this->exec();

    }

    public function exec(){
        $this->getLogger()->info(TextFormat::YELLOW."(Only-Serv) Working...");

        @mkdir($this->getDataFolder());

        $this->saveDefaultConfig();
        $this->getResource("config.yml");
        

    }

    public function Transfer(Player $player = null){
        $packet = new TransferPacket();

        $packet->address = $this->getConfig()->get("lobby.ip");
        $packet->port = $this->getConfig()->get("lobby.port");

        if($player == null){
            $this->getServer()->broadcastPacket($this->getServer()->getOnlinePlayers(), $packet);

            return;

        }

        $player->dataPacket($packet);

    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{

        switch ($command->getName()){

            case "lobby":
                if($sender instanceOf Player) $this->Transfer($sender);

                break;

        }

        return true;

    }

    public function onInteract(PlayerInteractEvent $event){
        $player = $event->getPlayer();
        $item = $event->getItem();

        if($item->getId() == $this->getConfig()->get("lobby.item.id") && $item->getDamage() == $this->getConfig()->get("lobby.item.damage")){
            $this->Transfer($player);

        }

    }

    public function onKick(PlayerKickEvent $event){
        $this->Transfer($event->getPlayer());

        $event->setCancelled(true);

    }


}
  