<?php

namespace athletic\utils;

use pocketmine\Player;
use pocketmine\entity\Skin;

use Enes5519\PlayerHead\PlayerHead;
use Enes5519\PlayerHead\entities\HeadEntity;

class Head{

    private $player;

    public function __construct(Player $player){
        $this->player = $player;
    }

    public function getSkin(): Skin{
        $playerSkin = $this->player->getSkin();
        $skin = new Skin($this->player->getName(), $playerSkin->getSkinData(), $playerSkin->getCapeData(), $playerSkin->getGeometryName());
        return $skin;
    }

    public function createHead(Skin $skin): HeadEntity{
        $head = PlayerHead::spawnPlayerHead($skin, $this->getPlayer());
        return $head;
    }

    public function getPlayer(): Player{
        return $this->player;
    }
}
