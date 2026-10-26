<?php

declare(strict_types=1);

namespace betterlite\betterprotector\commands\subcommands;

use betterlite\betterprotector\BetterProtector;
use betterlite\betterprotector\libs\commandox\argument\IntegerArgument;
use betterlite\betterprotector\libs\commandox\BaseSubCommand;
use betterlite\betterprotector\libs\commandox\CommandContext;
use pocketmine\plugin\Plugin;
use function array_slice;
use function ceil;
use function count;
use function max;
use function min;

/**
 * Help subcommand: displays available subcommands and usage.
 * Author: BetterLite Team
 */
class HelpSubCommand extends BaseSubCommand {

    public function __construct(Plugin $plugin) {
        parent::__construct(
            $plugin,
            "help",
            "Show the help menu for /area commands",
            ["?"]
        );
    }

    protected function configure(): void {
        $this->setPermission("betterprotector.command.area");
        $this->registerArgument(0, new IntegerArgument("page", true));
    }

    public function onRun(CommandContext $context): void {
        $sender = $context->getSender();
        $rootCmd = $context->getRootCommand();
        $subcommands = $rootCmd->getSubCommands();

        $available = [];
        foreach ($subcommands as $sub) {
            if ($sub->checkPermission($sender)) {
                $available[] = $sub;
            }
        }

        $perPage = 6;
        $maxPages = (int) max(1, ceil(count($available) / $perPage));
        $page = (int) ($context->getArg("page") ?? 1);
        $page = min($maxPages, max(1, $page));

        $sender->sendMessage("§8=== §bBetterProtector Help §7(§ePage §f{$page}§7/§f{$maxPages}§7) §8===");

        $offset = ($page - 1) * $perPage;
        $slice = array_slice($available, $offset, $perPage);

        foreach ($slice as $sub) {
            $usage = "/area " . $sub->getName();
            foreach ($sub->getArguments() as $arg) {
                $usage .= " " . $arg->getUsageSyntax();
            }
            $sender->sendMessage("§e" . $usage . " §8- §7" . $sub->getDescription());
        }

        if ($page < $maxPages) {
            $sender->sendMessage("§7Type §b/area help " . ($page + 1) . " §7to view the next page.");
        }
        $sender->sendMessage("§8======================================");
    }
}
