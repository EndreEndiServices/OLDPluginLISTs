<?php
namespace fwarps;

use pocketmine\command\CommandSender;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\utils\TextFormat as F;

class warp{
	public $p;
	public $name;
	public function __construct(Position $pos, $n){
		$this->p = $pos;
		$this->name = $n;
	}
	public function warp(CommandSender $p){
		if($this->canUse($p) && $p instanceof Player){
			$p->teleport($this->p);
			$p->sendMessage(F::YELLOW. "§7(§cВарп§7) §aТелепортация..");
		}
        else{
            $p->sendMessage(F::YELLOW. "»" .F::RED. " Вы не имеете доступ к этому варпу");
        }
	}
    public function warpAs(CommandSender $sender, Player $p){
        if($this->canUse($sender)){
            $p->teleport($this->p);
            $p->sendMessage(F::YELLOW. "»".F::GOLD." Администратор " .F::RED.$sender->getName().F::GOLD. " телепортировал тебя на варп " .F::GREEN. $this->name);
            $sender->sendMessage(F::YELLOW. "»" .F::GOLD. " Вы телепортировали игрока ".F::RED. $p->getPlayer()->getName() .F::GOLD." на варп ".F::GREEN.$this->name);
        }
        else{
            $sender->sendMessage(F::YELLOW. "»" .F::RED. " Вы не имеете доступ к этому варпу");
        }
    }
    public function canUse(CommandSender $player){
        return ($player->hasPermission("fapi.warp"));
    }
}