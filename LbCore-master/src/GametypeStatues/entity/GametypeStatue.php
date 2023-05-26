<?php
namespace GametypeStatues\entity;

use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\network\protocol\AddPlayerPacket;
use pocketmine\network\protocol\PlayerListPacket;
use Kits\KitData;
use LbCore\LbCore;
use Kits\task\SaveKitsTask;

/**
 * Describes statue entity common options
 */
class GametypeStatue extends Human {
	/**@var int*/
	public $NPCId = 0;
    public $text = "";
	public $type = "";
	
    public function getSaveId() {
        return "Human";
    }
	
   public function spawnTo(Player $player) {
	   if($player !== $this and !isset($this->hasSpawned[$player->getId()])){
			$pk = new PlayerListPacket();
			$pk->type = PlayerListPacket::TYPE_ADD;
			$pk->entries[] = [$this->getUniqueId(), $this->getId(), $this->getName(), $this->skinName, $this->skin];
					
			$pk2 = new PlayerListPacket();
			$pk2->type = PlayerListPacket::TYPE_REMOVE;
			$pk2->entries[] = [$this->getUniqueId()];			
			
			$this->hasSpawned[$player->getId()] = $player;			
			$pk3 = new AddPlayerPacket();
			$pk3->uuid = $this->getUniqueId();
			$pk3->username = $this->getName();
			$pk3->eid = $this->getId();
			$pk3->x = $this->x;
			$pk3->y = $this->y;
			$pk3->z = $this->z;
			$pk3->speedX = $this->motionX;
			$pk3->speedY = $this->motionY;
			$pk3->speedZ = $this->motionZ;
			$pk3->yaw = $this->yaw;
			$pk3->pitch = $this->pitch;
			$pk3->item = $this->getInventory()->getItemInHand();
			$pk3->metadata = $this->dataProperties;
			
			$this->server->batchPackets([$player], [$pk, $pk3, $pk2]);		   
			$this->inventory->sendArmorContents($player);	
			$this->inventory->sendHeldItem($player);
		}		
	}
	
	/**
	 * Calls when player attacks statue - inform him about game type dns
	 * 
	 * @param $damage
	 * @param EntityDamageEvent $source
	 */
	public function attack($damage, EntityDamageEvent $source) {
		$source->setCancelled();
		if (!($source instanceof EntityDamageByEntityEvent)) {
			return;
		}
        $player = $source->getDamager();
		if($this->type == "game"){
			$player->sendMessage(TextFormat::GRAY.$this->text);
		}else{
			// Check to see if they hit a sign that is a Kit sign
			$kitId = $this->NPCId;
			$kit = KitData::getKit($kitId);
			if (!isset($kit->name)) {
				return;
			}
			// If they tapped the same sign twice then give them the kit! Or if they tap once give kit info
			if ($player->kitSignLastTapped === $kitId) {
				if ($player->haveKit($kitId)) {
					$player->sendLocalizedMessage("HAVE_KIT");
					return;
				}
				if (!$player->isAuthorized() || !$player->isVip()) {
					$player->sendLocalizedMessage("ONLY_FOR_VIP");
					return;
				}
				try {
					$player->addKit($kitId);
				} catch (PlayerBaseException $e) {
					LbCore::getInstance()->getLogger()->warning($e->getMessage());
					return;
				}
				// save kits into db
				$task = new SaveKitsTask($player->getName(), $player->getKits());
				LbCore::getInstance()->getServer()->getScheduler()->scheduleAsyncTask($task);
				$player->sendLocalizedMessage("VIP_SELECT_KIT", array($kit->name));
			} else {
				$player->kitSignLastTapped = $kitId;
				$player->sendMessage(TextFormat::YELLOW . 'The ' . TextFormat::DARK_PURPLE . $kit->name . TextFormat::YELLOW . ' kit:');
				$player->sendLocalizedMessage($kit->description, [], TextFormat::YELLOW);
				$player->sendLocalizedMessage("TAP_TO_SELECT_KIT");
			}
		}
	}
	
	/**
	 * Nothing to drop
	 * @return array
	 */
	public function getDrops(){
		return [];
	}
}
