<?php

namespace ow;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\HeartParticle;
use pocketmine\level\particle\LavaParticle;
use pocketmine\level\particle\PortalParticle;
use pocketmine\level\sound\ClickSound;
use pocketmine\level\sound\AnvilUseSound;
use pocketmine\level\sound\BatSound;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat;
use pocketmine\utils\TextFormat as F;
use pocketmine\level\particle\AngryVillagerParticle;
use pocketmine\level\particle\WaterDripParticle;
use pocketmine\entity\Effect;
use pocketmine\block\Block;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\level\particle\ExplodeParticle;
use pocketmine\utils\Config;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\level\particle\ItemBreakParticle;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerToggleSprintEvent;

class owperms extends PluginBase implements Listener {
	private $playerGroup;
	private $mysqli;
	
	public function onEnable() {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->mysqli = new \mysqli("", "", "", "");
	}
	
	public function setPrefixTag($player) {
		$group = $this->getGroup($player->getName());
		if($group == "user") {
			$player->setNameTag(F::GRAY. "user " .F::DARK_AQUA. $player->getName());
		}
		if($group == "vip") {
			$player->setNameTag(F::BLUE. F::BOLD. "vip " .F::DARK_AQUA. $player->getName());
		}
		if($group == "premium") {
			$player->setNameTag(F::LIGHT_PURPLE. F::BOLD. "premium " .F::DARK_AQUA. $player->getName());
		}
		if($group == "helper") {
			$player->setNameTag(F::DARK_GREEN. F::BOLD. "helper " .F::DARK_AQUA. $player->getName());
		}
		if($group == "admin") {
			$player->setNameTag(F::RED. F::BOLD. "admin " .F::DARK_AQUA. $player->getName());
		}
		if($group == "youtube") {
			$player->setNameTag(F::BOLD. F::WHITE. "You" .F::RED. "Tube " .F::DARK_AQUA. $player->getName());
		}
	}
	
	public function checkAcc($username) {
	    $username = strtolower($username);
		$result = $this->mysqli->query("SELECT * FROM `perms` WHERE `nickname` = '".$username."'");
        $user = mysqli_fetch_assoc($result);
        if($user) {
	        return true;
        } else {
	        return false;
        }
	}
	
	public function updateGroup($username) {
	    $username = strtolower($username);
	    if($this->checkAcc($username)) {
	        $this->playerGroup[$username] = $this->checkGroup($username);
		} else {
		    $this->playerGroup[$username] = "user";
		    $this->createData($username);
		}
	}
	
	public function onJoin(PlayerJoinEvent $e) {
		$player = $e->getPlayer();
		if($this->checkAcc($player->getName())) {
		    $this->updateGroup($player->getName());
			$this->setPrefixTag($player);
		} else {
		    $this->createData($player->getName());
		}
	}
	
	public function createData($username) {
	    $username = strtolower($username);
        if(!$this->checkAcc($username)) {
            $this->mysqli->query("INSERT INTO `perms` (`id`, `nickname`, `group`) VALUES (NULL, '".$username."', 'user')");
			$this->getServer()->getLogger()->info(F::YELLOW. "[OWPerms]" .F::GOLD. " Создана таблица игроку " .F::GREEN. $username);
			$this->updateGroup($username);
		}
	}
	
	public function checkGroup($username) {
	    $username = strtolower($username);
        $result = $this->mysqli->query("SELECT * FROM `perms` WHERE `nickname` = '".$username."'");
		if($this->checkAcc($username)) {
			$data = $result->fetch_assoc();
			$result->free();
			if(isset($data["group"])){
			    return $data["group"];
			}
		} else {
		    $this->createData($username);
		}
	}
	
	public function getGroup($username) {
	    $username = strtolower($username);
	    if(isset($this->playerGroup[$username])) {
			return $this->playerGroup[$username];
		} else {
		    $this->updateGroup($username);
		}
	}
	
	public function setGroup($username, $group) {
	    $username = strtolower($username);
		$group = strtolower($group);
        if($this->checkAcc($username)) {
            $this->mysqli->query("UPDATE `perms` SET `group` = '".$group."' WHERE `nickname` = '".$username."'");
			$this->getServer()->getLogger()->info(F::YELLOW. "[OWPerms]" .F::GOLD. " Игрок " .F::GREEN. $username. F::GOLD. " получил группу " .F::GREEN. $group);
			$this->updateGroup($username);
		} else {
		    $this->createData($username);
            $this->mysqli->query("UPDATE `perms` SET `group` = '".$group."' WHERE `nickname` = '".$username."'");
			$this->updateGroup($username);
		}
	}

    public function onCommand(CommandSender $entity, Command $cmd, $label, array $args) {
        switch ($cmd->getName()) {

            case "owperms":
			if($entity->isOp()) {
                if(isset($args[0])) {
				    if($args[0] == "setgroup") {
					    if(isset($args[1])) {
						    if(isset($args[2])) {
						    	if($args[2] == "admin" || $args[2] == "user" || $args[2] == "helper" || $args[2] == "vip" || $args[2] == "premium" || $args[2] == "youtube") {
								    $this->setGroup($args[1], $args[2]);
								    if($this->getServer()->getPlayer($args[1]) Instanceof Player) {
									    $this->getServer()->getPlayer($args[1])->sendMessage(F::YELLOW. "[OWPerms]" .F::GOLD. " поздравляем! Вы получили группу " .F::AQUA. $args[2]. F::GOLD. "!");
								    }
								    $entity->sendMessage(F::YELLOW. "[OWPerms]" .F::GOLD. " вы выдали группу " .F::AQUA. $args[2] .F::GOLD. " игроку " .F::GREEN. $args[1] .F::GOLD. ".");
									$this->getServer()->getLogger()->info(F::YELLOW. "[OWPerms]" .F::GREEN. $entity->getName(). " выдал игроку " .F::GREEN. $args[1]. F::GOLD. " группу " .F::GREEN. $args[2]. F::GOLD. ".");
							    } else {
								    $entity->sendMessage(F::YELLOW. "[OWPerms]" .F::GOLD. " такой группы не существует.");
							    }
							}
						}
					}
				}
			} else {
				$entity->sendMessage(F::YELLOW. "[OWPerms]". F::GOLD. " недостаточно прав!");
			}
		}
	}

 
}