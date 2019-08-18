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

namespace KFA;

use KFA\Commands\KFACommand;
use KFA\Database\Connection;
use KFA\Database\DataManager;
use KFA\Entities\JoinEntity;
use KFA\Entities\Leaderboard;
use KFA\EventListeners\EventHandler;
use KFA\PluginUtils\PluginUtils;
use KFA\TaskHandlers\UpdateLeaderBoard;
use KFA\TaskHandlers\UpdateNPC;
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
		$this->verifyArenaStatus();
		$this->getServer()->getCommandMap()->register('ffa', new KFACommand());
		$this->getServer()->getPluginManager()->registerEvents(new EventHandler($this), $this);
		$this->getScheduler()->scheduleRepeatingTask(new UpdateNPC(), 20);
		$this->getScheduler()->scheduleRepeatingTask(new UpdateLeaderBoard(), 160);
		Entity::registerEntity(JoinEntity::class, true);
		Entity::registerEntity(Leaderboard::class, true);
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
		$resources = ['database.sq3', 'settings.yml'];
		foreach ($resources as $resource) {
			$this->saveResource($resource);
		}
	}

	/**
	 * Verify if arena is enabled
	 */
	private function verifyArenaStatus()
	{
		if (DataManager::getArenaStatus() == 'true') {
			PluginUtils::sendSucessLog("Arena enabled");
			$this->getServer()->loadLevel(DataManager::getArena());
		} else {
			PluginUtils::sendWarningLog("Your arena is not configured, please configure arena before playing!");
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