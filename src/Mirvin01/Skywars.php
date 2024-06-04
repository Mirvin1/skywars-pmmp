<?php

declare(strict_types=1);

namespace Mirvin01;

use Mirvin01\MapTask;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\sound\Sound;
use pocketmine\world\World;

class Skywars{

    public const MAX_PLAYERS = 9;

    public const MIN_PLAYERS = 2;

    private Main $main;

    private World $world;

    private Config $config;

    private TaskHandler $progressTask;

    private $players = [];

    private $spawnPosition;

    public $chests;

    public $primeChests;

    private $observers = [];

    public GameStatus $gameStatus = GameStatus::WAITING;

    public function __construct(Main $main, World $world){
        $this->main = $main;
        $this->world = $world; $this->world->setAutoSave(false);
        $this->config = $this->main->getConfig();
        $this->spawnPosition = $this->config->get($this->world->getFolderName())["spawnPosition"];
        $this->chests = $this->config->get($this->world->getFolderName())["chests"];
        $this->primeChests = $this->config->get($this->world->getFolderName())["primeChests"];
        $this->progressTask = $this->main->getScheduler()->scheduleRepeatingTask(new GameProgressTask($this), 20);
        $this->main->getServer()->getPluginManager()->registerEvents(new GameListeners($this->main, $this), $this->main);
    }

    public function  getWorld(): World{
        return $this->world;
    }

    public function addPlayer(Player $player): void{
        $player->setGamemode(GameMode::ADVENTURE());
        array_push($this->players, $player);
        $player->teleport(new Position($this->spawnPosition[$this->getNumberPlayers() - 1][0], $this->spawnPosition[$this->getNumberPlayers() - 1][1], $this->spawnPosition[$this->getNumberPlayers() - 1][2], $this->main->getServer()->getWorldManager()->getWorldByName("swmap_copy")));
    }

    public function removePlayer(Player $player): void{
        unset($this->players[array_search($player, $this->players)]);
    }

    public function getPlayers(): array{
        $this->main->getServer()->getWorldManager()->loadWorld($this->world->getFolderName());
        return $this->players;
    }

    public function getNumberPlayers(): int{
        return count($this->players);
    }

    public function isFullPlayers(): bool{
        if($this->getNumberPlayers() >= self::MAX_PLAYERS){
            return true;
        } else {
            return false;
        }
    }

    public function sendTitleToPlayers(string $title, string $subtitle = ""): void{
        foreach ($this->players as $player) {
            if(!$player->isOnline()) return;
            $player->sendTitle($title, $subtitle);
        }
    }

    public function sendTipToPlayers(string $message): void{
        foreach ($this->players as $player) {
            if(!$player->isOnline()) return;
            $player->sendTip($message);
        }
    }

    public function sendMessageToPlayers(string $message): void{
        foreach ($this->players as $player) {
            if(!$player->isOnline()) return;
            $player->sendMessage($message);
        }
    }

    public function sendSoundToPlayers(Sound $sound): void{
        foreach ($this->players as $player) {
            if(!$player->isOnline()) return;
            $player->broadcastSound($sound);
        }
    }

    public function killAndSpectator(Player $player): void{
        $player->setGamemode(GameMode::ADVENTURE());
        $player->setFlying(true);
        $player->setAllowFlight(true);
        $player->setInvisible(true);
        
        $player->setHealth(20);
        $player->getHungerManager()->setFood(20);
        $player->sendTitle(TextFormat::BOLD.TextFormat::RED. "ПОМЕР", "Режим наблюдателя");
        $this->removePlayer($player);
        $player->getInventory()->clearAll();
        $this->checkWin();
    }

    public function setPlayerSurvival(): void{
        foreach ($this->players as $player) {
            if(!$player->isOnline()) return;
            $player->setGamemode(GameMode::SURVIVAL());
        }
    }

    public function giveKit(): void{
        
    }

    public function checkWin(): void{
        if($this->getNumberPlayers() < self::MIN_PLAYERS && $this->gameStatus === GameStatus::STARTED){ // исправить на <
            $winner = reset($this->players);
            // if(!$winner) return;
            // if($winner->isOnline()) return;
            $winner->sendTitle(TextFormat::BOLD.TextFormat::GOLD. "ПОБЕДИЛ", "Поздравляем с победой!");
            $winner->setGamemode(GameMode::SPECTATOR());
            $this->gameStatus = GameStatus::FINAL;
            $this->reload();
        }
    }

    public function reload(){
        // переместить игроков на другой сервер
        $this->main->getScheduler()->scheduleRepeatingTask(new MapTask($this->world, "unload"), 1);
        $this->main->getScheduler()->scheduleRepeatingTask(new MapTask($this->world, "load"), 1);
        
        $this->main->getServer()->getWorldManager()->loadWorld("swmap_copy");
        $this->world = $this->main->getServer()->getWorldManager()->getWorldByName($this->world->getFolderName());
        $this->world->setAutoSave(false);
        $this->gameStatus = GameStatus::WAITING;
    }

    public function getConfig(): Config{
        return $this->config;
    }

}