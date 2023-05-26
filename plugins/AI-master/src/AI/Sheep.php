<?php

namespace AI;

use pocketmine\block\Liquid;
use pocketmine\entity\EffectInstance;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Item;
use pocketmine\level\particle\HugeExplodeParticle;
use pocketmine\level\particle\MobSpawnParticle;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\entity\Attribute;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\particle\GenericParticle;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\utils\Color;


class Sheep extends EntityAI{

    const NETWORK_ID = 13;

    const DATA_COLOR_BASIC = "aleatory";

    public $entityTimer = [];

    public $width = 1.3;
    public $length = 1.4;
    public $height = 1.79;

    public $forcemovement = 0;

    public $forcelook = false;
    public $selectlook = false;

    public $target = null;

    public $observe = false;

    public $xPosition = 0;
    public $zPosition = 0;

    public $yTimer = 0;

    public $entityDamageCount = 0;

    public $panic = false;

    public $age = 0;

    public function initEntity(): void{
        parent::initEntity();

        $this->setClassic();

    }

    public function setClassic(){
        $this->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->setValue(0.23000000417232513);

        $this->setGenericFlag(self::DATA_FLAG_IMMOBILE, false);
        $this->setWoolColor(self::DATA_COLOR_BASIC);

        /*Basic*/

        $this->entityTimer["movement"] = 0;
        $this->entityTimer["panic"] = 0;
        $this->entityTimer["eat"] = 0;
        $this->entityTimer["shear"] = 0;

        /*AddOn*/

        $this->entityTimer["add"] = 0;
        $this->entityTimer["damage"] = 0;

        $this->setName(false);

    }

    public function entityBaseTick(int $tickDiff = 1): bool{
        $hasUpdate = parent::entityBaseTick($tickDiff);

        /*Movement*/

        $this->entityTimer["movement"] += 1;

        if($this->panic == true){

            if($this->entityTimer["movement"] >= mt_rand(15, 25)){
                $this->entityTimer["movement"] = 0;
                $this->forcelook = true;

                $this->setMovement();

            }

            $this->entityTimer["panic"] += 1;

            if($this->entityTimer["panic"] >= mt_rand(130, 140)){
                $this->entityTimer["panic"] = 0;
                $this->panic = false;

            }

        }elseif($this->entityTimer["movement"] >= mt_rand(60, 80)){
            $this->entityTimer["movement"] = 0;
            $this->forcelook = true;
            $this->observe = false;

            /*Observe*/

            if(0 == mt_rand(0, 8)){
                $this->forcelook = false;
                $this->observe = true;

                $this->setLook();

            }else{
                $this->selectlook = false;

            }

            $this->setMovement();

        }

        $this->setMovement(false);

        /*Observe*/

        if($this->forcemovement == 0 && $this->panic == false){
            $this->forcelook = false;
            $this->observe = true;

            $this->setLook();

        }else{
            $this->forcelook = true;
            $this->observe = false;

            $this->selectlook = false;

        }

        /*Eat*/

        $this->entityTimer["eat"] += 1;

        if($this->entityTimer["eat"] >= mt_rand(100, 120) && $this->panic == false){

            if($this->getBaby() == true){
                if(0 == mt_rand(0, 300)) $this->setEat();

            }else{
                if(0 == mt_rand(0, 350)) $this->setEat();

            }

        }

        /*Age*/

        $this->age();

        /*Shear*/

        if($this->getShear() == true){
            $this->entityTimer["shear"] += 1;

            if($this->entityTimer["shear"] >= 12000){
                $this->entityTimer["shear"] = 0;

                $this->setShear(false);

            }

        }

        /*Follow*/
        //TODO

        /*AddOn*/

        /*
         * used to mod the AI
         *
         */

        $this->addOn();

        return $hasUpdate;
        
    }

