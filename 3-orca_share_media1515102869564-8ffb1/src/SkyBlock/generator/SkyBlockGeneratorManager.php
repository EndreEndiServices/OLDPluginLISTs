<?php

namespace SkyBlock\generator;

use pocketmine\level\generator\Generator;
use SkyBlock\generator\generators\BasicIsland;
use SkyBlock\SkyBlock;

class SkyBlockGeneratorManager {

    /** @var SkyBlock */
    private $plugin;

    /** @var SkyBlockGenerator[] */
    private $generators = [];

    /**
     * SkyBlockGeneratorManager constructor.
     *
     * @param SkyBlock $plugin
     */
    public function __construct(SkyBlock $plugin) {
        $this->plugin = $plugin;
        $this->registerGenerator(BasicIsland::class, "basic", "Basic Island");
        Generator::addGenerator(BasicIsland::class, "basicgen");
    }

    /**
     * Return if a generator exists
     *
     * @param $name
     * @return bool
     */
    public function isGenerator($name) {
        return isset($this->generators[$name]);
    }

    /**
     * Return skyblock generators
     *
     * @return SkyBlockGenerator[]
     */
    public function getGenerators() {
        return $this->generators;
    }

    public function getGeneratorIslandName($name) {
        return $this->isGenerator($name) ? $this->generators[$name] : "";
    }

    /**
     * Register a generator
     *
     * @param $generator
     * @param string $name
     * @param string $islandName
     */
    public function registerGenerator($generator, $name, $islandName) {
        Generator::addGenerator($generator, $name);
        $this->generators[$name] = $islandName;
    }

}