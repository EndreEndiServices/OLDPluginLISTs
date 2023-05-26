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

 * edited by @rubmendk (no)

 */



namespace ManualUHC;



use pocketmine\command\Command;

use pocketmine\command\CommandSender;

use pocketmine\Player;

use pocketmine\item\Item;

use pocketmine\utils\TextFormat;



class UHCCommand extends Command {



    /** @var Main */

    private $plugin;



    /**

     * UHCCommand constructor.

     *

     * @param Main $plugin

     */

    public function __construct(Main $plugin) {

        $this->plugin = $plugin;

        parent::__construct("uhc", "Comando principal de UHC manual", "Uso: /uhc", []);

    }



    /**

     * @param CommandSender|Player $sender

     * @param $message

     */

    public function sendMessage($sender, $message) { $sender->sendMessage(TextFormat::RED . "§8[§4Deadkills§8]" . TextFormat::YELLOW . $message); }



    public function execute(CommandSender $sender, $commandLabel, array $args) {

        if($sender instanceof Player) {

            if(isset($args[0])) {

                if($sender->isOp()) {

                    switch($args[0]) {

                        case "m":

                            foreach($this->plugin->getServer()->getOnlinePlayers() as $player) {

                                // todo: Me dijiste que lo modificarías :)
                            $player->getPlayer()->getInventory()->clearAll();
                            $player->getPlayer()->removeAllEffects();
                            $player->getInventory()->addItem(Item::get(276, 0, 1));
                            $player->getInventory()->addItem(Item::get(278, 0, 1));
                            $player->getInventory()->addItem(Item::get(279, 0, 1));
                            $player->getInventory()->addItem(Item::get(322, 0, 15));
                            $player->getInventory()->addItem(Item::get(364, 0, 64));
                            $player->getInventory()->addItem(Item::get(5, 0, 64));	
                            $player->getInventory()->addItem(Item::get(1, 0, 64));
                            $player->getInventory()->setHelmet(Item::get(310, 0, 1));
                            $player->getInventory()->setChestplate(Item::get(311, 0, 1));
                            $player->getInventory()->setLeggings(Item::get(312, 0, 1));
                            $player->getInventory()->setBoots(Item::get(313, 0, 1));
                                $this->sendMessage($player, "§7Te han dado el kit para Meetups! Disfruta! ");


                          }
  
                          break;

                        case "s":

                            foreach($this->plugin->getServer()->getOnlinePlayers() as $player) {

                                // todo: Me dijiste que lo modificarías :)
                            $player->getPlayer()->getInventory()->clearAll();
                            $player->getPlayer()->removeAllEffects();
                            $player->getInventory()->addItem(Item::get(364, 0, 64));
                            $player->setWhitelisted(true);
                            $player->setMaxHealth(20);
                            $player->setHealth(20);
                            $player->setFood(20);
                            $player->setGamemode(0);
                                $this->sendMessage($player, "§7El UHC ha Empezado! ");

                            }

                            break;

                        case "e":

                            foreach($this->plugin->getServer()->getOnlinePlayers() as $player) {

                                // todo: Me dijiste que lo modificarías :)
                            $player->getPlayer()->getInventory()->clearAll();
                            $player->getPlayer()->removeAllEffects();
                            $player->getInventory()->addItem(Item::get(364, 0, 64));
                                $this->sendMessage($player, "§7+64 Steak! ");

                            }

                            break;

                        case "r":

                            // Modifica esto como veas y pones tus propias reglas

                            foreach($this->plugin->getServer()->getOnlinePlayers() as $player) {

                                $player->sendMessage(TextFormat::GREEN . "§7-=[§bReglas§7]=-:");

                                // todo: Pon las normas que quieras

                                $player->sendMessage(TextFormat::RED . "§8[§4Deadkills§8]" . TextFormat::GREEN . "\n§bSiguenos en Twitter §3@UHCDeadkills ");

                            }

                            break;

                        case "f":

                            foreach($this->plugin->getServer()->getOnlinePlayers() as $player) {

                                $player->setHealth($player->getMaxHealth());

                                $this->sendMessage($player, "§7¡Has sido curado!");

                            }

                            break;

                        case "h":

                            foreach($this->plugin->getServer()->getOnlinePlayers() as $player) {

                                $player->teleport($this->plugin->getServer()->getDefaultLevel()->getSafeSpawn());

                                $this->sendMessage($player, "§7Fuiste teletransportado al lobby");

                            }

                            break;

                        case "t":

                            foreach($this->plugin->getServer()->getOnlinePlayers() as $player) {

                                $player->sendPopup("§8[§4Deadkills§8]§7 Teleportation...")
                                $player->sendMessage(TextFormat::GREEN . "§8[§4Deadkills§8]§7 Teleportation...");
                                $player->sendMessage(TextFormat::GREEN . "§8[§4Deadkills§8]§7 Teleportation...");
                                $player->sendMessage(TextFormat::GREEN . "§8[§4Deadkills§8]§7 Teleportation...");
                                $player->sendMessage(TextFormat::GREEN . "§8[§4Deadkills§8]§7 Teleportation...");
                                $player->sendMessage(TextFormat::GREEN . "§8[§4Deadkills§8]§7 Teleportation...");
                                $player->sendMessage(TextFormat::GREEN . "§8[§4Deadkills§8]§7 Si te bugeas RELOGEA");
                                $player->sendMessage(TextFormat::GREEN . "§8[§4Deadkills§8]§7 Si te bugeas RELOGEA");
                                $player->sendMessage(TextFormat::GREEN . "§8[§4Deadkills§8]§7 Si te bugeas RELOGEA");
                                $player->teleport($sender->getLocation());

                            }

                            break;

                        case "l":

                            $sender->teleport($this->plugin->getServer()->getDefaultLevel()->getSafeSpawn());

                            break;

                        case "c":

                            foreach($this->plugin->getServer()->getOnlinePlayers() as $player) {

                                $player->getInventory()->clearAll();

                                $this->sendMessage($player, "§7Tu inventario fue reseteado ");

                            }

                            break;

                        case "w":

                            foreach($this->plugin->getServer()->getOnlinePlayers() as $player) {

                                $player->setWhitelisted(true);

                                $this->sendMessage($player, "§7Has sido añadido a la whitelist");

                            }

                            break;

                        case "p":

                            $eventListener = $this->plugin->getEventListener();

                            $eventListener->build = !$eventListener->build;

                            $text = ($eventListener->build) ? "Activaste" : "Desactivaste";

                            $this->sendMessage($sender, "§7{$text} el build");

                            break;

                        case "deathban":

                            $eventListener = $this->plugin->getEventListener();

                            $eventListener->deathban = !$eventListener->deathban;

                            $text = ($eventListener->deathban) ? "Activaste" : "Desactivaste";

                            $this->sendMessage($sender, "§b{$text} el deathban");

                            break;

                        case "info":

                            $messages = [

                                "m" => "§7Da kit a todos los jugadores (meetup)",

                                "f" => "§7Cura a todos los jugadores",

                                "info" => "§7Muestra los comandos aplicables",

                                "r" => "§7Muestra las reglas",

                                "e" => "§7Da +64 steak a todos",

                                "s" => "§7Empieza el UHC (da carne, apples, AutoWlAll y resetea inv y gamemode)",

                                "h" => "§7Teleporta a todos al hub",

                                "t" => "§7Teleporta a todos a tu posición actual",

                                "l" => "§7Te teletransporta al hub",

                                "c" => "§7Resetea los inventarios",

                                "deathban" => "§7Activa/desactiva el deathban",

                                "w" => "§7Añade a todos a la whitelist",

                                "p" => "§7Desactiva/activa la protección"

                            ];

                            foreach($messages as $name => $message) {

                                $sender->sendMessage(TextFormat::GREEN . "§4/UHC {$name} " . TextFormat::YELLOW . $message);

                            }

                            break;

                        default:

                            $this->sendMessage($sender, "§fSi no sabe usar /uhc, escriba /uhc info");

                            break;

                    }

                }

                else {

                    $this->sendMessage($sender, "§4Tienes que ser OP para usar este comando");

                }

            }

            else {

                $this->sendMessage($sender, "§fSi no sabe usar /uhc, escriba /uhc info");

            }

        }

        else {

            $this->sendMessage($sender, "§bPor favor, usa este comando en el juego.");

        }

    }



}