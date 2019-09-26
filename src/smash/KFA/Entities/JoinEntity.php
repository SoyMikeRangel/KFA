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

namespace smash\KFA\Entities;

use pocketmine\entity\Human;

class JoinEntity extends Human
{
	public function getName(): string
	{
		return '';
	}
}