<?php
namespace PrisonCore\Commands;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\utils\Config;
use PrisonCore\Core;

use pocketmine\item\Item;
use pocketmine\inventory\Inventory;
use onebone\economyapi\EconomyAPI;

class withdraw extends Command{
	  public function __construct(Core $plugin){
	       $this->core = $plugin;
	       $this->economy = EconomyAPI::getInstance();
	       parent::__construct("withdraw", "Withdraw your inGame money on a paper!");
		}
	  public function execute(CommandSender $sender, $label, array $args){
	       if(!$sender instanceof Player){
		      $sender->sendMessage($this->core->prefix." §cRun this command on game!");
		      return true;
		      }
		    if(isset($args[0])){
			  if(is_numeric($args[0]) && $args[0] >= 1){
				 $value = $args[0];
				 if($this->economy->mymoney($sender) >= $value){
					 $cheque = Item::get(339, $value, 1);
					 $cheque->setCustomName("§aCheque of §b".$sender->getName()."§r \n§6Value: §7".$value."$");
					 $sender->getInventory()->addItem($cheque);
					 $this->economy->reduceMoney($sender, $value);
					 $sender->sendMessage($this->core->prefix." §aSuccessfully withdrawn cheque of §e".$value."$");
					 $sender->sendMessage($this->core->prefix." §c-".$value."$");
					}else{
					  $sender->sendMessage($this->core->prefix." §cYou don't have ".$value."$");
						}
				}else{
				   $sender->sendMessage($this->core->prefix." §cInvailid amount");
					}
			}else{
			  $sender->sendMessage($this->core->prefix." §cInvailid amount");
				}
      }
	}