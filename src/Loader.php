<?php

namespace dhnnz\BountyHunters;

use pocketmine\plugin\PluginBase;

class Loader extends PluginBase{

    /** @var Loader $instance */
    public static $instance;

    public function onLoad(): void
    {
        self::$instance = $this;
    }

    public function onEnable(): void{

    }

    public static function getInstance(): self
    {
        return self::$instance;
    }
}