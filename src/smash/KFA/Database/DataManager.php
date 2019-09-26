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

namespace smash\KFA\Database;


use smash\KFA\KFA;
use smash\KFA\PluginUtils\PluginUtils;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;

class DataManager
{
	private static $arena;
	public static $leaderboard = [];

	/**
	 * @return bool|mixed
	 */

	public static function getArena()
	{
		self::$arena = new Config(KFA::getInstance()->getDataFolder() . "settings.yml", Config::YAML);
		return self::$arena->get("arena");
	}

	/**
	 * @param Player $player
	 * @param string $arena
	 */
	public static function setArena(Player $player, string $arena)
	{
		if (Server::getInstance()->isLevelGenerated($arena)) {
			self::$arena = new Config(KFA::getInstance()->getDataFolder() . "settings.yml", Config::YAML);
			self::$arena->set('arena', $arena);
			self::$arena->save();
		} else {
			PluginUtils::sendErrorMessage("This level does not exists!", $player);
		}
	}

	/**
	 * @param Player $player
	 */
	public static function setSpawn1(Player $player)
	{
		self::$arena = new Config(KFA::getInstance()->getDataFolder() . "settings.yml", Config::YAML);
		$x = (float)$player->x;
		$y = (float)$player->y;
		$z = (float)$player->z;
		self::$arena->set('spawn-1', [$x, $y, $z]);
		self::$arena->save();
	}

	/**
	 * @param Player $player
	 */
	public static function setSpawn2(Player $player)
	{
		self::$arena = new Config(KFA::getInstance()->getDataFolder() . "settings.yml", Config::YAML);
		$x = (float)$player->x;
		$y = (float)$player->y;
		$z = (float)$player->z;
		self::$arena->set('spawn-2', [$x, $y, $z]);
		self::$arena->save();
	}

	/**
	 * @param Player $player
	 */
	public static function setSpawn3(Player $player)
	{
		self::$arena = new Config(KFA::getInstance()->getDataFolder() . "settings.yml", Config::YAML);
		$x = (float)$player->x;
		$y = (float)$player->y;
		$z = (float)$player->z;
		self::$arena->set('spawn-3', [$x, $y, $z]);
		self::$arena->save();
	}

