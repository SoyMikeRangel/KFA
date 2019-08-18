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

namespace KFA\Database;

use KFA\KFA;
use SQLite3;

class Connection extends SQLite3
{
	private $db;
	private static $internal;

	/**
	 * Connection constructor.
	 * @param KFA $main
	 */
	public function __construct(KFA $main)
	{
		$this->db = new SQLite3($main->getDataFolder() . DIRECTORY_SEPARATOR . "database.sq3");
		$this->createTable();
		self::$internal = $this->db;
	}

	/**
	 * @return SQLite3
	 */
	public function getDatabase(): SQLite3
	{
		return $this->db;
	}

	/**
	 * @query create table of Players
	 */
	protected function createTable()
	{
		$this->db->exec('CREATE TABLE IF NOT EXISTS Players (
            NAME TEXT NOT NULL,
            KILLS INT NOT NULL,
            DEATHS INT NOT NULL,
            KDR FLOAT NOT NULL,
            UNIQUE(NAME)
        )');
	}

	/**
	 * Close database
	 */
	public static function closeDatabase()
	{
		self::$internal->close();
	}
}