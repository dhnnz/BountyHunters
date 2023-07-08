<?php

namespace dhnnz\BountyHunters\economy;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI as ApiBedrockEconomyAPI;
use cooldogedev\BedrockEconomy\api\legacy\ClosureContext;
use pocketmine\player\Player;

class BedrockEconomyAPI
{

    public function addMoney(Player $player, $amount, callable $callable)
    {
        ApiBedrockEconomyAPI::legacy()->addToPlayerBalance(
            $player->getName(),
            $amount,
            ClosureContext::create(
                function (bool $wasUpdated) use ($callable): void {
                    $callable($wasUpdated);
                }
            )
        );
    }

    public function reduceMoney(Player $player, $amount, callable $callable)
    {
        ApiBedrockEconomyAPI::legacy()->subtractFromPlayerBalance(
            $player->getName(),
            $amount,
            ClosureContext::create(
                function (bool $wasUpdated) use ($callable): void {
                    $callable($wasUpdated);
                },
            )
        );
    }
}