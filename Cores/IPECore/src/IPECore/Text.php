<?php
   
   namespace IPECore;
   
   use pocketmine\Server;
   use pocketmine\Player;
   use pocketmine\item\Item;
   use pocketmine\math\Vector3;
   use pocketmine\utils\UUID;
   use pocketmine\entity\Entity;
   use pocketmine\entity\Human;
   use pocketmine\nbt\tag\CompoundTag;
   use pocketmine\level\format\generic\GenericChunk;
   use pocketmine\event\entity\EntityDamageEvent;
   use pocketmine\network\protocol\AddPlayerPacket;
   
   class Text extends Human{
   	
   	public $text;
   	
   	public function __construct(GenericChunk $chunk, CompoundTag $nbt){
	   	parent::__construct($chunk, $nbt);
		  $this->text = $nbt->CustomName;
	  }
   	
   	public function attack($damage, EntityDamageEvent $source){
   		$source->setCancelled(true);
   		parent::attack($damage, $source);
   	}
   	
   	public function onUpdate($currentTick){
   		if($this->closed){
   			return false;
   		}
   		if(!isset($this->namedtag->infos)){
   			return false;
   		}
   		$info=$this->namedtag->infos;
   		$file=$info["file"];
   		if($file!=""){
   		 $data=$info["datafolder"];
   		 $tag = file_get_contents($data); // fixed "ō" bug !
   		 $text=Main::getInstance()->replacedText($tag);
   		 $this->setNameTag($text);
   		}else{
   			$tag=$this->getNameTag(); //fixed now! No update bug !
   			$text=Main::getInstance()->replacedText($tag);
   			$this->setNameTag($text);
   		}
   	}
   	
   	public function spawnTo(Player $p){
   		$main=Main::getInstance();
    $pk = new AddPlayerPacket();
    $pk->eid = $this->getId();
    $pk->uuid = UUID::fromRandom();
    $pk->x = $this->x;
    $pk->y = $this->y;
    $pk->z = $this->z;
    $pk->speedX = 0;
    $pk->speedY = 0;
    $pk->speedZ = 0;
    $pk->yaw = 0;
    $pk->pitch = 0;
    $pk->item = Item::get(0);
    $this->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_INVISIBLE, true);
    $this->setNameTag($main->replaceForPlayer($p, $this->text));
    $this->setNameTagAlwaysVisible(true);
    $this->setNameTagVisible(true);
    $pk->metadata = $this->dataProperties;
    $p->dataPacket($pk);
	Server::getInstance()->removePlayerListData($this->getUniqueId(), [$p]);	
    $this->saveNBT();
    parent::spawnTo($p);
   	}
   }
?>
