<?php

declare(strict_types=1);

namespace betterlite\betterprotector\session;

use betterlite\betterprotector\utils\BoundingBox;
use pocketmine\math\Vector3;
use pocketmine\world\Position;
use pocketmine\world\World;
use InvalidArgumentException;

/**
 * Tracks player selection, wand interaction, and current area status.
 * Author: BetterLite Team
 */
class SelectionSession {

    private ?Position $pos1 = null;
    private ?Position $pos2 = null;
    private ?string $lastAreaName = null;
    private bool $bypass = false;

    public function isBypass(): bool {
        return $this->bypass;
    }

    public function setBypass(bool $bypass): void {
        $this->bypass = $bypass;
    }

    public function toggleBypass(): bool {
        $this->bypass = !$this->bypass;
        return $this->bypass;
    }

    public function getPos1(): ?Position {
        return $this->pos1;
    }

    public function setPos1(Position|Vector3|null $pos, ?World $world = null): void {
        if ($pos === null) {
            $this->pos1 = null;
            return;
        }

        $w = $pos instanceof Position ? $pos->getWorld() : $world;
        if ($w === null) {
            throw new InvalidArgumentException("World must be provided when pos is not a Position");
        }

        $this->pos1 = new Position($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $w);
    }

    public function getPos2(): ?Position {
        return $this->pos2;
    }

    public function setPos2(Position|Vector3|null $pos, ?World $world = null): void {
        if ($pos === null) {
            $this->pos2 = null;
            return;
        }

        $w = $pos instanceof Position ? $pos->getWorld() : $world;
        if ($w === null) {
            throw new InvalidArgumentException("World must be provided when pos is not a Position");
        }

        $this->pos2 = new Position($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $w);
    }

    public function hasCompleteSelection(): bool {
        return $this->pos1 !== null && $this->pos2 !== null
            && $this->pos1->getWorld()->getFolderName() === $this->pos2->getWorld()->getFolderName();
    }

    public function getBoundingBox(): ?BoundingBox {
        if (!$this->hasCompleteSelection() || $this->pos1 === null || $this->pos2 === null) {
            return null;
        }

        return BoundingBox::fromCorners($this->pos1, $this->pos2);
    }

    public function getVolume(): ?int {
        return $this->getBoundingBox()?->getVolume();
    }

    public function resetSelection(): void {
        $this->pos1 = null;
        $this->pos2 = null;
    }

    public function getLastAreaName(): ?string {
        return $this->lastAreaName;
    }

    public function setLastAreaName(?string $name): void {
        $this->lastAreaName = $name !== null ? strtolower($name) : null;
    }
}
