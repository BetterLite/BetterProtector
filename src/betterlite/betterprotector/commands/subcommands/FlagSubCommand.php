<?php

declare(strict_types=1);

namespace betterlite\betterprotector\commands\subcommands;

use betterlite\betterprotector\BetterProtector;
use betterlite\betterprotector\flag\Flag;
use betterlite\betterprotector\libs\commandox\argument\EnumArgument;
use betterlite\betterprotector\libs\commandox\argument\StringArgument;
use betterlite\betterprotector\libs\commandox\BaseSubCommand;
use betterlite\betterprotector\libs\commandox\CommandContext;
use betterlite\betterprotector\utils\Messages;
use pocketmine\plugin\Plugin;
use function implode;
use function strtolower;

/**
 * Flag subcommand: views or modifies flags for an area.
 * Author: BetterLite Team
 */
class FlagSubCommand extends BaseSubCommand {

    public function __construct(Plugin $plugin) {
        parent::__construct(
            $plugin,
            "flag",
            "View or modify flags of a protected area",
            ["setflag", "flags"]
        );
    }

    protected function configure(): void {
        $this->setPermission("betterprotector.command.area.flag");
        $this->registerArgument(0, new StringArgument("area"));
        $this->registerArgument(1, new EnumArgument("flag", Flag::all(), true));
        // Values must match everything Flag::parseValue() accepts.
        $this->registerArgument(2, new EnumArgument("value", [
            "allow", "deny", "true", "false", "on", "off",
            "enable", "disable", "enabled", "disabled", "yes", "no", "1", "0"
        ], true));
    }

    public function onRun(CommandContext $context): void {
        $sender = $context->getSender();
        $areaName = strtolower((string) $context->getArg("area"));
        $plugin = BetterProtector::getInstance();
        $area = $plugin->getAreaManager()->getArea($areaName);

        if ($area === null) {
            Messages::send($sender, "area-delete-not-found", ["name" => $areaName]);
            return;
        }

        $flagKey = $context->getArg("flag");
        if ($flagKey === null) {
            $sender->sendMessage("§8=== §bFlags for Area §e" . $area->getName() . " §8===");
            foreach (Flag::all() as $fKey) {
                $status = Flag::formatValue($area->getFlag($fKey));
                $desc = Flag::getDescription($fKey);
                $sender->sendMessage(" §7- §b{$fKey}§7: {$status} §8({$desc})");
            }
            $sender->sendMessage("§7Change flag with: §e/area flag " . $area->getName() . " <flag> <allow/deny>");
            $sender->sendMessage("§8=======================================");
            return;
        }

        $flagKey = strtolower((string) $flagKey);
        if (!Flag::isValid($flagKey)) {
            Messages::send($sender, "area-flag-invalid", [
                "flag" => $flagKey,
                "flags" => implode(", ", Flag::all())
            ]);
            return;
        }

        $valRaw = $context->getArg("value");
        if ($valRaw === null) {
            $newVal = $area->toggleFlag($flagKey);
            $plugin->getAreaManager()->save();
            Messages::send($sender, "area-flag-updated", [
                "flag" => $flagKey,
                "value" => Flag::formatValue($newVal),
                "name" => $area->getName()
            ]);
            return;
        }

        $value = Flag::parseValue((string) $valRaw);
        if ($value === null) {
            Messages::send($sender, "area-flag-invalid-value", ["value" => (string) $valRaw]);
            return;
        }

        $area->setFlag($flagKey, $value);
        $plugin->getAreaManager()->save();

        Messages::send($sender, "area-flag-updated", [
            "flag" => $flagKey,
            "value" => Flag::formatValue($value),
            "name" => $area->getName()
        ]);
    }
}
