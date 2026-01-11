<?php

namespace SilverCart\Admin\Dev\Tasks;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Security\Permission;
use Symfony\Component\Console\Input\InputInterface;
use SilverStripe\PolyExecution\PolyOutput;

/**
 * Provides a task to show the phpinfo() output.
 * 
 * @package SilverCart
 * @subpackage Admin_Dev_Tasks
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2017 pixeltricks GmbH
 * @since 22.09.2017
 * @license see license file in modules root directory
 */
class PHPInfoTask extends BuildTask {

    /**
     * Set a custom url segment (to follow dev/tasks/)
     *
     * @var string
     */
    private static $segment = 'phpinfo';

    /**
     * Shown in the overview on the {@link TaskRunner}.
     * HTML or CLI interface. Should be short and concise, no HTML allowed.
     * 
     * @var string
     */
    protected string $title = 'PHP Info Task';

    /**
     * Describe the implications the task has, and the changes it makes. Accepts 
     * HTML formatting.
     * 
     * @var string
     */
    protected static string $description = 'Task to show the PHP info output.';
    
    /**
     * Runs this task.
     * 
     * @param HTTPRequest $request Request
     * 
     * @return void
     */
    public function run_old($request) {
        if (Permission::check('ADMIN')) {
            phpinfo();
        }
    }

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        if (Permission::check('ADMIN')) {
            ob_start();
            phpinfo();
            $phpinfo = ob_get_clean();
            $output->writeln($phpinfo);
            return 0;
        }
        $output->writeln('Permission denied. ADMIN permission required to run this task.');
        return 1;
    }

}