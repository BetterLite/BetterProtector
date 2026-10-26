<?php

declare(strict_types=1);

namespace betterlite\betterprotector\commands\subcommands;

use betterlite\betterprotector\BetterProtector;
use betterlite\betterprotector\libs\commandox\argument\EnumArgument;
use betterlite\betterprotector\libs\commandox\BaseSubCommand;
use betterlite\betterprotector\libs\commandox\CommandContext;
use betterlite\betterprotector\utils\Messages;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use function strtolower;

/**
 * Bypass subcommand: toggles administrative bypass mode.
 * Author: BetterLite Team
 */
class BypassSubCommand extends BaseSubCommand {

    public function __construct(Plugin $plugin) {
        parent::__construct(
            $plugin,
            "bypass",
            "Toggle bypass mode for all protected areas",
            ["adminbypass"]
        );
    }

    protected function configure(): void {
        $this->setPermission("betterprotector.command.area.bypass");
        $this->registerArgument(0, new EnumArgument("mode", ["on", "off", "toggle"], true));
    }

    public function onRun(CommandContext $context): void {
        $sender = $context->getSender();
        if (!($sender instanceof Player)) {
            Messages::send($sender, "error-only-player");
            return;
        }

        $session = BetterProtector::getInstance()->getSessionManager()->getSession($sender);
        $mode = $context->getArg("mode");

        if ($mode !== null) {
            $modeStr = strtolower((string) $mode);
            $enabled = match ($modeStr) {
                "on" => true,
                "off" => false,
                default => !$session->isBypass(),
            };
            $session->setBypass($enabled);
        } else {
            $session->toggleBypass();
        }

        if ($session->isBypass()) {
            Messages::send($sender, "bypass-enabled");
        } else {
            Messages::send($sender, "bypass-disabled");
        }
    }
}
