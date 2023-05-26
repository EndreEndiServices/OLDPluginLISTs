<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 26.06.2016
 * Time: 23:00
 */

namespace justmaks19\shootingroom\task;


use justmaks19\shootingroom\ShootingRoom;
use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\MobSpawnParticle;
use pocketmine\math\Vector3;
use pocketmine\scheduler\PluginTask;

class TargetRemakeTask extends PluginTask
{

    /** @var Level */
    public $level;

    /** @var Block */
    public $block;
    
    public $stringPos;

    /** @var int */
    public $tick;

    public function __construct(ShootingRoom $plugin, Level $level, Block $block, $stringPos)
    {
        $this->level = $level;
        $this->block = $block;
        $this->stringPos = $stringPos;
        $this->tick = 5;
        parent::__construct($plugin);
    }

    /**
     * Actions to execute when run
     *
     * @param $currentTick
     *
     * @return void
     */
    public function onRun($currentTick)
    {
        $pos = explode(" ", $this->stringPos);
        $v3 = new Vector3($pos[0], $pos[1], $pos[2]);
        if($this->tick >= 1 && $this->tick <= 5){
            $this->tick--;
            $int = mt_rand(0,20);
            $yr = mt_rand(0, 1);
            if($int >= 0 && $int <= 10){
                $this->level->addParticle(new MobSpawnParticle(new Vector3($pos[0], $pos[1]+$yr, $pos[2])));
                $this->level->addParticle(new MobSpawnParticle(new Vector3($pos[0]+1, $pos[1]+$yr, $pos[2])));
                $this->level->addParticle(new MobSpawnParticle(new Vector3($pos[0]-1, $pos[1]+$yr, $pos[2])));
                $this->level->addParticle(new MobSpawnParticle(new Vector3($pos[0], $pos[1]+$yr, $pos[2]+1)));
                $this->level->addParticle(new MobSpawnParticle(new Vector3($pos[0], $pos[1]+$yr, $pos[2]-1)));
            }else if($int >= 1 && $int <= 20){
                $this->level->addParticle(new FlameParticle(new Vector3($pos[0], $pos[1]+$yr, $pos[2])));
                $this->level->addParticle(new FlameParticle(new Vector3($pos[0]+1, $pos[1]+$yr, $pos[2])));
                $this->level->addParticle(new FlameParticle(new Vector3($pos[0]-1, $pos[1]+$yr, $pos[2])));
                $this->level->addParticle(new FlameParticle(new Vector3($pos[0], $pos[1]+$yr, $pos[2]+1)));
                $this->level->addParticle(new FlameParticle(new Vector3($pos[0], $pos[1]+$yr, $pos[2]-1)));
            }
        }else if($this->tick == 0) {
            $this->getOwner()->getServer()->getScheduler()->cancelTasks($this->getOwner());
            unset($this->tick);
        }
    }
}