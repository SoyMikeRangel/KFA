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

namespace KFA\Commands;


use KFA\Database\Connection;
use KFA\Database\DataManager;
use KFA\Entities\EntityManager;
use KFA\Entities\JoinEntity;
use KFA\Entities\Leaderboard;
use KFA\KFA;
use KFA\PluginUtils\PluginUtils;
use KFA\TaskHandlers\KitTask;
use KFA\TaskHandlers\LeaveTask;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\command\utils\CommandException;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;


class KFACommand extends Command implements PluginIdentifiableCommand
{

	private $manager;

	public function __construct()
	{
		$this->manager = new DataManager();
		parent::__construct('ffa', '§aKFA Plugin Command', '§cUse: </ffa help>', ['ffa', 'kfa']);
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param string[] $args
	 *
	 * @return mixed
	 * @throws CommandException
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if ($sender instanceof Player) {
			if (isset($args[0])) {
				switch ($args[0]) {
					case 'remnpc':
						if ($sender->isOp()) {
							$npc = Server::getInstance()->getDefaultLevel()->getEntities();
							foreach ($npc as $entity) {
								if ($entity instanceof JoinEntity || $entity instanceof Leaderboard) {
									$entity->close();
								}
							}
						} else {
							PluginUtils::sendErrorMessage("You cant use this command!", $sender->getPlayer());
						}
						break;
					case 'npc':
						if ($sender->isOp()) {
							$npc = new EntityManager();
							$npc->setJoinEntity($sender->getPlayer());
							PluginUtils::sendSucessMessage("NPC Configured!", $sender->getPlayer());
						} else {
							PluginUtils::sendErrorMessage("You cant use this command!", $sender->getPlayer());
						}
						break;
					case 'arena':
						if ($sender->isOp()) {
							if (isset($args[1])) {
								DataManager::setArena($sender->getPlayer(), $args[1]);
								PluginUtils::sendSucessMessage("Arena level configured!", $sender->getPlayer());
							} else {
								PluginUtils::sendErrorMessage("Use: </ffa arena {arenaName}>", $sender->getPlayer());
							}
						} else {
							PluginUtils::sendErrorMessage("You cant use this command!", $sender->getPlayer());
						}
						break;
					case 'join':
						if ($sender->getLevel()->getName() == DataManager::getArena()) {
							PluginUtils::sendErrorMessage("You are already in FFA!", $sender->getPlayer());
						} elseif (DataManager::getArenaStatus() == false) {
							PluginUtils::sendErrorMessage("The arena is disabled!", $sender->getPlayer());
						} else {
							$connection = new Connection(KFA::getInstance());
							$sql = $connection->getDatabase()->prepare("INSERT OR IGNORE INTO Players(NAME,KILLS,DEATHS,KDR) SELECT :name, :kills, :deaths, :kdr WHERE NOT EXISTS(SELECT * FROM Players WHERE NAME = :name);");
							$sql->bindValue(":name", $sender->getName(), SQLITE3_TEXT);
							$sql->bindValue(":kills", 0, SQLITE3_NUM);
							$sql->bindValue(":deaths", 0, SQLITE3_NUM);
							$sql->bindValue(":kdr", 1, SQLITE3_FLOAT);
							$sql->execute();
							$sender->teleport(DataManager::getRandomSpawn());
							KFA::getInstance()->getScheduler()->scheduleTask(new KitTask($sender->getPlayer()));
							$sender->addTitle("§c☣FFA☣", "§7> Be the last one!", 20, 20, 20);
						}
						break;
					case 'setup':
						if ($sender->isOp()) {
							if (isset($args[1])) {
								switch ($args[1]) {
									case 'spawn1':
										DataManager::setSpawn1($sender->getPlayer());
										PluginUtils::sendSucessMessage("Spawn §91§a configured!", $sender->getPlayer());
										break;
									case 'spawn2':
										DataManager::setSpawn2($sender->getPlayer());
										PluginUtils::sendSucessMessage("Spawn §92§a configured!", $sender->getPlayer());
										break;
									case 'spawn3':
										DataManager::setSpawn3($sender->getPlayer());
										PluginUtils::sendSucessMessage("Spawn §93§a configured!", $sender->getPlayer());
										break;
									case 'enable':
										DataManager::setArenaStatus();
										PluginUtils::sendSucessMessage("Arena enabled!", $sender->getPlayer());
										break;
								}
							} else {
								PluginUtils::sendErrorMessage('use: </ffa setup spawn1|2|3|enable>', $sender->getPlayer());
							}
						} else {
							PluginUtils::sendErrorMessage("You cant use this command!", $sender->getPlayer());
						}
						break;
					case 'leave':
						if ($sender->getLevel()->getName() == DataManager::getArena()) {
							KFA::getInstance()->getScheduler()->scheduleTask(new LeaveTask($sender->getPlayer()));
						}
						break;
					case 'help':
						if ($sender->isOp()) {
							$sender->sendMessage(PluginUtils::OP_HELP);
						} else {
							$sender->sendMessage(PluginUtils::HELP);
						}
						break;
					case 'score':
						if (DataManager::verifyPlayerInDB($sender->getName()) == 'true') {
							$sender->sendMessage($this->manager->getScore($sender->getPlayer()->getName()));
						} else {
							PluginUtils::sendErrorMessage('You have not played FFA, join now!', $sender->getPlayer());
						}
						break;
					case 'tops':
						if ($sender->isOp()) {
							$npc = new EntityManager();
							$npc->setLeaderboardEntity($sender->getPlayer());
						} else {
							PluginUtils::sendErrorMessage("You cant use this command!", $sender->getPlayer());
						}
						break;
					default:
						PluginUtils::sendErrorMessage('use: </ffa help>', $sender->getPlayer());
						break;
				}
			} else {
				PluginUtils::sendErrorMessage('use: </ffa help>', $sender->getPlayer());
			}
		} else {
			PluginUtils::sendErrorLog("Please use this command in game!");
		}
		return true;
	}

	/**
	 * @return Plugin
	 */
	public function getPlugin(): Plugin
	{
		return KFA::getInstance();
	}
}