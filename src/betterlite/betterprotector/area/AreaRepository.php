<?php

declare(strict_types=1);

namespace betterlite\betterprotector\area;

use betterlite\betterprotector\flag\Flag;
use betterlite\betterprotector\utils\BoundingBox;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginLogger;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function json_decode;
use function json_encode;
use function rename;
use function unlink;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;

/**
 * Area persistence storage layer.
 * Author: BetterLite Team
 */
class AreaRepository {

    public function __construct(
        private string $filePath,
        private ?PluginLogger $logger = null
    ) {}

    /**
     * Loads areas from storage file. Seamlessly migrates legacy iProtector format if found.
     *
     * @return array<string, Area>
     */
    public function load(): array {
        if (!file_exists($this->filePath)) {
            return [];
        }

        $content = file_get_contents($this->filePath);
        if ($content === false || trim($content) === "" || trim($content) === "[]") {
            return [];
        }

        try {
            $raw = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            $this->logger?->error("[BetterProtector] Failed to parse areas storage: " . $e->getMessage());
            return [];
        }

        if (!is_array($raw)) {
            return [];
        }

        $areas = [];
        $migratedLegacy = false;

        foreach ($raw as $key => $datum) {
            if (!is_array($datum) || !isset($datum["name"])) {
                continue;
            }

            // Check if legacy iProtector format
            if (isset($datum["level"]) && isset($datum["pos1"]) && isset($datum["pos2"])) {
                $pos1 = new Vector3((float) $datum["pos1"][0], (float) $datum["pos1"][1], (float) $datum["pos1"][2]);
                $pos2 = new Vector3((float) $datum["pos2"][0], (float) $datum["pos2"][1], (float) $datum["pos2"][2]);
                $bounds = BoundingBox::fromCorners($pos1, $pos2);

                // Convert legacy inverted flags to modern standard flags
                $legacyFlags = $datum["flags"] ?? [];
                $flags = [
                    Flag::EDIT => !((bool) ($legacyFlags["edit"] ?? false)),
                    Flag::TOUCH => !((bool) ($legacyFlags["touch"] ?? false)),
                    Flag::GOD => (bool) ($legacyFlags["god"] ?? false),
                    Flag::PVP => false,
                    Flag::EXPLOSION => false,
                    Flag::DECAY => false,
                    Flag::ENTER => true,
                ];

                $area = new Area(
                    name: (string) $datum["name"],
                    worldName: (string) $datum["level"],
                    bounds: $bounds,
                    flags: $flags,
                    whitelist: (array) ($datum["whitelist"] ?? []),
                    priority: 0
                );

                $areas[$area->getName()] = $area;
                $migratedLegacy = true;
                $this->logger?->info("[BetterProtector] Migrated legacy iProtector area: " . $area->getName());
                continue;
            }

            // Standard modern format
            try {
                $area = Area::fromArray($datum);
                $areas[$area->getName()] = $area;
            } catch (\Throwable $e) {
                $this->logger?->warning("[BetterProtector] Could not load area '" . ($datum["name"] ?? $key) . "': " . $e->getMessage());
            }
        }

        // If legacy data was converted, save into the modern format immediately
        if ($migratedLegacy) {
            $this->save($areas);
            $this->logger?->notice("[BetterProtector] Legacy iProtector data saved to new modern format.");
        }

        return $areas;
    }

    /**
     * Saves areas to storage file using atomic write to prevent corruption.
     *
     * @param array<string, Area> $areas
     */
    public function save(array $areas): void {
        $data = [];
        foreach ($areas as $area) {
            $data[$area->getName()] = $area->toArray();
        }

        try {
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            $tmpPath = $this->filePath . ".tmp";

            if (file_put_contents($tmpPath, $json) !== false) {
                if (file_exists($this->filePath)) {
                    @unlink($this->filePath);
                }
                rename($tmpPath, $this->filePath);
            }
        } catch (\Throwable $e) {
            $this->logger?->error("[BetterProtector] Failed to save areas: " . $e->getMessage());
        }
    }
}
