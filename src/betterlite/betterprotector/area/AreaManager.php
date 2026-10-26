<?php

declare(strict_types=1);

namespace betterlite\betterprotector\area;

use betterlite\betterprotector\BetterProtector;
use betterlite\betterprotector\flag\Flag;
use pocketmine\player\Player;
use pocketmine\world\Position;

/**
 * Area business logic and spatial querying.
 * Author: BetterLite Team
 */
class AreaManager {

    /** @var array<string, Area> Areas indexed by lowercase name */
    private array $areas = [];

    /** @var array<string, array<string, Area>> Areas indexed by world name then area name */
    private array $areasByWorld = [];

    public function __construct(
        private BetterProtector $plugin,
        private AreaRepository $repository
    ) {
        $this->load();
    }

    public function load(): void {
        $this->areas = [];
        $this->areasByWorld = [];

        $loaded = $this->repository->load();
        foreach ($loaded as $area) {
            $this->addArea($area, false);
        }
    }

    public function save(): void {
        $this->repository->save($this->areas);
    }

    public function addArea(Area $area, bool $save = true): void {
        $name = $area->getName();
        $world = strtolower($area->getWorldName());

        $this->areas[$name] = $area;
        $this->areasByWorld[$world][$name] = $area;

        if ($save) {
            $this->save();
        }
    }

    public function removeArea(string $name, bool $save = true): bool {
        $name = strtolower($name);
        if (!isset($this->areas[$name])) {
            return false;
        }

        $area = $this->areas[$name];
        unset($this->areas[$name]);
        unset($this->areasByWorld[strtolower($area->getWorldName())][$name]);

        if ($save) {
            $this->save();
        }

        return true;
    }

    public function getArea(string $name): ?Area {
        return $this->areas[strtolower($name)] ?? null;
    }

    public function hasArea(string $name): bool {
        return isset($this->areas[strtolower($name)]);
    }

    /**
     * @return array<string, Area>
     */
    public function getAllAreas(): array {
        return $this->areas;
    }

    /**
     * @return array<string, Area>
     */
    public function getAreasInWorld(string $worldName): array {
        return $this->areasByWorld[strtolower($worldName)] ?? [];
    }

    /**
     * Returns the highest priority area containing the position, or null if none.
     */
    public function getAreaAt(Position $pos): ?Area {
        $worldName = strtolower($pos->getWorld()->getFolderName());
        if (!isset($this->areasByWorld[$worldName])) {
            return null;
        }

        $x = $pos->getFloorX();
        $y = $pos->getFloorY();
        $z = $pos->getFloorZ();

        $highest = null;
        $highestPriority = PHP_INT_MIN;

        foreach ($this->areasByWorld[$worldName] as $area) {
            if ($area->getBounds()->contains($x, $y, $z)) {
                if ($area->getPriority() >= $highestPriority) {
                    $highestPriority = $area->getPriority();
                    $highest = $area;
                }
            }
        }

        return $highest;
    }

    /**
     * Returns all areas containing the position, ordered from highest to lowest priority.
     *
     * @return Area[]
     */
    public function getAreasAt(Position $pos): array {
        $worldName = strtolower($pos->getWorld()->getFolderName());
        if (!isset($this->areasByWorld[$worldName])) {
            return [];
        }

        $x = $pos->getFloorX();
        $y = $pos->getFloorY();
        $z = $pos->getFloorZ();

        $matching = [];
        foreach ($this->areasByWorld[$worldName] as $area) {
            if ($area->getBounds()->contains($x, $y, $z)) {
                $matching[] = $area;
            }
        }

        usort($matching, fn(Area $a, Area $b) => $b->getPriority() <=> $a->getPriority());
        return $matching;
    }

    public function hasBypass(?Player $player): bool {
        if ($player === null) {
            return true;
        }

        if ($player->hasPermission("betterprotector.bypass") || $player->hasPermission("iprotector.access")) {
            return true;
        }

        $session = $this->plugin->getSessionManager()->getSession($player);
        return $session->isBypass();
    }

    public function getWorldDefaultFlag(string $worldName, string $flag): bool {
        $config = $this->plugin->getConfig();
        $worldVal = $config->getNested("worlds." . strtolower($worldName) . ".{$flag}");
        if (is_bool($worldVal)) {
            return $worldVal;
        }

        $defaultVal = $config->getNested("default-world-flags.{$flag}");
        if (is_bool($defaultVal)) {
            return $defaultVal;
        }

        return Flag::getDefault($flag);
    }

