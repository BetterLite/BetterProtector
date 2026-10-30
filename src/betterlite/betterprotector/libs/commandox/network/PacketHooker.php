<?php

declare(strict_types=1);

namespace betterlite\betterprotector\libs\commandox\network;

use betterlite\betterprotector\libs\commandox\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\serializer\AvailableCommandsPacketAssembler;
use pocketmine\network\mcpe\protocol\serializer\AvailableCommandsPacketDisassembler;
use pocketmine\network\mcpe\protocol\types\command\CommandHardEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use function array_values;

/**
 * Intercepts AvailableCommandsPacket to inject Bedrock client-side
 * command overloads, enums, and tab-completion suggestions.
 */
class PacketHooker implements Listener {

    private static bool $registered = false;

    public static function isRegistered(): bool {
        return self::$registered;
    }

    public static function register(Plugin $plugin): void {
        if (self::$registered) {
            return;
        }

        $plugin->getServer()->getPluginManager()->registerEvents(new self(), $plugin);
        self::$registered = true;
    }

    /**
     * Reset registration state. Must be called on plugin disable so that
     * a subsequent enable (e.g. /reload) re-registers the listener.
     */
    public static function unregister(): void {
        self::$registered = false;
    }

    public function onDataPacketSend(DataPacketSendEvent $event): void {
        $packets = $event->getPackets();

        // Quick check: only intercept if AvailableCommandsPacket is present
        $hasAvailableCommands = false;
        foreach ($packets as $packet) {
            if ($packet instanceof AvailableCommandsPacket) {
                $hasAvailableCommands = true;
                break;
            }
        }

        if (!$hasAvailableCommands) {
            return;
        }

        $targets = $event->getTargets();
        if (empty($targets)) {
            return;
        }

        // Safely extract session regardless of array indexing
        $session = array_values($targets)[0] ?? null;
        if ($session === null) {
            return;
        }

        $player = $session->getPlayer();
        if ($player === null) {
            return;
        }

        $newPackets = [];
        $modified = false;

        foreach ($packets as $packet) {
            if ($packet instanceof AvailableCommandsPacket) {
                try {
                    $disassembled = AvailableCommandsPacketDisassembler::disassemble($packet);
                    $hasCommando = false;

                    foreach ($disassembled->commandData as $data) {
                        $cmd = Server::getInstance()->getCommandMap()->getCommand($data->name);
                        if ($cmd instanceof BaseCommand) {
                            $hasCommando = true;
                            if (!$cmd->testPermissionSilent($player)) {
                                $data->overloads = [];
                                continue;
                            }

                            $overloads = self::generateOverloads($cmd, $player);
                            if (!empty($overloads)) {
                                $data->overloads = $overloads;
                            }
                        }
                    }

                    if ($hasCommando) {
                        // BUG-FIX: preserve unused hard enums and soft enums so that
                        // other plugins' commands are not broken by the reassembly.
                        $newPackets[] = AvailableCommandsPacketAssembler::assemble(
                            array_values($disassembled->commandData),
                            array_values($disassembled->unusedHardEnums),
                            array_values($disassembled->unusedSoftEnums)
                        );
                        $modified = true;
                        continue;
                    }
                } catch (\Throwable $e) {
                    // Never crash the server because of a packet hook failure —
                    // fall through and send the original packet untouched.
                    $plugin = $player->getServer()->getPluginManager()->getPlugin("BetterProtector");
                    if ($plugin !== null) {
                        $plugin->getLogger()->error(
                            "[CommandoX] Failed to inject tab-completion data: " . $e->getMessage()
                        );
                    }
                }
            }

            $newPackets[] = $packet;
        }

        if ($modified) {
            $event->setPackets($newPackets);
        }
    }

    /**
     * @return CommandOverload[]
     */
    private static function generateOverloads(BaseCommand $command, CommandSender $sender): array {
        $overloads = [];

        // Generate overloads for each subcommand
        foreach ($command->getSubCommands() as $name => $subCommand) {
            if (!$subCommand->checkPermission($sender)) {
                continue;
            }

            $subParam = CommandParameter::enum(
                $subCommand->getName(),
                new CommandHardEnum($subCommand->getName(), [$subCommand->getName()]),
                0,
                false
            );

            $params = [$subParam];
            foreach ($subCommand->getArguments() as $argument) {
                $netParam = $argument->getNetworkParameter();
                if ($netParam !== null) {
                    $params[] = $netParam;
                }
            }

            $overloads[] = new CommandOverload(false, $params);
        }

        // Generate overloads for root command arguments (if any)
        $rootArgs = $command->getArguments();
        if (!empty($rootArgs)) {
            $rootParams = [];
            foreach ($rootArgs as $argument) {
                $netParam = $argument->getNetworkParameter();
                if ($netParam !== null) {
                    $rootParams[] = $netParam;
                }
            }
            if (!empty($rootParams)) {
                $overloads[] = new CommandOverload(false, $rootParams);
            }
        } elseif (empty($overloads)) {
            $overloads[] = new CommandOverload(false, []);
        }

        return $overloads;
    }
}
