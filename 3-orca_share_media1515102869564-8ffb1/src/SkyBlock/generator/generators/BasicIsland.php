<?php

namespace SkyBlock\generator\generators;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\object\Tree;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use SkyBlock\generator\SkyBlockGenerator;

class BasicIsland extends SkyBlockGenerator {

    /** @var array */
    private $settings;

    /** @var string */
    private $name;

    /** @var ChunkManager */
    private $level;

    /** @var Random */
    private $random;

    /**
     * BasicIsland constructor.
     *
     * @param array $settings
     */
    public function __construct(array $settings = []) {
        $this->settings = $settings;
    }

    /**
     * Initialize BasicIsland
     *
     * @param ChunkManager $level
     * @param Random $random
     */
    public function init(ChunkManager $level, Random $random) {
        $this->level = $level;
        $this->random = $random;
        $this->name = "basic";
        $this->islandName = "Basic Island";
    }

    /**
     * Return generator name
     *
     * @return string
     */
    public function getName() : string {
        return $this->name;
    }

    public function getSettings() : array {
        return $this->settings;
    }

    public function generateChunk(int $chunkX, int $chunkZ) {
        $chunk = $this->level->getChunk($chunkX, $chunkZ);
        $chunk->setGenerated();
        if ($chunkX % 20 == 0 && $chunkZ % 20 == 0) {
            for ($x = 0; $x < 16; $x++) {
                for ($z = 0; $z < 16; $z++) {
                    $chunk->setBlock($x, 0, $z, Block::BEDROCK);
                    for ($y = 1; $y <= 3; $y++) {
                        $chunk->setBlock($x, $y, $z, Block::STONE);
                    }
                    $chunk->setBlock($x, 4, $z, Block::DIRT);
                    $chunk->setBlock($x, 5, $z, Block::GRASS);
                }
                Tree::growTree($this->level, $chunkX * 16 + 8, 6, $chunkZ * 16 + 8, $this->random, 0);
            }
            $chunk->setX($chunkX);
            $chunk->setZ($chunkZ);
            $this->level->setChunk($chunkX, $chunkZ, $chunk);
        }
    }

    public function populateChunk(int $chunkX, int $chunkZ) {
        // this disappeared in API 3.0.0-ALPHA1 due to client changes (client doesn't support custom biome colours anymore)
        /*for ($x = 0; $x < 16; $x++) {
            for ($z = 0; $z < 16; $z++) {
                $this->level->getChunk($chunkX, $chunkZ)->setBiomeColor($x, $z, 133, 188, 86);
            }
        }*/
    }

    /**
     * Return BasicIsland spawn
     *
     * @return Vector3
     */
    public function getSpawn() : Vector3 {
        return new Vector3(8, 7, 10);
    }

}