<?php

namespace SilverCart\Admin\Controllers;

use SilverCart\Dev\Tools;
use SilverCart\Admin\Forms\GridField\GridFieldBatchController;
use SilverCart\Admin\Forms\GridField\GridFieldQuickAccessController;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldExportButton;

/**
 * ModelAdmin extension for SilverCart.
 * Provides some special functions for SilverCarts admin area.
 * 
 * @package SilverCart
 * @subpackage Admin_Controllers
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2017 pixeltricks GmbH
 * @since 22.09.2017
 * @license see license file in modules root directory
 */
class ModelAdmin extends \SilverStripe\Admin\ModelAdmin
{
    /**
     * Allowed actions.
     *
     * @var array
     */
    private static $allowed_actions = [
        'handleBatchCallback',
    ];
    /**
     * The URL segment
     *
     * @var string
     */
    private static $url_segment = 'silvercart';
    /**
     * Menu icon
     *
     * @var string
     */
    private static $menu_icon = 'silvercart/silvercart:client/img/glyphicons-halflings.png';
    /**
     * Name of DB field to make records sortable by.
     *
     * @var string
     */
    private static $sortable_field = '';
    /**
     * The default CSV export delimiter character.
     * 
     * @var string
     */
    private static $csv_export_delimiter = ',';
    /**
     * The default CSV export enclosure character.
     * 
     * @var string
     */
    private static $csv_export_enclosure = '"';
    /**
     * Determines whether the CSV export file is generated with a header line.
     * 
     * @var string
     */
    private static $csv_export_has_header = true;
    /**
     * GridField of the edit form
     *
     * @var \SilverStripe\Forms\GridField\GridField
     */
    protected $gridField = null;
    /**
     * GridFieldConfig of the edit form
     *
     * @var GridFieldConfig
     */
    protected $gridFieldConfig = null;
    /**
     * If this is set to true the ModelAdmins SearchForm will be collapsed on
     * load.
     *
     * @var bool
     */
    protected static $search_form_is_collapsed = true;

    /**
     * Provides hook for decorators, so that they can overwrite css
     * and other definitions.
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.09.2018
     */
    protected function init()
    {
        parent::init();
        $this->extend('updateInit');
    }
    
    /**
     * Allows user code to hook into ModelAdmin::init() prior to updateInit 
     * being called on extensions.
     *
     * @param callable $callback The callback to execute
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.09.2018
     */
    protected function beforeUpdateInit($callback)
    {
        $this->beforeExtending('updateInit', $callback);
    }
    
    /**
     * Allows user code to hook into ModelAdmin::getEditForm() prior to 
     * updateEditForm being called on extensions.
     *
     * @param callable $callback The callback to execute
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.09.2018
     */
    protected function beforeUpdateEditForm($callback)
    {
        $this->beforeExtending('updateEditForm', $callback);
    }

    /**
     * title in the top bar of the CMS
     *
     * @return string 
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 24.10.2017
     */
    public function SectionTitle()
    {
        $sectionTitle = parent::SectionTitle();
        if (class_exists($this->modelClass)) {
            $sectionTitle = Tools::plural_name_for(singleton($this->modelClass));
        }
        return $sectionTitle;
    }
    
    /**
     * Builds and returns the edit form.
     * 
     * @param int       $id     The current records ID. Won't be used for ModelAdmins.
     * @param FieldList $fields Fields to use. Won't be used for ModelAdmins.
     * 
     * @return \SilverStripe\Forms\Form
     */
    public function getEditForm($id = null, $fields = null) : Form
    {
        $this->beforeUpdateEditForm(function(\SilverStripe\Forms\Form $form) {
            $config         = $this->getGridFieldConfigFor($form);
            $sortable_field = $this->config()->get('sortable_field');
            if (class_exists('\Symbiote\GridFieldExtensions\GridFieldOrderableRows')
             && !empty($sortable_field)
            ) {
                $config->addComponent(new \Symbiote\GridFieldExtensions\GridFieldOrderableRows($sortable_field));
            } elseif (class_exists('\UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows')
             && !empty($sortable_field)
            ) {
                $config->addComponent(new \UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows($sortable_field));
            }
            if (GridFieldBatchController::hasBatchActionsFor($this->modelClass)) {
                $config->addComponent(new GridFieldBatchController($this->modelClass, 'buttons-before-left'));
            }
            if (singleton($this->modelClass)->hasMethod('getQuickAccessFields')) {
                $config->addComponent(new GridFieldQuickAccessController());
            }
            $exportButton = $config->getComponentByType(GridFieldExportButton::class);
            if ($exportButton instanceof GridFieldExportButton) {
                $exportButton->setCsvSeparator($this->config()->csv_export_delimiter);
                $exportButton->setCsvEnclosure($this->config()->csv_export_enclosure);
                $exportButton->setCsvHasHeader($this->config()->csv_export_has_header);
            }
        });
        return parent::getEditForm($id, $fields);
    }
    
    /**
     * Returns the CSS class to use for the SearchForms collapse state.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 06.03.2014
     */
    public function SearchFormCollapseClass() : string
    {
        $collapseClass = '';
        if (self::$search_form_is_collapsed) {
            $collapseClass = 'collapsed';
        }
        return $collapseClass;
    }
    
    /**
     * Handles a batch action
     * 
     * @param HTTPRequest $request Request to handle
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.03.2013
     */
    public function handleBatchCallback(HTTPRequest $request)
    {
        $result = '';
        if (GridFieldBatchController::hasBatchActionsFor($this->modelClass)) {
            $result = GridFieldBatchController::handleBatchCallback($this->modelClass, $request);
        }
        return $result;
    }

    /**
     * Returns the GridField of the given edit form
     * 
     * @param Form $form The edit form to get GridField for
     * 
     * @return \SilverStripe\Forms\GridField\GridField
     */
    public function getGridFieldFor(Form $form) : GridField
    {
        if (is_null($this->gridField)) {
            $this->gridField = $form->Fields()->dataFieldByName($this->sanitiseClassName($this->modelClass));
        }
        return $this->gridField;
    }
    
    /**
     * Returns the GridFieldConfig of the given edit form
     * 
     * @param Form $form The edit form to get GridField for
     * 
     * @return GridFieldConfig
     */
    public function getGridFieldConfigFor(Form $form) : GridFieldConfig
    {
        if (is_null($this->gridFieldConfig)) {
            $this->gridFieldConfig = $this->getGridFieldFor($form)->getConfig();
        }
        return $this->gridFieldConfig;
    }
    
    /**
     * Workaround to hide this class in CMS menu.
     * 
     * @param Member $member Member
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 24.10.2017
     */
    public function canView($member = null) : bool
    {
        if (get_class($this) === ModelAdmin::class) {
            return false;
        }
        return parent::canView($member);
    }
}