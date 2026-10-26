<?php

declare(strict_types=1);

namespace betterlite\betterprotector\libs\commandox\argument;

use betterlite\betterprotector\libs\commandox\CommandArgument;
use betterlite\betterprotector\libs\commandox\exception\ArgumentParseException;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\types\command\CommandHardEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use function in_array;
use function strtolower;
use function trim;

class BooleanArgument implements CommandArgument {

    public function __construct(
        private string $name,
        private bool $optional = false
    ) {}

    public function getName(): string {
        return $this->name;
    }

    public function isOptional(): bool {
        return $this->optional;
    }

    public function getTypeName(): string {
        return "bool";
    }

    public function getUsageSyntax(): string {
        $syntax = "{$this->name}:bool";
        return $this->optional ? "[{$syntax}]" : "<{$syntax}>";
    }

    public function parse(array $words, int &$cursor, CommandSender $sender): mixed {
        if (!isset($words[$cursor])) {
            if ($this->optional) {
                return false;
            }
            throw new ArgumentParseException("Missing boolean '{$this->name}'.", $this->name);
        }

        $raw = strtolower(trim($words[$cursor]));
        $cursor++;

        if ($raw === "" && $this->optional) {
            return false;
        }

        $trueValues  = ["true", "1", "yes", "y", "on", "allow", "enable"];
        $falseValues = ["false", "0", "no", "n", "off", "deny", "disable"];

        if (in_array($raw, $trueValues, true)) {
            return true;
        }

        if (in_array($raw, $falseValues, true)) {
            return false;
        }

        throw new ArgumentParseException(
            "Argument '{$this->name}' must be true/false or allow/deny.",
            $this->name
        );
    }

    public function getSuggestions(CommandSender $sender): array {
        return ["allow", "deny", "true", "false"];
    }

    public function getNetworkParameter(): ?CommandParameter {
        return CommandParameter::enum(
            $this->name,
            new CommandHardEnum($this->name, ["allow", "deny", "true", "false"]),
            0,
            $this->optional
        );
    }
}
