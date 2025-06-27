<?php

namespace SilverCart\Admin\Model;

use Broarm\CookieConsent\CookieConsent;
use SilverCart\Dev\Tools;
use SilverCart\Forms\FormFields\FieldGroup;
use SilverCart\Forms\FormFields\TextField;
use SilverCart\Forms\FormFields\TextareaField;
use SilverCart\Model\Pages\Page;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\Requirements;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * SiteConfig extension for cookie policy (EU law) settings.
 * 
 * @package SilverCart
 * @subpackage Admin_Model
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 15.05.2018
 * @copyright 2018 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class CookiePolicyConfig extends DataExtension
{
    /**
     * DB attributes.
     *
     * @var array
     */
    private static $db = [
        'CookiePolicyConfigIsActive'    => 'Boolean(1)',
        'CookiePolicyConfigPosition'    => 'Enum("BannerBottom,BannerTop,BannerTopPushDown,FloatingLeft,FloatingRight","BannerTopPushDown")',
        'CookiePolicyConfigLayout'      => 'Enum("Block,Classic,Edgeless,Wire","Block")',
        'CookiePolicyConfigBgColor'     => 'Varchar(7)',
        'CookiePolicyConfigTxtColor'    => 'Varchar(7)',
        'CookiePolicyConfigBtnColor'    => 'Varchar(7)',
        'CookiePolicyConfigBtnTxtColor' => 'Varchar(7)',
        'CookiePolicyConfigMessageText' => 'Text',
        'CookiePolicyConfigButtonText'  => 'Varchar(64)',
        'CookiePolicyConfigPolicyText'  => 'Varchar(64)',
    ];
    /**
     * Defaults for DB attributes.
     *
     * @var array
     */
    private static $defaults = [
        'CookiePolicyConfigIsActive'    => true,
        'CookiePolicyConfigBgColor'     => '#000000',
        'CookiePolicyConfigTxtColor'    => '#ffffff',
        'CookiePolicyConfigBtnColor'    => '#f1d600',
        'CookiePolicyConfigBtnTxtColor' => '#000000',
    ];
    
    /**
     * Updates the CMS fields.
     * 
     * @param FieldList $fields Fields to update
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 15.05.2018
     */
    public function updateCMSFields(FieldList $fields) : void
    {
        if (class_exists(CookieConsent::class)) {
            $cookyPolicyTab = $fields->findOrMakeTab('Root.CookieConsent', $this->owner->fieldLabel('CookiePolicy'));
            $cookyPolicyTab->setTitle($this->owner->fieldLabel('CookiePolicy'));
            $cookyPolicyTab->unshift(CheckboxField::create('CookiePolicyConfigIsActive', $this->owner->fieldLabel('CookiePolicyConfigIsActive')));
            if (class_exists(GridFieldOrderableRows::class)) {
                $grid = $fields->dataFieldByName('Cookies');
                /* @var $grid \SilverStripe\Forms\GridField\GridField */
                if ($grid !== null && $grid->getList() instanceof DataList) {
                    $grid->getConfig()->addComponent(GridFieldOrderableRows::create('Sort'));
                }
            }
        } else {
            $positionSrc = Tools::enum_i18n_labels($this->owner, 'CookiePolicyConfigPosition');
            $layoutSrc   = Tools::enum_i18n_labels($this->owner, 'CookiePolicyConfigLayout');

            $colorGroup = FieldGroup::create('ColorGroup', '', $fields);
            $colorGroup->push(TextField::create('CookiePolicyConfigBgColor', $this->owner->fieldLabel('CookiePolicyConfigBgColor'))->setInputType('color'));
            $colorGroup->push(TextField::create('CookiePolicyConfigTxtColor', $this->owner->fieldLabel('CookiePolicyConfigTxtColor'))->setInputType('color'));
            $colorGroup->push(TextField::create('CookiePolicyConfigBtnColor', $this->owner->fieldLabel('CookiePolicyConfigBtnColor'))->setInputType('color'));
            $colorGroup->push(TextField::create('CookiePolicyConfigBtnTxtColor', $this->owner->fieldLabel('CookiePolicyConfigBtnTxtColor'))->setInputType('color'));

            $cookyPolicyTab = $fields->findOrMakeTab('Root.CookiePolicy', $this->owner->fieldLabel('CookiePolicy'));
            $cookyPolicyTab->push(CheckboxField::create('CookiePolicyConfigIsActive', $this->owner->fieldLabel('CookiePolicyConfigIsActive')));
            $cookyPolicyTab->push(TextareaField::create('CookiePolicyConfigMessageText', $this->owner->fieldLabel('CookiePolicyConfigMessageText')));
            $cookyPolicyTab->push(TextField::create('CookiePolicyConfigButtonText', $this->owner->fieldLabel('CookiePolicyConfigButtonText')));
            $cookyPolicyTab->push(TextField::create('CookiePolicyConfigPolicyText', $this->owner->fieldLabel('CookiePolicyConfigPolicyText')));
            $cookyPolicyTab->push(DropdownField::create('CookiePolicyConfigPosition', $this->owner->fieldLabel('CookiePolicyConfigPosition'))->setSource($positionSrc));
            $cookyPolicyTab->push(DropdownField::create('CookiePolicyConfigLayout', $this->owner->fieldLabel('CookiePolicyConfigLayout'))->setSource($layoutSrc));
            $cookyPolicyTab->push($colorGroup);

            $this->owner->extend('updateCookiePolicyFields', $cookyPolicyTab);
        }
    }
    
    /**
     * Updates the field labels.
     * 
     * @param array &$labels Labels to update
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 15.05.2018
     */
    public function updateFieldLabels(&$labels) : void
    {
        $labels = array_merge(
                $labels,
                Tools::field_labels_for(self::class),
                Tools::enum_field_labels_for($this->owner, 'CookiePolicyConfigPosition', self::class),
                Tools::enum_field_labels_for($this->owner, 'CookiePolicyConfigLayout', self::class),
                [
                    'CookiePolicy'         => _t(self::class . '.CookiePolicy', 'Cookie Policy'),
                    'CookieConsentTitle'   => _t(Page::class . '.TITLE', 'Title'),
                    'CookieConsentContent' => _t(self::class . '.CookiePolicy', 'Cookie Policy'),
                ]
        );
    }
    
    /**
     * Sets the default values if empty.
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 15.05.2018
     */
    public function requireDefaultRecords() : void
    {
        $config = SiteConfig::current_site_config();
        if ($config instanceof SiteConfig
         && $config->exists()
         && empty($config->CookiePolicyConfigMessageText)
        ) {
            $this->setDefaultValuesFor($config);
        }
    }
    
    /**
     * Sets the default values for the given SiteConfig
     * 
     * @param SiteConfig $config SiteConfig
     * 
     * @return void
     */
    protected function setDefaultValuesFor(SiteConfig $config) : void
    {
        $defaults = $config->config()->get('defaults');
        $config->CookiePolicyConfigBgColor     = $defaults['CookiePolicyConfigBgColor'];
        $config->CookiePolicyConfigTxtColor    = $defaults['CookiePolicyConfigTxtColor'];
        $config->CookiePolicyConfigBtnColor    = $defaults['CookiePolicyConfigBtnColor'];
        $config->CookiePolicyConfigBtnTxtColor = $defaults['CookiePolicyConfigBtnTxtColor'];
        $config->CookiePolicyConfigMessageText = $this->owner->fieldLabel('CookiePolicyConfigMessageTextDefault');
        $config->CookiePolicyConfigButtonText  = $this->owner->fieldLabel('CookiePolicyConfigButtonTextDefault');
        $config->CookiePolicyConfigPolicyText  = $this->owner->fieldLabel('CookiePolicyConfigPolicyTextDefault');
        $config->write();
    }

    /**
     * Returns the basic configuration parameters for cookie consent.
     * 
     * @return array
     */
    public function getCookiePolicyPositionConfig() : array
    {
        $bgColor     = $this->owner->CookiePolicyConfigBgColor;
        $txtColor    = $this->owner->CookiePolicyConfigTxtColor;
        $btnColor    = $this->owner->CookiePolicyConfigBtnColor;
        $btnTxtColor = $this->owner->CookiePolicyConfigBtnTxtColor;
        
        $cfg = [];
        $cfg['palette'] = [
            'popup' => [
                'background' => $bgColor,
                'text'       => $txtColor,
            ],
            'button' => [
                'background' => $btnColor,
                'text'       => $btnTxtColor,
            ],
        ];
        $cfg['content'] = [
            'message' => $this->owner->CookiePolicyConfigMessageText,
            'dismiss' => $this->owner->CookiePolicyConfigButtonText,
            'link'    => $this->owner->CookiePolicyConfigPolicyText,
        ];
        $dataPrivacyPage = Tools::PageByIdentifierCode(Page::IDENTIFIER_DATA_PRIVACY_PAGE);
        if (!($dataPrivacyPage instanceof Page)) {
            $dataPrivacyPage = Tools::PageByIdentifierCode('Silvercart' . Page::IDENTIFIER_DATA_PRIVACY_PAGE);
        }
        if ($dataPrivacyPage instanceof Page) {
            $cfg['content']['href'] = $dataPrivacyPage->Link();
        }
        switch ($this->owner->CookiePolicyConfigPosition) {
            case 'BannerTop':
                $cfg['position'] = 'top';
                break;
            case 'BannerTopPushDown':
                $cfg['position'] = 'top';
                $cfg['static']   = true;
                break;
            case 'FloatingLeft':
                $cfg['position'] = 'bottom-left';
                break;
            case 'FloatingRight':
                $cfg['position'] = 'bottom-right';
                break;
            case 'BannerBottom':
            default:
                break;
        }
        switch ($this->owner->CookiePolicyConfigLayout) {
            case 'Classic':
                $cfg['theme'] = 'classic';
                break;
            case 'Edgeless':
                $cfg['theme'] = 'edgeless';
                break;
            case 'Wire':
                $cfg['palette']['button'] = [
                    'background' => 'transparent',
                    'text'       => $btnColor,
                    'border'     => $btnColor,
                ];
                break;
            case 'Block':
            default:
                break;
        }
        return $cfg;
    }
    
    /**
     * Loads the JS and CSS requirements.
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 18.05.2018
     */
    public static function load_requirements() : void
    {
        if (class_exists(CookieConsent::class)) {
            return;
        }
        $config = SiteConfig::current_site_config();
        if ($config->CookiePolicyConfigIsActive) {
            $cfg    = $config->getCookiePolicyPositionConfig();
            $json   = json_encode($cfg);
            $js     = 'window.addEventListener("load", function(){window.cookieconsent.initialise(' . $json . ')});';
            Requirements::css('silvercart/silvercart:client/gdpr/cookieconsent.min.css');
            Requirements::javascript('silvercart/silvercart:client/gdpr/cookieconsent.min.js');
            Requirements::customScript($js, 'cookieconsent');
        }
    }
}