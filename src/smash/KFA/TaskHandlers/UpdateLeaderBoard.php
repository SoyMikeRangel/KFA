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
use smash\KFA\Entities\Leaderboard;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class UpdateLeaderBoard extends Task
{

	/**
	 * Actions to execute when run
	 *
	 * @param int $currentTick
	 *
	 * @return void
	 */
	public function onRun(int $currentTick)
	{
		$level = Server::getInstance()->getDefaultLevel();
		foreach ($level->getEntities() as $entity) {
			if ($entity instanceof Leaderboard) {
				$entity->setNameTagAlwaysVisible(true);
				$entity->setImmobile(true);
				$entity->setScale(1);
				$entity->setNameTag(DataManager::getTops());
			}
		}
	}
}