<?php

namespace dhnnz\BountyHunters;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Loader extends PluginBase
{

    /** @var Loader $instance */
    public static $instance;

    public function onLoad(): void
    {
        self::$instance = $this;
    }

    public Config $bountyConfig;

    public function onEnable(): void
    {
        $this->saveDefaultConfig();
        $this->bountyConfig = new Config($this->getDataFolder() . "bounty.json");
    }

    public function getMessage(string $message, array $args = []): string
    {
        $replace = $this->getConfig()->get($message, $message);

        for ($i = 0; $i < count($args); $i++) {
            $replace = str_replace("%$i%", $args[$i], $replace);
        }

        $replace = TextFormat::colorize($replace);
        return $replace;
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        $bountyArray = $this->bountyConfig->getAll();

        switch ($command->getName()) {
            case "topbounties":
                arsort($bountyArray);

                $page = isset($args[0]) ? is_numeric($args[0]) ? intval($args[0]) : 1 : 1;
                $perPage = 5;
                $offset = ($page - 1) * $perPage;

                $message = "";
                $top = 1;

                foreach (array_slice($bountyArray, $offset, $perPage) as $name => $data) {
                    $message .= $this->getMessage("top.message", [$top, $name, number_format($data["moneyPlace"])]);
                    $top++;
                }

                $sender->sendMessage($this->getMessage("top.head.message", [$page]));
                $sender->sendMessage($message);
                break;
            case "placebounty":
                if (!($sender instanceof Player))
                    return false;

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

                $this->bountyConfig->setAll($bountyArray);
                $this->bountyConfig->save();
                break;
            case "bounties":
                if (!isset($args[0])) {
                    $sender->sendMessage(TextFormat::RED . "Usage: /bounties [playerName]");
                    return false;
                }

                $playerPlace = $this->getServer()->getPlayerExact($args[0]);

                if (!($playerPlace instanceof Player)) {
                    $sender->sendMessage(TextFormat::RED . "Unknown player");
                    return false;
                }

                $moneyPlace = isset($bountyArray[$playerPlace->getName()]["moneyPlace"]) ? $bountyArray[$playerPlace->getName()]["moneyPlace"] : 0;

                $sender->sendMessage($this->getMessage("sender.bounties.message", [number_format((float) $moneyPlace), $playerPlace->getName()]));
                break;
            case "mybounties":
                if (!($sender instanceof Player))
                    return false;

                $moneyPlace = isset($bountyArray[$sender->getName()]["moneyPlace"]) ? $bountyArray[$sender->getName()]["moneyPlace"] : 0;

                $sender->sendMessage($this->getMessage("sender.mybounties.message", [number_format((float) $moneyPlace), $sender->getName()]));
                break;
        }
        return true;
    }

    public static function getInstance(): self
    {
        return self::$instance;
    }
}