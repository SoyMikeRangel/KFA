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
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class KitTask extends Task
{
	private $player;

	/**
	 * KitTask constructor.
	 * @param Player $player
	 */
	public function __construct(Player $player)
	{
		$this->player = $player;
	}

	public function giveEnchantedKit(Player $player)
	{
		$inv = $player->getInventory();
		$armor = $player->getArmorInventory();
		$protection = Enchantment::getEnchantment(Enchantment::PROTECTION);
		$unbreaking = Enchantment::getEnchantment(Enchantment::UNBREAKING);
		$power = Enchantment::getEnchantment(Enchantment::POWER);
		$punch = Enchantment::getEnchantment(Enchantment::PUNCH);
		$sword = Item::get(Item::DIAMOND_SWORD, 0, 1);
		$sword->addEnchantment(new EnchantmentInstance($punch));
		$bow = Item::get(Item::BOW, 1, 1);
		$bow->addEnchantment(new EnchantmentInstance($power));
		$arrow = Item::get(Item::ARROW, 0, 30);
		$g_apple = Item::get(Item::GOLDEN_APPLE, 0, 3);
		$helmet = Item::get(Item::DIAMOND_HELMET, 0, 1);
		$helmet->addEnchantment(new EnchantmentInstance($protection));
		$helmet->addEnchantment(new EnchantmentInstance($unbreaking));
		$chestplate = Item::get(Item::DIAMOND_CHESTPLATE, 0, 1);
		$chestplate->addEnchantment(new EnchantmentInstance($protection));
		$chestplate->addEnchantment(new EnchantmentInstance($unbreaking));
		$leggings = Item::get(Item::DIAMOND_LEGGINGS, 0, 1);
		$leggings->addEnchantment(new EnchantmentInstance($protection));
		$leggings->addEnchantment(new EnchantmentInstance($unbreaking));
		$boots = Item::get(Item::DIAMOND_BOOTS, 0, 1);
		$boots->addEnchantment(new EnchantmentInstance($protection));
		$boots->addEnchantment(new EnchantmentInstance($unbreaking));
		$armor->setHelmet($helmet);
		$armor->setBoots($boots);
		$armor->setChestplate($chestplate);
		$armor->setLeggings($leggings);
		$inv->setContents(array($sword, $bow, $g_apple, $arrow));
	}

	public function giveBasicKit(Player $player)
	{
		$inv = $player->getInventory();
		$armor = $player->getArmorInventory();
		$sword = Item::get(Item::DIAMOND_SWORD, 0, 1);
		$bow = Item::get(Item::BOW, 1, 1);
		$arrow = Item::get(Item::ARROW, 0, 30);
		$g_apple = Item::get(Item::GOLDEN_APPLE, 0, DataManager::getGapples());
		$helmet = Item::get(Item::DIAMOND_HELMET, 0, 1);
		$chestplate = Item::get(Item::DIAMOND_CHESTPLATE, 0, 1);
		$leggings = Item::get(Item::DIAMOND_LEGGINGS, 0, 1);
		$boots = Item::get(Item::DIAMOND_BOOTS, 0, 1);
		$armor->setHelmet($helmet);
		$armor->setBoots($boots);
		$armor->setChestplate($chestplate);
		$armor->setLeggings($leggings);
		$inv->setContents(array($sword, $bow, $g_apple, $arrow));
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
		if (DataManager::getEnchantmentStatus() == true) {
			$this->giveEnchantedKit($this->player);
		} else {
			$this->giveBasicKit($this->player);
		}
	}
}