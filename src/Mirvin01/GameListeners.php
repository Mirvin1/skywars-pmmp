<?php

declare(strict_types=1);

namespace Mirvin01;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\inventory\Inventory;
use pocketmine\math\Vector3;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class GameListeners implements Listener{

    private $game;

    public function __construct(Main $main, Skywars $game){
        $this->game = $game;
    }

    public function onQuit(PlayerQuitEvent $event): void{
        $player = $event->getPlayer();
        $this->game->removePlayer($player);
    }

    public function onDamage(EntityDamageEvent $event): void{
        $player = $event->getEntity();
        if($player instanceof Player){
            if($player->getGamemode() !== GameMode::ADVENTURE()) return;

            if($event->getFinalDamage() >= $player->getHealth()){
                $this->game->killAndSpectator($player);
                $event->cancel();
            }
        }

        if($this->game->gameStatus === GameStatus::WAITING || $this->game->gameStatus === GameStatus::FINAL){
            $event->cancel();
        }
    }
    
    public function onInteract(PlayerInteractEvent $event): void{
        $player = $event->getPlayer();
        if($player->getGamemode() !== GameMode::ADVENTURE()) return;

        if($this->game->gameStatus === GameStatus::WAITING || $this->game->gameStatus === GameStatus::FINAL){
            $event->cancel();
        }
    }

    public function onInv(InventoryTransactionEvent $event): void{
        $player = $event->getTransaction()->getSource();
        if($player->getGamemode() !== GameMode::ADVENTURE()) return;

        if($this->game->gameStatus === GameStatus::WAITING || $this->game->gameStatus === GameStatus::FINAL){
            $event->cancel();
        }
    }

    public function onMove(PlayerMoveEvent $event){
        $player = $event->getPlayer();
        
        if($player->getPosition()->getY() < 0){
            $this->game->KillAndSpectator($player);
            $player->teleport(new Vector3($player->getPosition()->getX(), 120, $player->getPosition()->getZ()));
        }
        
        if($player->getGamemode() !== GameMode::ADVENTURE()) return;

        if($this->game->gameStatus === GameStatus::WAITING || $this->game->gameStatus === GameStatus::FINAL){
            if($event->getFrom()->getX() !== $event->getTo()->getX() || $event->getFrom()->getZ() !== $event->getTo()->getZ()){
                $event->cancel();
            }
        }
    }
}
