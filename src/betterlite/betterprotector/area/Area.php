<?php

declare(strict_types=1);

namespace betterlite\betterprotector\area;

use betterlite\betterprotector\flag\Flag;
use betterlite\betterprotector\utils\BoundingBox;
use pocketmine\math\Vector3;
use pocketmine\world\Position;

/**
 * Representation of a protected area.
 * Author: BetterLite Team
 */
class Area {

    /** @var array<string, bool> */
    private array $flags;

    /** @var array<string, bool> Whitelist map (lowercase username => true) */
    private array $whitelist;

    /**
     * @param array<string, bool> $flags
     * @param string[] $whitelist
     */
    public function __construct(
        private string $name,
        private string $worldName,
        private BoundingBox $bounds,
        array $flags = [],
        array $whitelist = [],
        private int $priority = 0,
        private ?string $enterMessage = null,
        private ?string $leaveMessage = null
    ) {
        $this->name = strtolower($name);
        $this->flags = array_merge(Flag::DEFAULTS, $flags);
        $this->whitelist = [];
        foreach ($whitelist as $player) {
            $this->whitelist[strtolower(trim($player))] = true;
        }
    }

    public function getName(): string {
        return $this->name;
    }

    public function getWorldName(): string {
        return $this->worldName;
    }

    public function getBounds(): BoundingBox {
        return $this->bounds;
    }

    public function getPriority(): int {
        return $this->priority;
    }

    public function setPriority(int $priority): void {
        $this->priority = $priority;
    }

    /**
     * @return array<string, bool>
     */
    public function getFlags(): array {
        return $this->flags;
    }

    public function getFlag(string $flag, ?bool $default = null): bool {
        $flag = strtolower($flag);
        return $this->flags[$flag] ?? ($default ?? Flag::getDefault($flag));
    }

    public function setFlag(string $flag, bool $value): void {
        $this->flags[strtolower($flag)] = $value;
    }

    public function toggleFlag(string $flag): bool {
        $newVal = !$this->getFlag($flag);
        $this->setFlag($flag, $newVal);
        return $newVal;
    }

    /**
     * @return string[]
     */
    public function getWhitelist(): array {
        return array_keys($this->whitelist);
    }

    public function isWhitelisted(string $player): bool {
        return isset($this->whitelist[strtolower(trim($player))]);
    }

    public function addWhitelisted(string $player): bool {
        $player = strtolower(trim($player));
        if (isset($this->whitelist[$player])) {
            return false;
        }
        $this->whitelist[$player] = true;
        return true;
    }

    public function removeWhitelisted(string $player): bool {
        $player = strtolower(trim($player));
        if (!isset($this->whitelist[$player])) {
            return false;
        }
        unset($this->whitelist[$player]);
        return true;
    }

    public function contains(Position|Vector3 $pos, ?string $worldName = null): bool {
        if ($pos instanceof Position) {
            if ($pos->getWorld()->getFolderName() !== $this->worldName) {
                return false;
            }
        } elseif ($worldName !== null && $worldName !== $this->worldName) {
            return false;
        }

        return $this->bounds->contains($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
    }

    public function getEnterMessage(): ?string {
        return $this->enterMessage;
    }

    public function setEnterMessage(?string $message): void {
        $this->enterMessage = $message;
    }

    public function getLeaveMessage(): ?string {
        return $this->leaveMessage;
    }

    public function setLeaveMessage(?string $message): void {
        $this->leaveMessage = $message;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array {
        return [
            "name" => $this->name,
            "world" => $this->worldName,
            "bounds" => $this->bounds->toArray(),
            "flags" => $this->flags,
            "whitelist" => array_values($this->getWhitelist()),
            "priority" => $this->priority,
            "enter_message" => $this->enterMessage,
            "leave_message" => $this->leaveMessage
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            (string) $data["name"],
            (string) $data["world"],
            BoundingBox::fromArray($data["bounds"]),
            (array) ($data["flags"] ?? []),
            (array) ($data["whitelist"] ?? []),
            (int) ($data["priority"] ?? 0),
            isset($data["enter_message"]) && is_string($data["enter_message"]) ? $data["enter_message"] : null,
            isset($data["leave_message"]) && is_string($data["leave_message"]) ? $data["leave_message"] : null
        );
    }
}
