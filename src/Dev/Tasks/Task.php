<?php

namespace SilverCart\Dev\Tasks;

use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Input\InputInterface;


/**
 * Basic task functionallity to handle cli args (SS6).
 * Replaces the deprecated CliController-based task base class.
 *
 * @package SilverCart
 * @subpackage Dev_Tasks
 * @author Sebastian Diel <sdiel@pixeltricks.de>, Jiri Ripa <jripa@pixeltricks.de>
 * @since 09.01.2026
 * @copyright 2018 pixeltricks GmbH
 * @license see license file in modules root directory
 * @deprecated since version 4.1
 */
abstract class Task extends BuildTask
{
    use \SilverCart\Dev\CLITask;
    

    /**
     * Implement this in concrete tasks (instead of overriding execute()).
     */
    abstract protected function runTask(InputInterface $input, PolyOutput $output): int;

    /**
     * Minimal bridge helpers for your CLITask trait.
     */
    protected ?InputInterface $consoleInput = null;
    protected ?PolyOutput $consoleOutput = null;
   
    protected function setConsoleContext(InputInterface $input, PolyOutput $output): void
    {
        $this->consoleInput = $input;
        $this->consoleOutput = $output;
    }
    /**
     * Bridges the SilverStripe BuildTask execute() method to your CLITask trait.
     * 
     * @param InputInterface $input Console input
     * @param PolyOutput $output Console output
     * 
     * @return int
     * 
     * @author Jiri Ripa <jripa@pixeltricks.de>
     * @since 09.01.2026
     */
    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        // Bridge: make console input available to your legacy trait
        // so initArgs() can read from it (you adapt initArgs() accordingly)
        $this->setConsoleContext($input, $output);

        // Old behaviour: parse args/options
        $this->initArgs();

        // Then call your actual task runner
        return $this->runTask($input, $output);
    }
}