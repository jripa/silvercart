<?php

namespace SilverCart\Admin\Forms\GridField;

use ReflectionClass;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\Map;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Requirements;
use SilverStripe\View\SSViewer;

/**
 * Base for batch actions.
 *
 * @package SilverCart
 * @subpackage Admin_Forms_GridField_BatchActions
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 22.09.2017
 * @copyright 2017 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class GridFieldBatchAction
{
    /**
     * Name of action
     *
     * @var string
     */
    protected $action = null;
    /**
     * name of class
     *
     * @var string
     */
    protected $class = null;
    
    /**
     * Sets the default of a GridFieldBatchAction.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->class = get_class($this);
        $this->action = str_replace(GridFieldBatchAction::class, '', $this->class);
    }
    
    /**
     * Returns the markup of the callback form fields.
     * 
     * @return string
     */
    public function getCallbackFormFields()
    {
        return '';
    }

    /**
     * Returns the title of the action
     * 
     * @return string
     */
    public function getTitle() : string
    {
        return _t($this->class . '.TITLE', $this->action);
    }
    
    /**
     * Is used to call javascript requirements of an action.
     * 
     * @return void
     */
    public function RequireJavascript() : void
    {
        
    }
    
    /**
     * Is used to call javascript requirements of an action.
     * 
     * @param string $filename Name of the JS file
     * 
     * @return void
     */
    public function RequireDefaultJavascript(string $filename = null) : void
    {
        if ($filename === null) {
            $reflection = new ReflectionClass(static::class);
            $filename = $reflection->getShortName();
        }
        Requirements::javascript("silvercart/silvercart:client/admin/javascript/{$filename}.js");
    }
    
    /**
     * Handles the action.
     * 
     * @param GridField $gridField GridField to handle action for
     * @param array     $recordIDs Record IDs to handle action for
     * @param array     $data      Data to handle action for
     * 
     * @return void
     */
    public function handle(GridField $gridField, array $recordIDs, array $data)
    {
        
    }
    
    /**
     * Returns a DataObject as a Dropdown field
     * 
     * @param string $classname Classname of the DataObject to get Dropdown field for
     * 
     * @return DropdownField
     */
    public function getDataObjectAsDropdownField(string $classname) : DropdownField
    {
        $records    = DataObject::get($classname);
        $recordsMap = $records->map();
        if ($recordsMap instanceof Map) {
            $recordsMap = $recordsMap->toArray();
        }
        return DropdownField::create($classname, $classname, $recordsMap);
    }
    
    /**
     * Handles the default action to reset a has_one relation.
     * 
     * @param GridField $gridField    GridField to handle action for
     * @param array     $recordIDs    Record IDs to handle action for
     * @param int       $targetID     ID of the target relation
     * @param string    $relationName Name of the relation to change
     * 
     * @return void
     */
    public function handleDefaultHasOneRelation(GridField $gridField, array $recordIDs, int $targetID, string $relationName) : void
    {
        foreach ($recordIDs as $recordID) {
            $record = DataObject::get($gridField->getModelClass())->byID($recordID);
            if ($record->exists()) {
                $record->{$relationName} = $targetID;
                $record->write();
            }
        }
    }
    
    /**
     * Renders the given data with the class related template.
     * 
     * @param array $data Data to use in template
     * 
     * @return \SilverStripe\ORM\FieldType\DBHTMLText
     */
    public function render(array $data) : DBHTMLText
    {
        $template = SSViewer::get_templates_by_class($this, '', static::class);
        $forTemplate = ArrayData::create($data);
        return $forTemplate->renderWith($template);
    }
}