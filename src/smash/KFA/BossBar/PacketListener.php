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

namespace smash\KFA\BossBar;

use InvalidArgumentException;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\plugin\Plugin;
use pocketmine\Server;

class PacketListener implements Listener
{
	/** @var Plugin|null */
	private static $registrant;

	public static function isRegistered(): bool
	{
		return self::$registrant instanceof Plugin;
	}

	/**
	 * @return Plugin
	 */
	public static function getRegistrant(): Plugin
	{
		return self::$registrant;
	}

	/**
	 *
	 */
	public static function unregister(): void
	{
		self::$registrant = null;
	}

	/**
	 * @param Plugin $plugin
	 */
	public static function register(Plugin $plugin): void
	{
		if (self::isRegistered()) {
			return;//silent return
		}

		self::$registrant = $plugin;
		$plugin->getServer()->getPluginManager()->registerEvents(new self, $plugin);
	}

	/**
	 * @param DataPacketReceiveEvent $e
	 */
	public function onDataPacketReceiveEvent(DataPacketReceiveEvent $e)
	{
		if ($e->getPacket() instanceof BossEventPacket) $this->onBossEventPacket($e);
	}

	/**
	 * @param DataPacketReceiveEvent $e
	 */
	private function onBossEventPacket(DataPacketReceiveEvent $e)
	{
		if (!($pk = $e->getPacket()) instanceof BossEventPacket) throw new InvalidArgumentException(get_class($e->getPacket()) . " is not a " . BossEventPacket::class);
		/** @var BossEventPacket $pk */
		switch ($pk->eventType) {
			case BossEventPacket::TYPE_REGISTER_PLAYER:
			case BossEventPacket::TYPE_UNREGISTER_PLAYER:
				Server::getInstance()->getLogger()->debug("Got BossEventPacket " . ($pk->eventType === BossEventPacket::TYPE_REGISTER_PLAYER ? "" : "un") . "register by client for player id " . $pk->playerEid);
				break;
			default:
				$e->getPlayer()->kick("Invalid packet received", false);
		}
	}

}