<?php

namespace SilverCart\Model\Widgets;

use SilverCart\Model\Widgets\PageListWidget;
use SilverStripe\Assets\Image;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\Core\Extension as DataExtension;

/**
 * PageListWidget Page Extension.
 *
 * @package SilverCart
 * @subpackage Model_Widgets
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 09.10.2017
 * @copyright 2017 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class PageListWidgetPage extends DataExtension {

    /**
     * DB attributes
     *
     * @var array
     */
    private static $db = array(
        'widgetTitle'    => 'Varchar(255)',
        'widgetText'     => 'HTMLText',
        'widgetPriority' => 'Int(0)'
    );
    
    /**
     * Has one relations
     *
     * @var array
     */
    private static $has_one = array(
        'widgetImage' => Image::class,
    );
    
    /**
     * Belongs many many relations
     *
     * @var array
     */
    private static $belongs_many_many = array(
        'PageListWidgets' => PageListWidget::class,
    );

    /**
     * Add labels.
     *
     * @param array &$labels The labels
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>, Sebastian Diel <sdiel@pixeltricks.de>
     * @since 04.04.2013
     */
    public function updateFieldLabels(&$labels) {
        $labels = array_merge(
                $labels,
                [
                    'widgetInfoTab'     => _t(PageListWidgetPage::class . '.WIDGET_INFO_TAB', 'Widget infos'),
                    'widgetInfoTabInfo' => _t(PageListWidgetPage::class . '.WIDGET_INFO_TAB_EXPLANATION', 'The following data can be used by some widgets.'),
                    'widgetImage'       => _t(PageListWidgetPage::class . '.WIDGET_IMAGE', 'Image'),
                    'widgetText'        => _t(PageListWidgetPage::class . '.WIDGET_TEXT', 'Description'),
                    'widgetTitle'       => _t(PageListWidgetPage::class . '.WIDGET_TITLE', 'Title (optional)'),
                    'widgetPriority'    => _t(PageListWidgetPage::class . '.WIDGET_PRIORITY', 'Sort priority (the higher the more backwards)'),
                ]
        );
    }

    /**
     * Add fields to CMS fields.
     *
     * @param FieldList $fields The FieldList
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>, Sebastian Diel <sdiel@pixeltricks.de>, Jiri Ripa <jripa@pixeltricks.de>
     * @since 04.04.2013, 11.01.2026
     */
    public function updateCMSFields(FieldList $fields): void
    {
        $tab = 'Root.Widgets';

        // Baue die Felderliste nur aus Feldern auf, die noch nicht existieren
        $innerFields = [];

        $innerFields[] = LiteralField::create(
            'widgetInfoTabExplanation',
            '<div class="field">' . $this->owner->fieldLabel('widgetInfoTabInfo') . '</div>'
        );

        if (!$fields->dataFieldByName('widgetPriority')) {
            $innerFields[] = TextField::create('widgetPriority', $this->owner->fieldLabel('widgetPriority'));
        }
        if (!$fields->dataFieldByName('widgetTitle')) {
            $innerFields[] = TextField::create('widgetTitle', $this->owner->fieldLabel('widgetTitle'));
        }
        if (!$fields->dataFieldByName('widgetText')) {
            $innerFields[] = HTMLEditorField::create('widgetText', $this->owner->fieldLabel('widgetText'));
        }
        if (!$fields->dataFieldByName('widgetImage')) {
            $innerFields[] = UploadField::create('widgetImage', $this->owner->fieldLabel('widgetImage'));
        }

        if (count($innerFields) < 2) {
            return;
        }

        $widgetInfoField = ToggleCompositeField::create(
            'widgetInfoTab',
            $this->owner->fieldLabel('widgetInfoTab'),
            $innerFields
        )->setHeadingLevel(4);

        // Optional: ToggleCompositeField selbst nicht doppelt hinzufügen
        if (!$fields->fieldByName($tab . '.widgetInfoTab')) {
            $fields->addFieldToTab($tab, $widgetInfoField);
        }
    }
}