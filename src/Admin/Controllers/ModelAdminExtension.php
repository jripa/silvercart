<?php

namespace SilverCart\Admin\Controllers;

use SilverCart\Dev\Tools;
use SilverStripe\Control\Director;
use SilverStripe\Core\Extension;
use SilverStripe\View\Requirements;
use TractorCow\Fluent\Extension\FluentDirectorExtension;
use TractorCow\Fluent\State\FluentState;

/**
 * Decorates the default ModelAdmin to inject some custom javascript.
 *
 * @package SilverCart
 * @subpackage Admin\Controllers
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2017 pixeltricks GmbH
 * @since 22.09.2017
 * @license see license file in modules root directory
 * 
 * @property \SilverStripe\Admin\ModelAdmin $owner Owner
 */
class ModelAdminExtension extends Extension
{
    /**
     * Injects some custom javascript to provide instant loading of DataObject
     * tables.
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.01.2011
     */
    public function onAfterInit() : void
    {
        $request = $this->owner->getRequest();
        $localeParam = FluentDirectorExtension::config()->get('query_param');
        $requestedLocale = $request ? $request->getVar($localeParam) : null;
        $currentLocale = $requestedLocale ?: FluentState::singleton()->getLocale();
        if ($currentLocale) {
            Tools::set_current_locale($currentLocale);
        }
        if (Director::is_ajax()) {
            return;
        }
        Requirements::css('silvercart/silvercart:client/admin/css/LeftAndMainExtension.css');
        Requirements::add_i18n_javascript('silvercart/silvercart:client/javascript/lang');
    }
}