	/**
	 * @return bool
	 */
	public static function getArenaStatus(): bool
	{
		self::$arena = new Config(KFA::getInstance()->getDataFolder() . "settings.yml", Config::YAML);
		$status = self::$arena->get('enabled');
		if ($status == 'true') {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get Arena status
	 */
	public static function setArenaStatus()
	{
		self::$arena = new Config(KFA::getInstance()->getDataFolder() . "settings.yml", Config::YAML);
		self::$arena->set('enabled', 'true');
		self::$arena->save();
	}

	/**
	 * @return bool|mixed
	 * Get number of apples when a player dies
	 */
	public static function getGapples()
	{
		self::$arena = new Config(KFA::getInstance()->getDataFolder() . "settings.yml", Config::YAML);
		return self::$arena->get('gapples');
	}

	public static function getEnchantmentStatus(): bool
	{
		self::$arena = new Config(KFA::getInstance()->getDataFolder() . "settings.yml", Config::YAML);
		$status = self::$arena->get('enchantments');
		if ($status == 'true') {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return mixed
	 */
	public static function getRandomSpawn(): Position
	{
		self::$arena = new Config(KFA::getInstance()->getDataFolder() . "settings.yml", Config::YAML);
		$level = Server::getInstance()->getLevelByName(DataManager::getArena());
		$spawn1 = self::$arena->get('spawn-1');
		$spawn2 = self::$arena->get('spawn-2');
		$spawn3 = self::$arena->get('spawn-3');
		$positions = [new Position($spawn1[0], $spawn1[1], $spawn1[2], $level), new Position($spawn2[0], $spawn2[1], $spawn2[2], $level), new Position($spawn3[0], $spawn3[1], $spawn3[2], $level)];
		return $positions[array_rand($positions)];
	}

	/**
	 * @return int
	 */
	public static function getArenaPlayers(): int
	{
		KFA::getInstance()->getServer()->loadLevel(self::getSettings()->get('arena')); // Load it first
		$level = Server::getInstance()->getLevelByName(self::getSettings()->get('arena')); // then use it
		return count($level->getPlayers());
	}


	/**
	 * @param string $player
	 * @return mixed
	 */
	public function getKills(string $player)
	{
		$connection = new Connection(KFA::getInstance());
		$db = $connection->getDatabase();
		$kills = $db->querySingle("SELECT KILLS FROM Players WHERE NAME = '$player'");
		return $kills;
	}

	/**
	 * @param string $player
	 * @return mixed
	 */
	public function getDeaths(string $player)
	{
		$connection = new Connection(KFA::getInstance());
		$db = $connection->getDatabase();
		$deaths = $db->querySingle("SELECT DEATHS FROM Players WHERE NAME = '$player'");
		return $deaths;
	}

	/**
	 * @param string $player
	 * @return float|int
	 */
	public function getKDR(string $player)
	{
		if ($this->getDeaths($player) == 0) {
			return 0;
		}
		return $this->getKills($player) / $this->getDeaths($player);
	}

	/**
	 * @param string $player
	 */
	public function setKill(string $player)
	{
		$connection = new Connection(KFA::getInstance());
		$db = $connection->getDatabase();
		$kills = $this->getKills($player);
		$result = $kills + 1;
		$db->exec("UPDATE Players SET KILLS='$result' WHERE NAME='$player'");
		$kdr = $this->getKDR($player);
		$db->exec("UPDATE Players SET KDR='$kdr' WHERE NAME='$player'");
	}

	/**
	 * @param string $player
	 */
	public function setDeath(string $player)
	{
		$connection = new Connection(KFA::getInstance());
		$db = $connection->getDatabase();
		$deaths = $this->getDeaths($player);
		$result = $deaths + 1;
		$db->exec("UPDATE Players SET DEATHS='$result' WHERE NAME='$player'");
		$kdr = $this->getKDR($player);
		$db->exec("UPDATE Players SET KDR='$kdr' WHERE NAME='$player'");
	}

	/**
	 * @return Config
	 */
	public static function getSettings(): config
	{
		return new Config(KFA::getInstance()->getDataFolder() . DIRECTORY_SEPARATOR . "settings.yml");
	}

	/**
	 * @param string $player
	 * @return string
	 */
	public function getScore(string $player)
	{
		$kills = $this->getKills($player);
		$deaths = $this->getDeaths($player);
		$kdr = $this->getKDR($player);
		$message = "§7-§4--§7- " . PluginUtils::PREFIX . " §7-§4--§7-\n" .
			"§aPlayer " . "§6: " . "$player\n" .
			"§aKills " . "§6: " . "§7$kills\n" .
			"§aDeaths " . "§6: " . "§7$deaths\n" .
			"§aKill ratio " . "§6: " . "§7$kdr\n" .
			"§4--------------------";
		return $message;
	}

	/**
	 * @param string $player
	 * @return bool
	 */
	public static function verifyPlayerInDB(string $player)
	{
		$connection = new Connection(KFA::getInstance());
		$db = $connection->getDatabase();
		$deaths = $db->querySingle("SELECT NAME FROM Players WHERE NAME = '$player'");
		if ($deaths == null) {
			return 'false';
		} else {
			return 'true';
		}
	}


	/**
	 * Configure leaderboard
	 */

	public static function getTops()
	{
		self::$leaderboard = [];
		$connection = new Connection(KFA::getInstance());
		$db = $connection->getDatabase();
		$sql = "SELECT NAME, KILLS FROM Players ORDER BY KILLS DESC LIMIT 10";
		$result = $db->query($sql);
		$number = 0;
		while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
			self::$leaderboard[$number++] = $row;
		}
		$count = count(self::$leaderboard);
		$break = "\n";
		if ($count > 0) {
			$top1 = "§e1. §6Name: §a" . self::$leaderboard[0]['NAME'] . "  §6Kills: §a" . self::$leaderboard[0]['KILLS'];
		} else {
			$top1 = '';
		}
		if ($count > 1) {
			$top2 = "§e2. §6Name: §e" . self::$leaderboard[1]['NAME'] . "  §6Kills: §e" . self::$leaderboard[1]['KILLS'];
		} else {
			$top2 = '';
		}
		if ($count > 2) {
			$top3 = "§e3. §6Name: §e" . self::$leaderboard[2]['NAME'] . "  §6Kills: §e" . self::$leaderboard[2]['KILLS'];
		} else {
			$top3 = '';
		}
		if ($count > 3) {
			$top4 = "§e4. §6Name: §e" . self::$leaderboard[3]['NAME'] . "  §6Kills: §e" . self::$leaderboard[3]['KILLS'];
		} else {
			$top4 = '';
		}
		if ($count > 4) {
			$top5 = "§e5. §6Name: §e" . self::$leaderboard[4]['NAME'] . "  §6Kills: §e" . self::$leaderboard[4]['KILLS'];
		} else {
			$top5 = '';
		}
		if ($count > 5) {
			$top6 = "§e6. §6Name: §e" . self::$leaderboard[5]['NAME'] . "  §6Kills: §e" . self::$leaderboard[5]['KILLS'];
		} else {
			$top6 = '';
		}
		if ($count > 6) {
			$top7 = "§e7. §6Name: §e" . self::$leaderboard[6]['NAME'] . "  §6Kills: §e" . self::$leaderboard[6]['KILLS'];
		} else {
			$top7 = '';
		}
		if ($count > 7) {
			$top8 = "§e8. §6Name: §e" . self::$leaderboard[7]['NAME'] . "  §6Kills: §e" . self::$leaderboard[7]['KILLS'];
		} else {
			$top8 = '';
		}
		if ($count > 8) {
			$top9 = "§e9. §6Name: §e" . self::$leaderboard[8]['NAME'] . "  §6Kills: §e" . self::$leaderboard[8]['KILLS'];
		} else {
			$top9 = '';
		}
		if ($count > 9) {
			$top10 = "§e10. §6Name: §e" . self::$leaderboard[9]['NAME'] . "  §6Kills: §e" . self::$leaderboard[9]['KILLS'];
		} else {
			$top10 = '';
		}
		return "§4-----☣§cKFA Stats§4☣-----\n" . "§7Top Kills\n" . $top1 . $break . $top2 . $break . $top3 . $break . $top4 . $break . $top5 . $break . $top6 . $break . $top7 . $break . $top8 . $break . $top9 . $break . $top10;
	}

	/**
	 * @return string
	 */
	public static function getTopOne(): string
	{
		self::$leaderboard = [];
		$connection = new Connection(KFA::getInstance());
		$db = $connection->getDatabase();
		$sql = "SELECT NAME, KILLS FROM Players ORDER BY KILLS DESC LIMIT 10";
		$result = $db->query($sql);
		$number = 0;
		while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
			self::$leaderboard[$number++] = $row;
		}
		$count = count(self::$leaderboard);
		if ($count > 0) {
			$top1 = self::$leaderboard[0]['NAME'];
			return $top1;
		}
	}

	/**
	 * @param Player $player
	 * @return bool
	 */
	public static function isPlaying(Player $player): bool
	{
		$config = new Config(KFA::getInstance()->getDataFolder() . "playing.yml", Config::YAML);
		if ($config->get($player->getName()) == true) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @param Player $player
	 */
	public static function setPlaying(Player $player): void
	{
		$config = new Config(KFA::getInstance()->getDataFolder() . "playing.yml", Config::YAML);
		$config->set($player->getName(), true);
		$config->save();
	}

	/**
	 * @param Player $player
	 */
	public static function unsetPlaying(Player $player): void
	{
		$config = new Config(KFA::getInstance()->getDataFolder() . "playing.yml", Config::YAML);
		$config->set($player->getName(), false);
		$config->save();
	}
}