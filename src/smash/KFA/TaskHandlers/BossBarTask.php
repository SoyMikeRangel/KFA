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


use pocketmine\scheduler\Task;
use pocketmine\Server;
use smash\KFA\BossBar\BossBar;
use smash\KFA\Database\DataManager;

class BossBarTask extends Task
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
		$bar = new BossBar();
		$players = Server::getInstance()->getOnlinePlayers();
		if (count($players) > 0) {
			foreach ($players as $player) {
				if ($player->getLevel()->getFolderName() != DataManager::getArena()) {
					$bar->hideFrom([$player]);
					$bar->sendRemoveBossPacket([$player]);
					$bar->removePlayer($player);
				}
			}
		}
	}
}