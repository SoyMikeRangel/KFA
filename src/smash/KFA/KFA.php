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

namespace smash\KFA;

use smash\KFA\Commands\KFACommand;
use smash\KFA\Database\Connection;
use smash\KFA\Database\DataManager;
use smash\KFA\Entities\JoinEntity;
use smash\KFA\Entities\Leaderboard;
use smash\KFA\EventListeners\EventHandler;
use smash\KFA\PluginUtils\PluginUtils;
use smash\KFA\TaskHandlers\BossBarTask;
use smash\KFA\TaskHandlers\UpdateLeaderBoard;
use smash\KFA\TaskHandlers\UpdateNPC;
use pocketmine\entity\Entity;
use pocketmine\plugin\PluginBase;

class KFA extends PluginBase
{
	private static $instance;
	public $settings;

	/**
	 * onEnable function
	 */
	public function onEnable()
	{
		self::$instance = $this;
		$this->savePluginResources();
		$this->verifyConnection();
		if ($this->verifyArenaStatus()) {
			$this->getServer()->loadLevel(DataManager::getSettings()->get('arena'));
		}
		$this->getServer()->getCommandMap()->register('ffa', new KFACommand());
		$this->getServer()->getPluginManager()->registerEvents(new EventHandler($this), $this);
		$this->getScheduler()->scheduleRepeatingTask(new UpdateNPC(), 20);
		$this->getScheduler()->scheduleRepeatingTask(new UpdateLeaderBoard(), 160);
		Entity::registerEntity(JoinEntity::class, true);
		Entity::registerEntity(Leaderboard::class, true);
		$this->getScheduler()->scheduleRepeatingTask(new BossBarTask(), 20);
	}


	/**
	 * @return KFA
	 */
	public static function getInstance(): KFA
	{
		return self::$instance;
	}


	/**
	 * @return bool
	 * Verificar si la conexion a la base de datos es exitosa
	 * Verify if database connection is successful
	 */
	public function verifyConnection(): bool
	{
		if (file_exists($this->getDataFolder() . DIRECTORY_SEPARATOR . "database.sq3")) {
			if (!$db = new Connection($this)) {
				PluginUtils::sendErrorLog($db->lastErrorMsg());
				PluginUtils::sendErrorLog("Error getting database connection! disabling plugin...");
				$this->getServer()->getPluginManager()->disablePlugin($this);
				return false;
			} else {
				PluginUtils::sendSucessLog("The connection to the database is successful!");
				return true;
			}
		} else {
			PluginUtils::sendErrorLog("There's no database file!");
			PluginUtils::sendErrorLog("Error getting database connection! disabling plugin...");
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return false;
		}
	}


	/**
	 * Save files
	 */
	private function savePluginResources()
	{
		$resources = ['database.sq3', 'settings.yml', 'playing.yml'];
		foreach ($resources as $resource) {
			$this->saveResource($resource);
		}
	}

	/**
	 * Verify if arena is enabled
	 * @return bool
	 */
	private function verifyArenaStatus(): bool
	{
		if (DataManager::getArenaStatus() == 'true') {
			PluginUtils::sendSucessLog("Arena enabled");
			return true;
		} else {
			PluginUtils::sendWarningLog("Your arena is not configured, please configure arena before playing!");
			return false;
		}
	}


	/**
	 * General onDisable actions
	 */
	public function onDisable()
	{
		PluginUtils::sendSucessLog("Database disconnected!");
		PluginUtils::sendSucessLog("Resources saved!");
		Connection::closeDatabase();
	}
}