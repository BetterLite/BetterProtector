<?php

declare(strict_types=1);

namespace betterlite\betterprotector\commands\subcommands;

use betterlite\betterprotector\BetterProtector;
use betterlite\betterprotector\flag\Flag;
use betterlite\betterprotector\libs\commandox\argument\StringArgument;
use betterlite\betterprotector\libs\commandox\BaseSubCommand;
use betterlite\betterprotector\libs\commandox\CommandContext;
use betterlite\betterprotector\utils\Messages;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use function count;
use function implode;
use function number_format;
use function strtolower;

/**
 * Info subcommand: displays details about an area.
 * Author: BetterLite Team
 */
class InfoSubCommand extends BaseSubCommand {

    public function __construct(Plugin $plugin) {
        parent::__construct(
            $plugin,
            "info",
            "Show detailed information about an area",
            ["i", "show"]
        );
    }

    protected function configure(): void {
        $this->setPermission("betterprotector.command.area.info");
        $this->registerArgument(0, new StringArgument("name", true));
    }

    public function onRun(CommandContext $context): void {
        $sender = $context->getSender();
        $areaManager = BetterProtector::getInstance()->getAreaManager();
        $areaName = $context->getArg("name");
        $area = null;

        if ($areaName !== null && (string)$areaName !== "") {
            $area = $areaManager->getArea(strtolower((string)$areaName));
            if ($area === null) {
                Messages::send($sender, "area-delete-not-found", ["name" => (string)$areaName]);
                return;
            }
        } elseif ($sender instanceof Player) {
            $area = $areaManager->getAreaAt($sender->getPosition());
            if ($area === null) {
                $sender->sendMessage("§7You are not standing inside any protected area.");
                return;
            }
        } else {
            $sender->sendMessage("§cUsage: /area info <name>");
            return;
        }

        $b = $area->getBounds();
        $flagsFormatted = [];
        foreach ($area->getFlags() as $f => $val) {
            $flagsFormatted[] = "§b{$f}§7: " . Flag::formatValue($val);
        }

        $whitelist = $area->getWhitelist();
        $whitelistText = count($whitelist) > 0 ? implode("§7, §f", $whitelist) : "§7(none)";

        $sender->sendMessage("§8=== §bArea Details: §e" . $area->getName() . " §8===");
        $sender->sendMessage("§7World: §f" . $area->getWorldName());
        $sender->sendMessage("§7Bounds: §f({$b->getMinX()}, {$b->getMinY()}, {$b->getMinZ()}) §7to §f({$b->getMaxX()}, {$b->getMaxY()}, {$b->getMaxZ()})");
        $sender->sendMessage("§7Dimensions: §f{$b->getWidth()}x{$b->getHeight()}x{$b->getLength()} §7(§e" . number_format($b->getVolume()) . " blocks§7)");
        $sender->sendMessage("§7Priority: §f" . $area->getPriority());
        $sender->sendMessage("§7Whitelist: §f" . $whitelistText);
        $sender->sendMessage("§7Flags: " . implode("§7, ", $flagsFormatted));

        if ($area->getEnterMessage() !== null) {
            $sender->sendMessage("§7Enter Message: §f" . Messages::format($area->getEnterMessage()));
        }
        if ($area->getLeaveMessage() !== null) {
            $sender->sendMessage("§7Leave Message: §f" . Messages::format($area->getLeaveMessage()));
        }

        $sender->sendMessage("§8=======================================");
    }
}
