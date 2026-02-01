<?php

namespace SilverCart\Admin\Controllers;

use SilverCart\Admin\Controllers\ModelAdmin;
use SilverCart\Model\System\SystemErrorLog;

/**
 * ModelAdmin for SystemErrorLog.
 *
 * @package SilverCart
 * @subpackage Admin_Controllers
 */
class SystemErrorLogAdmin extends ModelAdmin
{
    /**
     * The code of the menu under which this admin should be shown.
     *
     * @var string
     */
    private static $menuCode = 'config';
    /**
     * The section of the menu under which this admin should be grouped.
     *
     * @var string
     */
    private static $menuSortIndex = 30;
    /**
     * The URL segment
     *
     * @var string
     */
    private static $url_segment = 'silvercart-system-errors';
    /**
     * The menu title
     *
     * @var string
     */
    private static $menu_title = 'System Errors';
    /**
     * Managed models
     *
     * @var string[]
     */
    private static $managed_models = [
        SystemErrorLog::class,
    ];
}
