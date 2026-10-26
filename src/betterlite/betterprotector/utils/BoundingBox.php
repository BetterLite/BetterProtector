<?php

declare(strict_types=1);

namespace betterlite\betterprotector\utils;

use pocketmine\math\Vector3;

/**
 * Axis-Aligned Bounding Box (AABB) for 3D area coordinates.
 * Author: BetterLite Team
 */
class BoundingBox {

    public function __construct(
        private int $minX,
        private int $minY,
        private int $minZ,
        private int $maxX,
        private int $maxY,
        private int $maxZ
    ) {}

    public static function fromCorners(Vector3 $pos1, Vector3 $pos2): self {
        return new self(
            (int) min($pos1->getFloorX(), $pos2->getFloorX()),
            (int) min($pos1->getFloorY(), $pos2->getFloorY()),
            (int) min($pos1->getFloorZ(), $pos2->getFloorZ()),
            (int) max($pos1->getFloorX(), $pos2->getFloorX()),
            (int) max($pos1->getFloorY(), $pos2->getFloorY()),
            (int) max($pos1->getFloorZ(), $pos2->getFloorZ())
        );
    }

    public function contains(int $x, int $y, int $z): bool {
        return $x >= $this->minX && $x <= $this->maxX
            && $y >= $this->minY && $y <= $this->maxY
            && $z >= $this->minZ && $z <= $this->maxZ;
    }

    public function intersects(BoundingBox $other): bool {
        return $this->minX <= $other->maxX && $this->maxX >= $other->minX
            && $this->minY <= $other->maxY && $this->maxY >= $other->minY
            && $this->minZ <= $other->maxZ && $this->maxZ >= $other->minZ;
    }

    public function getCenter(): Vector3 {
        return new Vector3(
            ($this->minX + $this->maxX) / 2.0,
            ($this->minY + $this->maxY) / 2.0,
            ($this->minZ + $this->maxZ) / 2.0
        );
    }

    public function getWidth(): int {
        return abs($this->maxX - $this->minX) + 1;
    }

    public function getHeight(): int {
        return abs($this->maxY - $this->minY) + 1;
    }

    public function getLength(): int {
        return abs($this->maxZ - $this->minZ) + 1;
    }

    public function getVolume(): int {
        return $this->getWidth() * $this->getHeight() * $this->getLength();
    }

    public function getMinX(): int {
        return $this->minX;
    }

    public function getMinY(): int {
        return $this->minY;
    }

    public function getMinZ(): int {
        return $this->minZ;
    }

    public function getMaxX(): int {
        return $this->maxX;
    }

    public function getMaxY(): int {
        return $this->maxY;
    }

    public function getMaxZ(): int {
        return $this->maxZ;
    }

    /**
     * @return array{min: array{int, int, int}, max: array{int, int, int}}
     */
    public function toArray(): array {
        return [
            "min" => [$this->minX, $this->minY, $this->minZ],
            "max" => [$this->maxX, $this->maxY, $this->maxZ]
        ];
    }

    /**
     * @param array{min: array{int, int, int}, max: array{int, int, int}} $data
     */
    public static function fromArray(array $data): self {
        return new self(
            (int) $data["min"][0],
            (int) $data["min"][1],
            (int) $data["min"][2],
            (int) $data["max"][0],
            (int) $data["max"][1],
            (int) $data["max"][2]
        );
    }
}
