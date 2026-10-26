<?php

declare(strict_types=1);

namespace betterlite\betterprotector\commands\subcommands;

use betterlite\betterprotector\area\Area;
use betterlite\betterprotector\BetterProtector;
use betterlite\betterprotector\libs\commandox\argument\IntegerArgument;
use betterlite\betterprotector\libs\commandox\argument\StringArgument;
use betterlite\betterprotector\libs\commandox\BaseSubCommand;
use betterlite\betterprotector\libs\commandox\CommandContext;
use pocketmine\plugin\Plugin;
use function array_slice;
use function array_values;
use function ceil;
use function count;
use function max;
use function min;
use function number_format;

/**
 * List subcommand: lists all protected areas.
 * Author: BetterLite Team
 */
class ListSubCommand extends BaseSubCommand {

    public function __construct(Plugin $plugin) {
        parent::__construct(
            $plugin,
            "list",
            "List all created protected areas",
            ["all"]
        );
    }

    protected function configure(): void {
        $this->setPermission("betterprotector.command.area.list");
        $this->registerArgument(0, new IntegerArgument("page", true));
        $this->registerArgument(1, new StringArgument("world", true));
    }

    public function onRun(CommandContext $context): void {
        $sender = $context->getSender();
        $areaManager = BetterProtector::getInstance()->getAreaManager();

        $page = (int) ($context->getArg("page") ?? 1);
        $worldFilter = $context->getArg("world") !== null ? (string)$context->getArg("world") : null;

        $areas = $worldFilter !== null
            ? $areaManager->getAreasInWorld($worldFilter)
            : $areaManager->getAllAreas();

        if (count($areas) === 0) {
            $sender->sendMessage("§7No protected areas have been registered" . ($worldFilter !== null ? " in world '{$worldFilter}'." : "."));
            return;
        }

        $areaList = array_values($areas);
        $perPage = 6;
        $maxPages = (int) max(1, ceil(count($areaList) / $perPage));
        $page = min($maxPages, max(1, $page));

        $header = "§8=== §bProtected Areas §7(§ePage §f{$page}§7/§f{$maxPages}§7) §8===";
        if ($worldFilter !== null) {
            $header = "§8=== §bProtected Areas in §e{$worldFilter} §7(§ePage §f{$page}§7/§f{$maxPages}§7) §8===";
        }
        $sender->sendMessage($header);

        $offset = ($page - 1) * $perPage;
        /** @var Area[] $slice */
        $slice = array_slice($areaList, $offset, $perPage);

        foreach ($slice as $area) {
            $b = $area->getBounds();
            $sender->sendMessage(
                "§7- §b" . $area->getName() . " §7in §e" . $area->getWorldName() .
                " §8({$b->getMinX()}, {$b->getMinY()}, {$b->getMinZ()} to {$b->getMaxX()}, {$b->getMaxY()}, {$b->getMaxZ()}) " .
                "§7[§f" . number_format($b->getVolume()) . " blocks§7]"
            );
        }

        if ($page < $maxPages) {
            $sender->sendMessage("§7Use §e/area list " . ($page + 1) . ($worldFilter !== null ? " {$worldFilter}" : "") . " §7to see next page.");
        }
        $sender->sendMessage("§8=======================================");
    }
}
