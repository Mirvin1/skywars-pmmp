<?php

namespace Mirvin01;

use pocketmine\scheduler\Task;
use pocketmine\world\World;

class MapTask extends Task{

    private World $world;

    private string $loadOrUnload;

    public function __construct(World $world, string $loadOrUnload){
        $this->world = $world;
        $this->loadOrUnload = $loadOrUnload;
    }
  
    public function onRun(): void{
        if($this->loadOrUnload === "load"){
            $this->world->getServer()->getWorldManager()->loadWorld($this->world->getFolderName());
        }
        if($this->loadOrUnload === "unload"){
            $this->world->getServer()->getWorldManager()->unloadWorld($this->world);
        }
        $this->getHandler()->cancel();
    }
}