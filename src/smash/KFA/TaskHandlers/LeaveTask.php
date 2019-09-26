<?php
/*
 *
 * | | / /|  ___/ _ \
 * | |/ / | |_ / /_\ \
 * |    \ |  _||  _  |
 * | |\  \| |  | | | |
 * \_| \_/\_|  \_| |_/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMineSmash
 * @link https://github.com/PocketmineSmashPE/KFA
 *
 *
*/
declare(strict_types=1);

namespace smash\KFA\TaskHandlers;


use smash\KFA\Database\DataManager;
use smash\KFA\PluginUtils\PluginUtils;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class LeaveTask extends Task
{
	private $player;

	public function __construct(Player $player)
	{
		$this->player = $player;
	}

	/**
	 * Actions to execute when run
	 *
	 * @param int $currentTick
	 *
	 * @return void
	 */
	public function onRun(int $currentTick)
	{
		if ($this->player->getLevel()->getName() == DataManager::getArena()) {
			PluginUtils::sendSucessMessage("Leaving FFA, please wait...", $this->player);
			$this->player->getInventory()->clearAll();
			$this->player->setGamemode(0);
			$this->player->setFood(20.0);
			$this->player->setHealth(20.0);
			$this->player->removeAllEffects();
			$this->player->getArmorInventory()->clearAll();
			$hub = Server::getInstance()->getDefaultLevel()->getSafeSpawn();
			$this->player->teleport($hub);
		} else {
			PluginUtils::sendErrorMessage("You are not in FFA", $this->player);
		}
	}
}