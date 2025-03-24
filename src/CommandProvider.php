<?php

namespace Masgeek;

use Composer\Command\BaseCommand;
use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;

class CommandProvider implements CommandProviderCapability
{

    /**
     * @return array|BaseCommand[]
     */
    public function getCommands(): array
    {
        return [];
    }
}
