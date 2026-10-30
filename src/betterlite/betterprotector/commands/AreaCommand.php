<?php

declare(strict_types=1);

namespace betterlite\betterprotector\commands;

use betterlite\betterprotector\BetterProtector;
use betterlite\betterprotector\commands\subcommands\BypassSubCommand;
use betterlite\betterprotector\commands\subcommands\CreateSubCommand;
use betterlite\betterprotector\commands\subcommands\DeleteSubCommand;
use betterlite\betterprotector\commands\subcommands\FlagSubCommand;
use betterlite\betterprotector\commands\subcommands\HelpSubCommand;
use betterlite\betterprotector\commands\subcommands\InfoSubCommand;
use betterlite\betterprotector\commands\subcommands\ListSubCommand;
use betterlite\betterprotector\commands\subcommands\MsgSubCommand;
use betterlite\betterprotector\commands\subcommands\Pos1SubCommand;
use betterlite\betterprotector\commands\subcommands\Pos2SubCommand;
use betterlite\betterprotector\commands\subcommands\PrioritySubCommand;
use betterlite\betterprotector\commands\subcommands\ReloadSubCommand;
use betterlite\betterprotector\commands\subcommands\TeleportSubCommand;
use betterlite\betterprotector\commands\subcommands\WandSubCommand;
use betterlite\betterprotector\commands\subcommands\WhitelistSubCommand;
use betterlite\betterprotector\libs\commandox\BaseCommand;
use betterlite\betterprotector\libs\commandox\CommandContext;
use pocketmine\plugin\Plugin;

/**
 * Main command router for /area powered by CommandoX.
 * Author: BetterLite Team
 */
class AreaCommand extends BaseCommand {

    public function __construct(Plugin $plugin) {
        parent::__construct(
            $plugin,
            "area",
            "Manage protected areas",
            ["bp", "betterprotector", "guard"]
        );
    }

    protected function configure(): void {
        $this->setPermission("betterprotector.command.area");

        $this->registerSubCommand(new HelpSubCommand($this->plugin));
        $this->registerSubCommand(new BypassSubCommand($this->plugin));
        $this->registerSubCommand(new Pos1SubCommand($this->plugin));
        $this->registerSubCommand(new Pos2SubCommand($this->plugin));
        $this->registerSubCommand(new WandSubCommand($this->plugin));
        $this->registerSubCommand(new CreateSubCommand($this->plugin));
        $this->registerSubCommand(new DeleteSubCommand($this->plugin));
        $this->registerSubCommand(new FlagSubCommand($this->plugin));
        $this->registerSubCommand(new WhitelistSubCommand($this->plugin));
        $this->registerSubCommand(new ListSubCommand($this->plugin));
        $this->registerSubCommand(new InfoSubCommand($this->plugin));
        $this->registerSubCommand(new TeleportSubCommand($this->plugin));
        $this->registerSubCommand(new PrioritySubCommand($this->plugin));
        $this->registerSubCommand(new MsgSubCommand($this->plugin));
        $this->registerSubCommand(new ReloadSubCommand($this->plugin));
    }

    public function onRun(CommandContext $context): void {
        $help = $this->getSubCommands()["help"] ?? null;
        if ($help !== null) {
            $help->onRun($context);
        }
    }
}
