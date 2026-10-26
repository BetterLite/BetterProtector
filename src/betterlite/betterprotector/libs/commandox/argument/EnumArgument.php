<?php

declare(strict_types=1);

namespace betterlite\betterprotector\libs\commandox\argument;

use betterlite\betterprotector\libs\commandox\CommandArgument;
use betterlite\betterprotector\libs\commandox\exception\ArgumentParseException;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\types\command\CommandHardEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use function array_values;
use function implode;
use function strtolower;
use function trim;

class EnumArgument implements CommandArgument {

    /**
     * @param string   $name
     * @param string[] $values   valid values (recommended: lowercase)
     * @param bool     $optional
     */
    public function __construct(
        private string $name,
        private array $values,
        private bool $optional = false
    ) {}

    public function getName(): string {
        return $this->name;
    }

    public function isOptional(): bool {
        return $this->optional;
    }

    /**
     * @return string[]
     */
    public function getValues(): array {
        return $this->values;
    }

    public function getTypeName(): string {
        return "enum(" . implode("|", $this->values) . ")";
    }

    public function getUsageSyntax(): string {
        $syntax = "{$this->name}:" . implode("|", $this->values);
        return $this->optional ? "[{$syntax}]" : "<{$syntax}>";
    }

    public function parse(array $words, int &$cursor, CommandSender $sender): mixed {
        if (!isset($words[$cursor])) {
            if ($this->optional) {
                return null;
            }
            throw new ArgumentParseException("Missing value for '{$this->name}'.", $this->name);
        }

        $raw = trim($words[$cursor]);
        $cursor++;

        if ($raw === "" && $this->optional) {
            return null;
        }

        $rawLower = strtolower($raw);
        $matchedValue = null;
        foreach ($this->values as $value) {
            if (strtolower($value) === $rawLower) {
                $matchedValue = $value;
                break;
            }
        }

        if ($matchedValue === null) {
            throw new ArgumentParseException(
                "Invalid value for '{$this->name}'. Allowed: " . implode(", ", $this->values),
                $this->name
            );
        }

        return $matchedValue;
    }

    public function getSuggestions(CommandSender $sender): array {
        return $this->values;
    }

    public function getNetworkParameter(): ?CommandParameter {
        return CommandParameter::enum(
            $this->name,
            new CommandHardEnum($this->name, array_values($this->values)),
            0,
            $this->optional
        );
    }
}
