<?php

declare(strict_types=1);

namespace betterlite\betterprotector\flag;

/**
 * Flag registry and definitions.
 * Author: BetterLite Team
 */
final class Flag {

    public const EDIT = "edit";
    public const TOUCH = "touch";
    public const PVP = "pvp";
    public const GOD = "god";
    public const EXPLOSION = "explosion";
    public const DECAY = "decay";
    public const ENTER = "enter";
    public const FLIGHT = "flight";
    public const ITEM_DROP = "item-drop";
    public const ITEM_PICKUP = "item-pickup";

    /** @var array<string, bool> */
    public const DEFAULTS = [
        self::EDIT => false,
        self::TOUCH => false,
        self::PVP => false,
        self::GOD => false,
        self::EXPLOSION => false,
        self::DECAY => false,
        self::ENTER => true,
        self::FLIGHT => true,
        self::ITEM_DROP => true,
        self::ITEM_PICKUP => true,
    ];

    /** @var array<string, string> */
    public const DESCRIPTIONS = [
        self::EDIT => "Allow/deny breaking and placing blocks",
        self::TOUCH => "Allow/deny interacting with chests, doors, buttons, etc.",
        self::PVP => "Allow/deny player vs player combat",
        self::GOD => "Make players immune to damage (godmode)",
        self::EXPLOSION => "Allow/deny explosions damaging blocks",
        self::DECAY => "Allow/deny leaves decay",
        self::ENTER => "Allow/deny non-whitelisted players from entering",
        self::FLIGHT => "Allow/deny player flight",
        self::ITEM_DROP => "Allow/deny dropping items",
        self::ITEM_PICKUP => "Allow/deny picking up items",
    ];

    /**
     * @return string[]
     */
    public static function all(): array {
        return array_keys(self::DEFAULTS);
    }

    public static function isValid(string $flag): bool {
        return array_key_exists(strtolower($flag), self::DEFAULTS);
    }

    public static function getDefault(string $flag): bool {
        return self::DEFAULTS[strtolower($flag)] ?? false;
    }

    public static function getDescription(string $flag): string {
        return self::DESCRIPTIONS[strtolower($flag)] ?? "No description";
    }

    /**
     * Converts a string representation to a boolean flag value.
     */
    public static function parseValue(string $value): ?bool {
        $value = strtolower(trim($value));
        return match ($value) {
            "true", "allow", "on", "enable", "enabled", "yes", "1" => true,
            "false", "deny", "off", "disable", "disabled", "no", "0" => false,
            default => null,
        };
    }

    public static function formatValue(bool $value): string {
        return $value ? "§aallow§r" : "§cdeny§r";
    }
}
