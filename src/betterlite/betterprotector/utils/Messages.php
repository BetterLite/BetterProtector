<?php

declare(strict_types=1);

namespace betterlite\betterprotector\utils;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

/**
 * Message formatter and sender.
 * Author: BetterLite Team
 */
class Messages {

    /** @var array<string, string> */
    private static array $messages = [];
    private static string $prefix = "";

    public static function init(Config $config): void {
        self::$messages = [];
        foreach ($config->getAll() as $key => $value) {
            if (is_string($value)) {
                self::$messages[$key] = $value;
            }
        }
        self::$prefix = self::format(self::$messages["prefix"] ?? "§8[§bBetterProtector§8]§r ");
    }

    public static function format(string $message): string {
        return TextFormat::colorize($message);
    }

    /**
     * @param array<string, string|int|float> $params
     */
    public static function get(string $key, array $params = [], bool $includePrefix = true): string {
        $msg = self::$messages[$key] ?? $key;
        foreach ($params as $paramKey => $paramValue) {
            $msg = str_replace("{" . $paramKey . "}", (string) $paramValue, $msg);
        }

        $formatted = self::format($msg);
        if ($includePrefix && $key !== "prefix") {
            return self::$prefix . $formatted;
        }

        return $formatted;
    }

    /**
     * @param array<string, string|int|float> $params
     */
    public static function send(CommandSender $sender, string $key, array $params = [], bool $includePrefix = true): void {
        $sender->sendMessage(self::get($key, $params, $includePrefix));
    }

    /**
     * @param array<string, string|int|float> $params
     */
    public static function sendPopup(Player $player, string $key, array $params = []): void {
        $player->sendPopup(self::get($key, $params, false));
    }

    /**
     * @param array<string, string|int|float> $params
     */
    public static function sendActionBar(Player $player, string $key, array $params = []): void {
        $player->sendActionBarMessage(self::get($key, $params, false));
    }

    /**
     * @param array<string, string|int|float> $params
     */
    public static function sendTitle(Player $player, string $titleKey, string $subtitleKey = "", array $params = []): void {
        $title = self::get($titleKey, $params, false);
        $subtitle = $subtitleKey !== "" ? self::get($subtitleKey, $params, false) : "";
        $player->sendTitle($title, $subtitle);
    }
}
