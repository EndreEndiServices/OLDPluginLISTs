<?php

declare(strict_types=1);

namespace MXSB\GreenWix\generator;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\biome\Biome;
use pocketmine\level\generator\biome\BiomeSelector;
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\object\OreType;
use pocketmine\level\generator\populator\GroundCover;
use pocketmine\level\generator\populator\Ore;
use pocketmine\level\generator\populator\Populator;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class SkyBlockGenerator extends Generator
{


    /** @var Level */
    protected $level;


    /** @var Random */
    protected $random;


    public function __construct(array $options = [])
    {
    }


    /**
     * Inits the class for the var
     * @param ChunkManager $level
     * @param Random $random
     * @return        void
     */
    public function init(ChunkManager $level, Random $random) : void
    {
        $this->level = $level;
        $this->random = $random;

    }

    public function getSettings(): array
    {
        return [];
    }


    /***
     * Вернёт название генератора
     *
     * @return string
     */
    public function getName(): string
    {
        return "sb";
    }


    /**
     * Generates a chunk
     *
     * @param int $chunkX
     * @param int $chunkZ
     * @return void
     */
    public function generateChunk(int $chunkX, int $chunkZ) : void
    {
        $chunk = $this->level->getChunk($chunkX, $chunkZ);
        for ($x = 0; $x < 16; $x++) {
            for ($z = 0; $z < 16; $z++) {
                $chunk->setBiomeId($x, $z, 1);
                if ($chunkX == 0 && $chunkZ == 0) $chunk->setBlockId($x, 127, $z, 2);
            }
        }
        $chunk->setGenerated();
    }


    /**
     * Populates the chunk with planets
     *
     * @param int $chunkX
     * @param int $chunkZ
     * @return void
     */
    public function populateChunk(int $chunkX, int $chunkZ) : void
    {
        //var_dump("Чанк сгенерирован");
        $this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());
        $chunk = $this->level->getChunk($chunkX, $chunkZ);
        $centerOfChunk = new Vector3($chunkX * 16 - 8, 128, $chunkZ * 16 - 8);

        
    }

    /**
     * Returns the dafault spawn
     *
     * @return void
     */
    public function getSpawn(): Vector3
    {
        return new Vector3(264, 255, 264);
    }

}
