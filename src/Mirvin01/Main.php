<?php

declare(strict_types=1);

namespace Mirvin01;

use LootTable;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

use Mirvin01\GameListeners;
use pocketmine\block\Planks;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\player\Player;
use pocketmine\scheduler\TaskHandler;
use pocketmine\world\World;

class Main extends PluginBase implements Listener{

    private Config $config;

    private array $games = [];

    private TaskHandler $progressTask;

    public function onEnable(): void{
        $this->getLogger()->info(TextFormat::GREEN. "on");

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getPluginManager()->registerEvents(new LootGenerator(), $this);

        $this->config = new Config($this->getDataFolder(). "config.json", Config::JSON);

        array_push($this->games, $this->createGame("swmap_copy"));
    }

    public function onDisable(): void{
        $this->getLogger()->info(TextFormat::DARK_RED, "off");
    }

    public function onJoin(PlayerJoinEvent $event): void{
       foreach($this->games as $game){
            if(!$game->isFullPlayers()){
                $game->addPlayer($event->getPlayer());
                return;
            }
       }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
        switch($args[0]){
            case "addspawn":

                if($sender instanceof Player){
                    $sender->sendMessage("Точка установленна");
                    $spawnPosition = $this->config->get($sender->getWorld()->getFolderName());


                    $spawnPosition["spawnPosition"][] = [(int) $sender->getPosition()->getX(), (int) $sender->getPosition()->getY(), (int) $sender->getPosition()->getZ()];

                    $this->config->set($sender->getWorld()->getFolderName(), $spawnPosition);
                    $this->config->save();
                }
                
                break;
            case "addchest":

                if($sender instanceof Player){
                    $sender->sendMessage("Точка установленна");
                    $spawnPosition = $this->config->get($sender->getWorld()->getFolderName());


                    $spawnPosition["chests"][] = [(int) $sender->getPosition()->getX(), (int) $sender->getPosition()->getY(), (int) $sender->getPosition()->getZ()];

                    $this->config->set($sender->getWorld()->getFolderName(), $spawnPosition);
                    $this->config->save();
                }
                break;
            case "primeChests":

                if($sender instanceof Player){
                    $sender->sendMessage("Точка установленна");
                    $spawnPosition = $this->config->get($sender->getWorld()->getFolderName());
    
    
                    $spawnPosition["primeChests"][] = [(int) $sender->getPosition()->getX(), (int) $sender->getPosition()->getY(), (int) $sender->getPosition()->getZ()];
    
                    $this->config->set($sender->getWorld()->getFolderName(), $spawnPosition);
                    $this->config->save();
                }
            break;
            case "reload":
                $this->games[0]->reload();
            break;
        }  
        return true;
    }

    public function createGame(string $worldName): Skywars{
        $this->getServer()->getWorldManager()->loadWorld($worldName);
        return new Skywars($this, $this->getServer()->getWorldManager()->getWorldByName($worldName));
    }

    public function getConfig(): Config{
        return $this->config;
    }
}