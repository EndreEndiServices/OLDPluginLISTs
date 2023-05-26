<?php
namespace NMagic_Stick;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as F;
use pocketmine\item\Item;
use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\BaseInventory;
use pocketmine\item\enchantment\Enchantment;
use onebone\economyapi\EconomyAPI;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\level\sound\ClickSound;
use pocketmine\scheduler\CallbackTask;

class MStick extends PluginBase implements Listener{

    public function onCommand(CommandSender $sender,Command $command,$label,array $args){
    	$command = strtolower($command);
        if($command == 'magicshop'){
            $sender->sendMessage("§8(§cСервер§8) §7>§e Вы телепортированы в магический магазин");
            $sender->teleport(new Position(170,66,104,$this->getServer()->getDefaultLevel()));
        }
        if($command == 'mg'){
            if(count($args) == 0){
                $sender->sendMessage("§6Волшебные Палочки§7:\n §cПалочка Защиты §7- §a30.000$ §8(§f/mg 1§8)\n §cПалочка Остроты §7- §a30.000$ §8(§f/mg 2§8) \n §cПалочка Огня §7- §a30.000$ §8(§f/mg 3§8) \n §cПалочка Отбрасывания §7- §a30.000$ §8(§f/mg 4§8)");
            }elseif(count($args) == 1){
                if($args[0] == 1){
                	$money = EconomyAPI::getInstance()->myMoney($sender);
                    if(30000 <= $money){
                        $item_enchant = Item::get(280,0,1);
                        $item_enchant->setCustomName(F::RED."Волшебная Палочка");
                        $item_enchant->addEnchantment(Enchantment::getEnchantment(0)->setLevel(10));
                        $sender->getInventory()->addItem($item_enchant);
                        $sender->sendMessage("§8(§6Волшебные Палочки§8) §7>§f Вы успешно купили §cПалочку Защиты §fза §a30.000$");
                        EconomyAPI::getInstance()->reduceMoney($sender, 30000);
                    }else{
                        $sender->sendMessage("§8(§6Волшебные Палочки§8) §7>§f У вас не достаточно денег!");
                    }
                }elseif($args[0] == 2){
                	$money = EconomyAPI::getInstance()->myMoney($sender);
                    if(30000 <= $money){
                        $item_enchant = Item::get(280,0,1);
                        $item_enchant->setCustomName(F::RED."Волшебная Палочка");
                        $item_enchant->addEnchantment(Enchantment::getEnchantment(16)->setLevel(10));
                        $sender->getInventory()->addItem($item_enchant);
                        $sender->sendMessage("§8(§6Волшебные Палочки§8) §7>§f Вы успешно купили §cПалочку Остроты §fза §a30.000$");
                        EconomyAPI::getInstance()->reduceMoney($sender, 30000);
                    }else{
                        $sender->sendMessage("§8(§6Волшебные Палочки§8) §7>§f У вас не достаточно денег!");
                    }
                }elseif($args[0] == 3){
                	$money = EconomyAPI::getInstance()->myMoney($sender);
                    if(30000 <= $money){
                        $item_enchant = Item::get(280,0,1);
                        $item_enchant->setCustomName(F::RED."Волшебная палочка");
                        $item_enchant->addEnchantment(Enchantment::getEnchantment(20)->setLevel(10));
                        $sender->getInventory()->addItem($item_enchant);
                        $sender->sendMessage("§8(§6Волшебные Палочки§8) §7>§f Вы успешно купили §cПалочку Огня §fза §a30.000$");
                        EconomyAPI::getInstance()->reduceMoney($sender, 30000);
                    }else{
                        $sender->sendMessage("§8(§6Волшебные Палочки§8) §7>§f У вас не достаточно денег!");
                    }
                }elseif($args[0] == 4){
                	$money = EconomyAPI::getInstance()->myMoney($sender);
                    if(30000 <= $money){
                        $item_enchant = Item::get(280,0,1);
                        $item_enchant->setCustomName(F::RED."Волшебная палочка");
                        $item_enchant->addEnchantment(Enchantment::getEnchantment(19)->setLevel(10));
                        $sender->getInventory()->addItem($item_enchant);
                        $sender->sendMessage("§8(§6Волшебные Палочки§8) §7>§f Вы успешно купили §cПалочку Отбрасывания §fза §a30.000$");
                        EconomyAPI::getInstance()->reduceMoney($sender, 3000);
                    }else{
                        $sender->sendMessage("§8(§6Волшебные Палочки§8) §7>§f У вас не достаточно денег!");
                    }
                }else{
                $sender->sendMessage("§8(§6Волшебные Палочки§8) §7>§f Используй §a/mg (1-4)");
                }
            }else{
                $sender->sendMessage("§8(§6Волшебные Палочки§8) §7>§f Используй §a/mg (1-4)");
            }
        }
    }

    public function onDrink(PlayerInteractEvent $e){
        $player = $e->getPlayer();
        $name = $player->getName();
        $item = $player->getInventory()->getItemInHand();
        $c_name = $item->getCustomName();
        $_1 = F::RED."Палочка Защиты";
        $_2 = F::RED."Палочка Остроты";
        $_3 = F::RED."Палочка Огня";
        $_4 = F::RED."Палочка Отбрасывания";
        if($c_name == $_1){
            $item_enchant = Item::get(280,0,1);
            $item_enchant->setCustomName(F::RED."Палочка Защиты");
            $item_enchant->addEnchantment(Enchantment::getEnchantment(0)->setLevel(10));
            $player->getInventory()->addItem($item_enchant);
        }elseif($c_name == $_2){
            $item_enchant = Item::get(280,0,1);
            $item_enchant->setCustomName(F::RED."Палочка Остроты");
            $item_enchant->addEnchantment(Enchantment::getEnchantment(16)->setLevel(10));
            $player->getInventory()->addItem($item_enchant);
        }elseif($c_name == $_3){
            $item_enchant = Item::get(280,0,1);
            $item_enchant->setCustomName(F::RED."Палочка Огня");
            $item_enchant->addEnchantment(Enchantment::getEnchantment(20)->setLevel(10));
            $player->getInventory()->addItem($item_enchant);
        }elseif($c_name == $_4){
            $item_enchant = Item::get(280,0,1);
            $item_enchant->setCustomName(F::RED."Палочка Отбрасывания");
            $item_enchant->addEnchantment(Enchantment::getEnchantment(19)->setLevel(10));
            $player->getInventory()->addItem($item_enchant);
        }
    }
}