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

namespace KFA\EventListeners;

use KFA\Database\Connection;
use KFA\Database\DataManager;
use KFA\Entities\JoinEntity;
use KFA\Entities\Leaderboard;
use KFA\KFA;
use KFA\TaskHandlers\KitTask;
use KFA\TaskHandlers\LeaveTask;
use KFA\TaskHandlers\RespawnTask;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\level\particle\HeartParticle;
use pocketmine\level\sound\AnvilFallSound;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;

class  EventHandler implements Listener
{

	private $db;
	private $manager;

	/**
	 * EventHandler constructor.
	 * @param KFA $main
	 */
	public function __construct(KFA $main)
	{
		$this->db = new Connection($main);
		$this->manager = new DataManager();
	}

	/**
	 * @param EntityDamageEvent $event
	 */
	public function onDeath(EntityDamageEvent $event)
	{
		$player = $event->getEntity();
		$x = $player->getX();
		$y = $player->getY();
		$z = $player->getZ();
		$level = Server::getInstance()->getLevelByName(DataManager::getArena());
		$pos = new Vector3($x, $y + 1, $z);
		if ($player instanceof Player) {
			if ($player->getLevel()->getName() == DataManager::getArena()) {
				if ($player->getHealth() < 2.0 || $player->getHealth() == 0.0) {
					KFA::getInstance()->getScheduler()->scheduleRepeatingTask(new RespawnTask($player), 20);
					$level->dropItem(new Vector3($x, $y, $z), Item::get(Item::GOLDEN_APPLE, 0, DataManager::getGapples()));
					$player->getInventory()->clearAll();
					$player->getArmorInventory()->clearAll();
					$player->setHealth(20);
					$player->setFood(20);
					$player->getInventory()->setItem(4, Item::get(Item::SLIME_BALL, 0, 1)->setCustomName("§eLeave FFA"));
					if ($event instanceof EntityDamageByEntityEvent) {
						$cause = $event->getEntity()->getLastDamageCause();
						$victim = $event->getEntity();
						if ($cause instanceof EntityDamageByEntityEvent) {
							if ($cause->getDamager() instanceof Player && $victim instanceof Player) {
								$cause->getDamager()->setHealth(20);
								$level->addParticle(new HeartParticle($pos, 2));
								$level->addSound(new AnvilFallSound($pos));
								$this->manager->setKill($cause->getDamager()->getName());
								$this->manager->setDeath($victim->getName());
							}
						}
					}
				}
			}
		}
	}

	/**
	 * @param EntityDeathEvent $event
	 */
	public function noDrops(EntityDeathEvent $event)
	{
		if ($event->getEntity()->getLevel()->getName() == DataManager::getArena()) {
			$event->setDrops([]);
		}
	}

	/**
	 * @param EntityDamageByEntityEvent $event
	 */
	public function onHitNPC(EntityDamageByEntityEvent $event)
	{
		$connection = new Connection(KFA::getInstance());
		$npc = $event->getEntity();
		$player = $event->getDamager();
		if ($npc instanceof JoinEntity && $player instanceof Player) {
			$name = $player->getName();
			$player->teleport(DataManager::getRandomSpawn());
			KFA::getInstance()->getScheduler()->scheduleTask(new KitTask($player));
			$player->addTitle("§c☣FFA☣", "§7> Be the last one!", 20, 20, 20);
			$event->setCancelled(true);
			$sql = $connection->getDatabase()->prepare("INSERT OR IGNORE INTO Players(NAME,KILLS,DEATHS,KDR) SELECT :name, :kills, :deaths, :kdr WHERE NOT EXISTS(SELECT * FROM Players WHERE NAME = :name);");
			$sql->bindValue(":name", $name, SQLITE3_TEXT);
			$sql->bindValue(":kills", 0, SQLITE3_NUM);
			$sql->bindValue(":deaths", 0, SQLITE3_NUM);
			$sql->bindValue(":kdr", 0, SQLITE3_FLOAT);
			$sql->execute();
		}
	}

	/**
	 * @param PlayerInteractEvent $event
	 */
	public function onUseSlime(PlayerInteractEvent $event)
	{
		if ($event->getPlayer()->getLevel()->getName() == DataManager::getArena()) {
			if ($event->getItem()->getId() == Item::SLIME_BALL && $event->getItem()->getCustomName() == "§eLeave FFA") {
				KFA::getInstance()->getScheduler()->scheduleTask(new LeaveTask($event->getPlayer()));
			}
		}
	}

	/**
	 * @param EntityDamageByEntityEvent $event
	 */
	public function onKnockback(EntityDamageByEntityEvent $event)
	{
		$damager = $event->getDamager();
		$victim = $event->getEntity();
		if (DataManager::getSettings()->get('knockback_enabled') == true) {
			if ($damager->getLevel()->getName() == DataManager::getArena() && $victim->getLevel()->getName() == DataManager::getArena()) {
				$event->setKnockBack(DataManager::getSettings()->get('knockback'));
			}
		}
	}

	/**
	 * @param PlayerExhaustEvent $event
	 */
	public function disableHunger(PlayerExhaustEvent $event)
	{
		if (DataManager::getSettings()->get('hunger') == 'true') {
			if ($event->getEntity()->getLevel()->getName() == DataManager::getArena()) {
				$event->setCancelled(true);
			}
		} else {
			if ($event->getEntity()->getLevel()->getName() == DataManager::getArena()) {
				$event->setCancelled(false);
			}
		}
	}

	/**
	 * @param EntityDamageByEntityEvent $event
	 */
	public function onHitLeaderboards(EntityDamageByEntityEvent $event)
	{
		$npc = $event->getEntity();
		$player = $event->getDamager();
		if ($npc instanceof Leaderboard && $player instanceof Player) {
			$event->setCancelled(true);
		}
	}

	/**
	 * @param EntityDamageEvent $event
	 */
	public static function noBasicDamage(EntityDamageEvent $event)
	{
		if ($event->getCause() == EntityDamageEvent::CAUSE_FALL) {
			$event->setCancelled();
		}
		if ($event->getCause() == EntityDamageEvent::CAUSE_SUICIDE) {
			$event->setCancelled();
		}
		if ($event->getCause() == EntityDamageEvent::CAUSE_STARVATION) {
			$event->setCancelled();
		}
	}
}