    public function canEdit(Position $pos, ?Player $player = null): bool {
        if ($this->hasBypass($player)) {
            return true;
        }

        $area = $this->getAreaAt($pos);
        if ($area !== null) {
            if ($player !== null && $area->isWhitelisted($player->getName())) {
                return true;
            }
            return $area->getFlag(Flag::EDIT);
        }

        return $this->getWorldDefaultFlag($pos->getWorld()->getFolderName(), Flag::EDIT);
    }

    public function canTouch(Position $pos, ?Player $player = null): bool {
        if ($this->hasBypass($player)) {
            return true;
        }

        $area = $this->getAreaAt($pos);
        if ($area !== null) {
            if ($player !== null && $area->isWhitelisted($player->getName())) {
                return true;
            }
            return $area->getFlag(Flag::TOUCH);
        }

        return $this->getWorldDefaultFlag($pos->getWorld()->getFolderName(), Flag::TOUCH);
    }

    public function canPvp(Position $pos, ?Player $damager = null): bool {
        if ($this->hasBypass($damager)) {
            return true;
        }

        $area = $this->getAreaAt($pos);
        if ($area !== null) {
            return $area->getFlag(Flag::PVP);
        }

        return $this->getWorldDefaultFlag($pos->getWorld()->getFolderName(), Flag::PVP);
    }

    public function isGod(Position $pos): bool {
        $area = $this->getAreaAt($pos);
        if ($area !== null) {
            return $area->getFlag(Flag::GOD);
        }

        return $this->getWorldDefaultFlag($pos->getWorld()->getFolderName(), Flag::GOD);
    }

    public function canExplode(Position $pos): bool {
        $area = $this->getAreaAt($pos);
        if ($area !== null) {
            return $area->getFlag(Flag::EXPLOSION);
        }

        return $this->getWorldDefaultFlag($pos->getWorld()->getFolderName(), Flag::EXPLOSION);
    }

    public function canDecay(Position $pos): bool {
        $area = $this->getAreaAt($pos);
        if ($area !== null) {
            return $area->getFlag(Flag::DECAY);
        }

        return $this->getWorldDefaultFlag($pos->getWorld()->getFolderName(), Flag::DECAY);
    }

    public function canEnter(Position $pos, ?Player $player = null): bool {
        if ($this->hasBypass($player)) {
            return true;
        }

        $area = $this->getAreaAt($pos);
        if ($area !== null) {
            if ($player !== null && $area->isWhitelisted($player->getName())) {
                return true;
            }
            return $area->getFlag(Flag::ENTER);
        }

        return $this->getWorldDefaultFlag($pos->getWorld()->getFolderName(), Flag::ENTER);
    }

    public function canFly(Position $pos, ?Player $player = null): bool {
        if ($this->hasBypass($player)) {
            return true;
        }

        $area = $this->getAreaAt($pos);
        if ($area !== null) {
            if ($player !== null && $area->isWhitelisted($player->getName())) {
                return true;
            }
            return $area->getFlag(Flag::FLIGHT);
        }

        return $this->getWorldDefaultFlag($pos->getWorld()->getFolderName(), Flag::FLIGHT);
    }

    public function canDropItem(Position $pos, ?Player $player = null): bool {
        if ($this->hasBypass($player)) {
            return true;
        }

        $area = $this->getAreaAt($pos);
        if ($area !== null) {
            if ($player !== null && $area->isWhitelisted($player->getName())) {
                return true;
            }
            return $area->getFlag(Flag::ITEM_DROP);
        }

        return $this->getWorldDefaultFlag($pos->getWorld()->getFolderName(), Flag::ITEM_DROP);
    }

    public function canPickupItem(Position $pos, ?Player $player = null): bool {
        if ($this->hasBypass($player)) {
            return true;
        }

        $area = $this->getAreaAt($pos);
        if ($area !== null) {
            if ($player !== null && $area->isWhitelisted($player->getName())) {
                return true;
            }
            return $area->getFlag(Flag::ITEM_PICKUP);
        }

        return $this->getWorldDefaultFlag($pos->getWorld()->getFolderName(), Flag::ITEM_PICKUP);
    }
}
