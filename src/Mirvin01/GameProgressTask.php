<?php

declare(strict_types=1);

namespace Mirvin01;

use Mirvin01\Skywars;
use pocketmine\player\GameMode;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\ClickSound;
use pocketmine\world\sound\GhastSound;

class GameProgressTask extends Task{

    private const COUNT_TIME = 5;
    
    private $counter = self::COUNT_TIME;
    
    private $game;


    public function __construct(Skywars $game){
        $this->game = $game;
    }

    public function onRun(): void{
        switch($this->game->gameStatus){
            case GameStatus::WAITING:
                if($this->game->getNumberPlayers() >= Skywars::MIN_PLAYERS){ // изменить на >
                    if($this->game->getNumberPlayers() === 5 and $this->counter > 10){
                        $this->counter = 10;
                    }

                    if($this->counter === 0){
                        $this->game->gameStatus = GameStatus::STARTED;
                        LootGenerator::getInstance()->runGen($this->game->getWorld(), $this->game->chests);
                        $this->game->sendTitleToPlayers(TextFormat::BOLD.TextFormat::GREEN. "GOOOOOL!", "");
                        $this->game->sendSoundToPlayers(new GhastSound());
                        $this->game->setPlayerSurvival();
                        $this->counter = self::COUNT_TIME;
                        return;
                    }
            
                    if($this->counter > 10){
                        $this->game->sendTitleToPlayers(TextFormat::YELLOW. "$this->counter");
                    } else {
                        $this->game->sendTitleToPlayers(TextFormat::RED. "$this->counter");
                    }
                    $this->game->sendSoundToPlayers(new ClickSound());
                    $this->counter--;
                    $this->game->sendTipToPlayers(TextFormat::GREEN. "Игроков ".$this->game->getNumberPlayers()." из ".Skywars::MAX_PLAYERS);
                } else {
                    $this->game->sendTipToPlayers(TextFormat::RED. "Недостаточно игрков!");
                    $this->counter = 15;
                }
                break;
            case GameStatus::STARTED:
                $this->game->checkWin();
                break;
            case GameStatus::FINAL:

                break;
        }
    }
}