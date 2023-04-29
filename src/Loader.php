<?php

namespace dhnnz\BountyHunters;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Loader extends PluginBase{

    /** @var Loader $instance */
    public static $instance;

    public function onLoad(): void
    {
        self::$instance = $this;
    }

    public Config $bountyConfig;

    public function onEnable(): void{
        $this->saveDefaultConfig();
        $this->bountyConfig = new Config($this->getDataFolder()."bounty.json");
    }

    public function getMessage($message, array $args = []): string{
        $message = $this->getConfig()->get($message, $message);

        for ($i=0; $i < count($args)-1; $i++) {
            $replace = str_replace("%$i", $args[$i], $message);
        }

        $replace = TextFormat::colorize($replace);
        return $replace;
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        $bountyArray = $this->bountyConfig->getAll();

        if(!($sender instanceof Player)) return false;

        switch($command->getName()){
            case "bounty":
                break;
            case "placebounty":
                if (!isset($args[0]) || !isset($args[1])) {
                    $sender->sendMessage(TextFormat::RED . "Usage: /placebounty [playerName] [money]");
                    return false;
                }

                $playerPlace = $this->getServer()->getPlayerExact($args[0]);
                $moneyPlace = intval($args[1]);

                if (!($playerPlace instanceof Player)) {
                    $sender->sendMessage(TextFormat::RED . "Unknown player");
                    return false;
                }

                if (!is_numeric($args[1])) {
                    $sender->sendMessage(TextFormat::RED . "Money must be an integer");
                    return false;
                }

                if (isset($bountyArray[$playerPlace->getName()])) {
                    $bountyArray[$playerPlace->getName()]["moneyPlace"] += $moneyPlace;
                } else {
                    $bountyArray[$playerPlace->getName()]["moneyPlace"] = $moneyPlace;
                }
                Server::getInstance()->broadcastMessage($this->getMessage("broadcast.placebounty.message", [number_format((float) $moneyPlace), $sender->getName(), $playerPlace->getName()]));
                $sender->sendMessage($this->getMessage("sender.placebounty.message", [number_format((float) $moneyPlace), $sender->getName(), $playerPlace->getName()]));
                break;
        }
        return true;
    }

    public static function getInstance(): self
    {
        return self::$instance;
    }
}