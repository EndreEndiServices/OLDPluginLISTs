<?php

namespace Commands;

use pocketmine\Server;
use pocketmine\Player;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginCommand;

use pocketmine\item\Item;

use pocketmine\inventory\Inventory;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;

use pocketmine\level\Level;

use pocketmine\utils\TextFormat as C;

use CrateSystem\Main;

class buykey extends PluginCommand{

    public function __construct($name, Main $plugin){
        parent::__construct($name, $plugin);
        $this->setDescription("Buy a crate key");
        $this->setAliases(["Buykey"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
        if($sender instanceof Player){
        $form = $this->getPlugin()->createSimpleForm(function (Player $sender, array $data){
            $result = $data[0];
            if ($result === null){
            }
            switch ($result){
                case 1:
                    //common shop
                    $this->getPlugin()->CommonShop->Start($sender);
                    break;
                case 2:
                    //vote shop
                    $this->getPlugin()->VoteShop->Start($sender);
                    break;
                case 3:
                    //rare shop
                    $this->getPlugin()->RareShop->Start($sender);
                    break;
                case 4:
                    //mythic shop
                    $this->getPlugin()->MythicShop->Start($sender);
                    break;
                case 5:
                    //legendary shop
                    $this->getPlugin()->LegendaryShop->Start($sender);
					break;
                }
            });

            $form->setTitle("§9Crates Shop");
            $form->setContent("§eSelect a key:");

            $form->addButton("§fExit");
            $form->addButton("§aCommon", 1);
            $form->addButton("§cVote", 2);
            $form->addButton("§6Rare", 3);
            $form->addButton("§5Mythic", 4);
            $form->addButton("§9Legendary", 5);

            $form->sendToPlayer($sender);
        }else{
            $sender->SendMessage("§cYou are not In-Game.");
        }
        return true;
        }
}
