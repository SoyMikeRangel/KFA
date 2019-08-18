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

namespace KFA\PluginUtils;

use KFA\KFA;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TE;

class PluginUtils
{
	const PREFIX = TE::GRAY . "[" . TE::DARK_RED . "KFA" . TE::GRAY . "] " . TE::GOLD . ": " . TE::RESET;
	const HELP = TE::GOLD . "-> " . self::PREFIX . "§7Help list" . TE::GOLD . " <-\n" .
	"§ahelp " . TE::GOLD . ": " . "§7Help for KFA Plugin\n" .
	"§ajoin " . TE::GOLD . ": " . "§7Join FFA, Kill all!\n" .
	"§aleave " . TE::GOLD . ": " . "§7Leave ffa, goodbye!\n" .
	"§ascore " . TE::GOLD . ": " . "§7See your ffa score\n" .
	"§astats " . TE::GOLD . ": " . "§7See global ffa score";
	const OP_HELP = TE::GOLD . "-> " . self::PREFIX . "§7Help list" . TE::GOLD . " <-" . "\n" .
	"§ahelp " . TE::GOLD . ": " . "§7Help for KFA Plugin" . "\n" .
	"§ajoin " . TE::GOLD . ": " . "§7Join FFA, Kill all!" . "\n" .
	"§aleave " . TE::GOLD . ": " . "§7Leave ffa, goodbye!" . "\n" .
	"§ascore " . TE::GOLD . ": " . "§7See your ffa score" . "\n" .
	"§astats " . TE::GOLD . ": " . "§7See global ffa score" . "\n" .
	"§asetup " . TE::GOLD . ": " . "§7Configure ffa arena" . "\n" .
	"§aarena " . TE::GOLD . ": " . "§7Select FFA level" . "\n" .
	"§anpc " . TE::GOLD . ": " . "§7Set the npc game" . "\n" .
	"§atops " . TE::GOLD . ": " . "§7Set the npc for leaderboard" . "\n" .
	"§aremnpc " . TE::GOLD . ": " . "§7Remove NPC" . "\n";

	/**
	 * @param string $message
	 * @param Player $player
	 */
	public static function sendSucessMessage(string $message, Player $player)
	{
		return $player->sendMessage(self::PREFIX . TE::GREEN . $message);
	}

	/**
	 * @param string $message
	 * @param Player $player
	 */
	public static function sendErrorMessage(string $message, Player $player)
	{
		return $player->sendMessage(self::PREFIX . TE::RED . $message);
	}

	/**
	 * @param string $message
	 * @param Player $player
	 */
	public static function sendWarningMessage(string $message, Player $player)
	{
		return $player->sendMessage(self::PREFIX . TE::YELLOW . $message);
	}

	/**
	 * @param string $message
	 */
	public static function sendSucessLog(string $message)
	{
		$logger = KFA::getInstance()->getLogger();
		return $logger->info(self::PREFIX . TE::GREEN . $message);
	}

	/**
	 * @param string $message
	 */
	public static function sendErrorLog(string $message)
	{
		$logger = KFA::getInstance()->getLogger();
		return $logger->error(self::PREFIX . TE::RED . $message);
	}

	/**
	 * @param string $message
	 */
	public static function sendWarningLog(string $message)
	{
		$logger = KFA::getInstance()->getLogger();
		return $logger->warning(self::PREFIX . TE::YELLOW . $message);
	}
}