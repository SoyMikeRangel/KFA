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
 * @link http://www.pocketmine.net/
 *
 *
*/
declare(strict_types=1);

namespace KFA\TaskHandlers;


use KFA\Database\DataManager;
use KFA\KFA;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class RespawnTask extends Task
{
	private $player;
	private $seconds = 3;

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
			$this->player->setGamemode(3);
			$this->player->addTitle("§4".$this->seconds, "§7...Resurrecting...");
			$this->player->sendTip("§4You died!");
		}
		if ($this->seconds == 0) {
			KFA::getInstance()->getScheduler()->cancelTask($this->getTaskId());
			$this->player->teleport(DataManager::getRandomSpawn());
			$this->player->setGamemode(0);
			$this->player->setHealth(20);
			$this->player->setFood(20);
			$this->player->removeAllEffects();
			KFA::getInstance()->getScheduler()->scheduleTask(new KitTask($this->player));
		}
		$this->seconds--;
	}

}