    public function addOn(){
        $this->entityTimer["add"] += 1;

        if($this->entityTimer["add"] >= 5){
            $this->setWoolColor();
            $this->setEffectColor(0, mt_rand(1, 255), mt_rand(1, 255), mt_rand(1, 255));

            $this->entityTimer["add"] = 0;

        }

        if($this->getY() <= 35){
            $this->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());

        }

    }

    public function setName($set, String $name = ""){
        if($name == ""){
            $name = $this->getName();

        }

        $this->setNameTag($name);
        
        $this->setNameTagVisible($set);
        $this->setNameTagAlwaysVisible($set);

    }

    public function getName(): string{
        return "§l§3FISH§r§lSHEEP";

    }

    public function getDrops(): array{
        $mutton = Item::get(Item::RAW_MUTTON, 0, mt_rand(1, 2));
        $wool = Item::get(Item::WOOL, $this->getWoolColor(), 1);

        if($this->isOnFire()){
            $mutton = Item::get(Item::COOKED_MUTTON, 0, 1);

        }

        if($this->getShear() == true){
            $wool = Item::get(0, 0, 1);

        }

        if($this->getBaby() == true){
            return [];

        }else{
            return [
                $mutton,
                $wool

            ];

        }

    }

    public function setShear($shear){
        $this->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_SHEARED, $shear);

    }

    public function getShear(){
        return $this->getDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_SHEARED);

    }

    public function setBaby($baby){
        $this->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_BABY, $baby);

        if($baby == true){
            $this->setScale(0.5);

        }else{
            $this->setScale(1);

        }

    }

    public function getBaby(){
        return $this->getDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_BABY);

    }

    public function setLook(){
        $viewers = [];

        foreach(Server::getInstance()->getOnlinePlayers() as $player){

            if($player->getPosition()->distance($this->getPosition()) <= 8){
                $viewers[] = $player;

            }

        }

        if(count($viewers) <= 0){
            return;

        }

        if($this->selectlook == false){
            unset($this->target);
            $this->target = $viewers[array_rand($viewers)];

            $this->selectlook = true;

        }

        if($this->target instanceof Player or $this->target instanceof Position){
            $this->lookAt(new Vector3(round($this->target->getX()), round($this->target->getY()) + 0.5, round($this->target->getZ())));

        }

    }

    public function setEat(){
        $pk = new EntityEventPacket();

        $pk->entityRuntimeId = $this->getId();
        $pk->event = EntityEventPacket::EAT_GRASS_ANIMATION;

        Server::getInstance()->broadcastPacket($this->getViewers(), $pk);

    }

    public function setMovement($movement = true){
        if($movement == true){
            $this->xPosition = mt_rand(-8, 8);
            $this->zPosition = mt_rand(-8, 8);

        }

        if($this->panic == true){

            if($this->xPosition == 0 && $this->zPosition == 0){
                return $this->setMovement();

            }

        }

        if($this->xPosition == 0 or $this->zPosition == 0 or $this->observe == true){
            return $this->forcemovement = 0;

        }

        if($this->forcelook == true){
            $this->setYaw($this->xPosition, $this->zPosition);
            $this->setPitch($this->xPosition, 0, $this->zPosition);

        }

        if(Server::getInstance()->getDefaultLevel()->getBlock($this->getPosition()->add(0, 0.5, 0)) instanceof Liquid){
            $this->setSwimming(true);

        }

        if($this->panic == true){
            $this->move($this->xPosition / (8 * abs($this->xPosition)), 0, $this->zPosition / (8 * abs($this->zPosition)));

        }else{
            $this->move($this->xPosition / (13 * abs($this->xPosition)), 0, $this->zPosition / (13 * abs($this->zPosition)));

        }

        if($this->yTimer < 15){
            $this->yTimer += 1;

        }

        if(
            ($this->lastX == $this->getX() && $this->lastY == $this->getY()) or
            ($this->lastZ == $this->getZ() && $this->lastY == $this->getY())

        ){

            if($this->yTimer == 15){
                $this->move($this->xPosition / (13 * abs($this->xPosition)), 1.8, $this->zPosition / (13 * abs($this->zPosition)));

                $this->yTimer = 0;

            }

        }

        return $this->forcemovement = 1;

    }

    public function setYaw($x, $z){
        $this->yaw = rad2deg(atan2( - $x, $z));

    }

    public function setPitch($x, $y, $z){
        $this->pitch = rad2deg( - atan2($y, sqrt($x * $x + $z * $z)));

    }

    public function setSwimming($swim){
        //TODO

    }

    public function setEffect($effect = -1, Int $amplifier = 1, Int $duration = INT32_MAX, bool $visible = true){
        if($effect == -1){
            $this->removeAllEffects();

            return;

        }

        if(Effect::getEffect($effect) instanceof Effect){
            $this->addEffect(new EffectInstance(Effect::getEffect($effect), $duration, $amplifier, $visible));

        }

    }

    public function setEffectColor($color = -1, Int $r = 0, Int $g = 0, Int $b = 0){
        if($color == -1){
            $this->removeEffect(1);

            return;

        }

        $effect = new Effect(Effect::SPEED, "%potion.moveSpeed", new Color($r, $g, $b));

        $this->addEffect(new EffectInstance($effect, INT32_MAX, 1, true));

    }

    public function setWoolColor($color = -1, bool $send = true){
        if($color == -1){
            $color = mt_rand(0, 15);

        }elseif($color == self::DATA_COLOR_BASIC){
            $rand = mt_rand(0, 100000);

            if($rand <= 164){
                $color = 6;

            }elseif($rand <= 3164){
                $color = 12;

            }elseif($rand <= 8164){
                $color = 8;

            }elseif($rand <= 13164){
                $color = 7;

            }elseif($rand <= 18164){
                $color = 15;

            }elseif($rand <= 100000){
                $color = 0;

            }

        }

        $this->getDataPropertyManager()->setPropertyValue(Entity::DATA_COLOR, Entity::DATA_TYPE_BYTE, $color, $send);

    }

    public function getWoolColor(){
        return $this->getDataPropertyManager()->getPropertyValue(Entity::DATA_COLOR, Entity::DATA_TYPE_BYTE);

    }

    public function setParticle($particle){
        Server::getInstance()->getDefaultLevel()->addParticle(new GenericParticle(new Vector3($this->getX(), $this->getY() + 1,  $this->getZ()), $particle));

    }

    public function setSound($sound){
        Server::getInstance()->getDefaultLevel()->broadcastLevelSoundEvent(new Vector3($this->getX(), $this->getY() + 1, $this->getZ()), $sound);

    }

    public function attack(EntityDamageEvent $source): void{
        $entity = $source->getEntity();

        if($entity instanceof Sheep){
            $source->setCancelled(true);

            if($source instanceof EntityDamageByEntityEvent &&  $source->getDamager() instanceof Player){
                $player = $source->getDamager();

                if($this->getBaby() == false){
                    $this->entityTimer["damage"] += 1;

                    if($this->entityTimer["damage"] >= 6){
                        $this->entityTimer["damage"] = 0;

                        $this->age = 0;

                        $this->setBaby(true);

                        $this->setMotion(new Vector3(mt_rand(-1, 1) / 6, 1, mt_rand(-1, 1) / 6));
                        Server::getInstance()->getDefaultLevel()->addParticle(new HugeExplodeParticle(new Vector3($this->getX(), $this->getY() + 0.5, $this->getZ())));
                        $this->setSound(LevelSoundEventPacket::SOUND_EXPLODE);

                        if(Server::getInstance()->getPluginManager()->getPlugin("Fireworks")){
                            $FireworkColors = [1 => "red", 6 => "dark_aqua", 10 => "green", 11 => "yellow", 12 => "aqua", 13 => "purple", 15 => "white"];

                            Server::getInstance()->getPluginManager()->getPlugin("Fireworks")->entity_Fireworks($player, $this->asVector3(), 0, true, array_rand($FireworkColors), true, true, mt_rand(0, 2));

                        }

                    }else{
                        $this->setMotion(new Vector3(mt_rand(-1, 1) / 6, 0.6, mt_rand(-1, 1) / 6));
                        Server::getInstance()->getDefaultLevel()->addParticle(new MobSpawnParticle(new Vector3($this->getX(), $this->getY(), $this->getZ()), 0.5, 0.5));

                    }

                }else{
                    $this->setMotion(new Vector3(mt_rand(-1, 1) / 6, 0.6, mt_rand(-1, 1) / 6));
                    Server::getInstance()->getDefaultLevel()->addParticle(new MobSpawnParticle(new Vector3($this->getX(), $this->getY(), $this->getZ()), 0.5, 0.5));

                }

            }else{
                $this->setMotion(new Vector3(mt_rand(-1, 1) / 6, 0.6, mt_rand(-1, 1) / 6));
                Server::getInstance()->getDefaultLevel()->addParticle(new MobSpawnParticle(new Vector3($this->getX(), $this->getY(), $this->getZ()), 0.5, 0.5));

            }

        }

        parent::attack($source);

    }

    public function age($age = 3680){
        $this->age += 1;

        if($this->getBaby() == true){

            if($this->age >= $age + mt_rand(0, 1020)){
                $this->setBaby(false);

            }

        }

        return $this->age;

    }

    public function follow(){
        //TODO

    }

    public function reproduce(){
        //TODO

    }

    public function shear(){
        //TODO

    }

    public function color(){
        //TODO

    }


}
