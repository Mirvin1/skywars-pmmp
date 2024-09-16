<?php

declare(strict_types=1);

namespace Mirvin01;

use pocketmine\block\tile\Chest;
use pocketmine\event\Listener;
use pocketmine\item\StringToItemParser;
use pocketmine\world\World;

class LootGenerator implements Listener{

    public const MAX_SLOT_SIZE = 19;

    private static LootGenerator $instance;

    public $primeLoot = [
        "stone",
        "golden_sword",
        "diamond_sword",
        "oak_planks",
        "oak_log",
        "bricks"
    ];

    public $cammonLoot = [

    ];

    public function __construct(){
        self::$instance = $this;
    }

    public static function getInstance(): LootGenerator{
        return self::$instance;
    }

    public function runGen(World $world, array $chestPositions): void{
        foreach($chestPositions as $cord){
            $chestTile = $world->getTileAt($cord[0], $cord[1], $cord[2]);
            if($chestTile instanceof Chest){
                for ($i = 0; $i < 10; $i++) {
                    $item = StringToItemParser::getInstance()->parse($this->primeLoot[array_rand($this->primeLoot)]);
                    $chestTile->getInventory()->setItem($i, $item->setCount(random_int(0, $item->getMaxStackSize())));
                }
            }
        }
    }

}
