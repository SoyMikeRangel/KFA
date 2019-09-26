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

namespace smash\KFA\EventListeners;

use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use smash\KFA\BossBar\BossBar;
use smash\KFA\Database\Connection;
use smash\KFA\Database\DataManager;
use smash\KFA\Entities\JoinEntity;
use smash\KFA\Entities\Leaderboard;
use smash\KFA\KFA;
use smash\KFA\PluginUtils\PluginUtils;
use smash\KFA\TaskHandlers\KitTask;
use smash\KFA\TaskHandlers\RespawnTask;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerMoveEvent;
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
			if ($player->getLevel()->getFolderName() == DataManager::getArena()) {
				switch ($event->getCause()) {
					case 1:
					case 2:
					case 5:
					case 7:
					case 8:
					case 11:
						if ($event->getFinalDamage() > $player->getHealth()) {
							$event->setCancelled();
							KFA::getInstance()->getScheduler()->scheduleRepeatingTask(new RespawnTask($player), 20);
							$level->dropItem(new Vector3($x, $y, $z), Item::get(Item::GOLDEN_APPLE, 0, DataManager::getGapples()));
							$level->addParticle(new HeartParticle($pos, 2));
							$player->getInventory()->clearAll();
							$player->getArmorInventory()->clearAll();
							$player->setHealth(20);
							$player->setFood(20);
							if ($event instanceof EntityDamageByEntityEvent) {
								$cause = $event->getEntity()->getLastDamageCause();
								$victim = $event->getEntity();
								if ($cause instanceof EntityDamageByEntityEvent) {
									$cause->getDamager()->setHealth(20);
									if ($cause->getDamager()->getName() == DataManager::getTopOne()) {
										$this->addStrike($player, true);
										$this->addStrike($cause->getDamager(), true);
									}
									$level->addSound(new AnvilFallSound($pos));
									if ($cause->getDamager()->getName() != $victim->getName()) {
										$this->manager->setKill($cause->getDamager()->getName());
										$this->manager->setDeath($victim->getName());
									}
								}
							}
						}
						break;
					default: //other than those above:
						return $event->setCancelled();
				}
			}
		}
	}

	/**
	 * @param Player $p
	 * @param $boolHere
	 * Set a lightning strike
	 */
	public function addStrike(Player $p, $boolHere)
	{
		if ($boolHere == true) {
			$light = new AddActorPacket();
			$light->type = 93;
			$light->entityRuntimeId = Entity::$entityCount++;
			$light->metadata = array();
			$light->position = $p->asVector3()->add(0, $height = 0);
			$light->yaw = $p->getYaw();
			$light->pitch = $p->getPitch();
			$p->dataPacket($light);
		}
	}

	/**
	 * @param EntityDeathEvent $event
	 */
	public function noDrops(EntityDeathEvent $event)
	{
		if ($event->getEntity()->getLevel()->getFolderName() == DataManager::getArena()) {
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
			$event->setCancelled(true);
			$name = $player->getName();
			$player->teleport(DataManager::getRandomSpawn());
			$player->setGamemode(2);
			KFA::getInstance()->getScheduler()->scheduleTask(new KitTask($player));
			$player->addTitle("§c☣ F F A ☣", "§7> Kill them all!");
			$bar = new BossBar();
			$bar->setTitle("§5[§k§6II§r§5] §9 = §4KFA §9 = §5[§k§6II§r§5]");
			$bar->setPercentage(100.0);
			$bar->addPlayer($player);
			$ffaplayers = Server::getInstance()->getLevelByName(DataManager::getArena())->getPlayers();
			if ($player->getName() == DataManager::getTopOne()) {
				$player->setNameTag($player->getNameTag() . "\n§aTop 1");
				$player->setDisplayName($player->getNameTag() . "\n§aTop 1");
				if (count($ffaplayers) > 0) {
					foreach ($ffaplayers as $player) {
						$player->sendMessage(PluginUtils::PREFIX . "§9TOP 1 §a" . DataManager::getTopOne() . "§9 Has joined!");
					}
				}
			}
			DataManager::setPlaying($player);
			$sql = $connection->getDatabase()->prepare("INSERT OR IGNORE INTO Players(NAME,KILLS,DEATHS,KDR) SELECT :name, :kills, :deaths, :kdr WHERE NOT EXISTS(SELECT * FROM Players WHERE NAME = :name);");
			$sql->bindValue(":name", $name, SQLITE3_TEXT);
			$sql->bindValue(":kills", 0, SQLITE3_NUM);
			$sql->bindValue(":deaths", 0, SQLITE3_NUM);
			$sql->bindValue(":kdr", 0, SQLITE3_FLOAT);
			$sql->execute();
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
			if ($damager->getLevel()->getFolderName() == DataManager::getArena() && $victim->getLevel()->getFolderName() == DataManager::getArena()) {
				$event->setKnockBack(DataManager::getSettings()->get('knockback'));
			}
		}
	}

	/**
	 * @param PlayerExhaustEvent $event
	 */
	public function disableHunger(PlayerExhaustEvent $event)
	{
		if (DataManager::getSettings()->get('hunger')) {
			if ($event->getPlayer()->getLevel()->getFolderName() == DataManager::getArena()) {
				$event->setCancelled(true);
			}
		} else {
			if ($event->getPlayer()->getLevel()->getFolderName() == DataManager::getArena()) {
				$event->setCancelled(false);
			}
		}
	}

	/*
	* @param PlayerMoveEvent $event
	* Particles to top one
	*/

	public function HeartSpiral(PlayerMoveEvent $event)
	{
		$player = $event->getPlayer();
		if ($player->getName() == DataManager::getTopOne()) {
			$level = $player->getLevel();
			if ($level == DataManager::getArena()) {
				$x = $player->getX();
				$y = $player->getY();
				$z = $player->getZ();
				$center = new Vector3($x, $y, $z);
				$particle = new HeartParticle($center);
				for ($yaw = 0, $y = $center->y; $y < $center->y + 2; $yaw += (M_PI * 2) / 20, $y += 1 / 20) {
					$x = -sin($yaw) + $center->x;
					$z = cos($yaw) + $center->z;
					$particle->setComponents($x, $y, $z);
					$level->addParticle($particle);
				}
			}
		}
	}

	/**
	 * @param EntityDamageByEntityEvent $event
	 */
	public function onHitLeaderboards(EntityDamageByEntityEvent $event)
	{
		$npc = $event->getEntity();
		if ($npc instanceof Leaderboard) {
			$event->setCancelled(true);
		}
	}

	/**
	 * @param PlayerExhaustEvent $event
	 */
	public function noHunger(PlayerExhaustEvent $event)
	{
		if ($event->getPlayer()->getLevel()->getFolderName() == DataManager::getArena()) {
			$event->setCancelled();
		}
	}
}