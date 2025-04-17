<?php

namespace Masgeek;

use Composer\Command\BaseCommand;
use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;

class CommandProvider implements CommandProviderCapability
{

   /**
    * Provides a list of commands available in the application.
    * 
    * This method is required by the CommandProviderCapability interface
    * and is used to register commands with the command bus or dispatcher.
    * Currently returns an empty array, indicating no commands are provided.
    * 
    * @return array|BaseCommand[] An array of BaseCommand objects
    */
    public function getCommands(): array
    {
        return [];
    }
}
