<?php

namespace SilverCart\Admin\Model;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Editable storefront color scheme.
 */
class ColorScheme extends DataObject
{
    private static $table_name = 'SilvercartColorScheme';

    private static $db = [
        'Title'          => 'Varchar(128)',
        'Code'           => 'Varchar(64)',
        'BgColor'        => 'Varchar(7)',
        'PrimaryColor'   => 'Varchar(7)',
        'SecondaryColor' => 'Varchar(7)',
        'AlternateColor' => 'Varchar(7)',
        'TextColor'      => 'Varchar(7)',
        'LinkColor'      => 'Varchar(7)',
        'BorderColor'    => 'Varchar(7)',
    ];

    private static $has_one = [
        'SiteConfig' => SiteConfig::class,
    ];

    private static $summary_fields = [
        'Title'        => 'Title',
        'Code'         => 'Code',
        'ColorPreview' => 'Colors',
    ];

    private static $casting = [
        'ColorPreview' => 'HTMLText',
    ];

    private static $default_sort = 'Title ASC';

    public function getCMSFields() : FieldList
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(['SiteConfigID']);

        foreach ($this->getColorFieldNames() as $fieldName) {
            $field = $fields->dataFieldByName($fieldName);
            if ($field instanceof TextField) {
                $field->setInputType('color');
            }
        }

        $fields->insertBefore(
            'BgColor',
            LiteralField::create(
                'ColorSchemeHint',
                '<p class="message good">' . _t(self::class . '.Hint', 'These colors are written as CSS variables for the active storefront theme.') . '</p>'
            )
        );

        return $fields;
    }

    public function fieldLabels($includerelations = true) : array
    {
        return array_merge(parent::fieldLabels($includerelations), [
            'Title'          => _t(self::class . '.Title', 'Title'),
            'Code'           => _t(self::class . '.Code', 'Code'),
            'BgColor'        => _t(self::class . '.BgColor', 'Background'),
            'PrimaryColor'   => _t(self::class . '.PrimaryColor', 'Primary'),
            'SecondaryColor' => _t(self::class . '.SecondaryColor', 'Secondary'),
            'AlternateColor' => _t(self::class . '.AlternateColor', 'Alternate'),
            'TextColor'      => _t(self::class . '.TextColor', 'Text'),
            'LinkColor'      => _t(self::class . '.LinkColor', 'Link'),
            'BorderColor'    => _t(self::class . '.BorderColor', 'Border'),
            'ColorPreview'   => _t(self::class . '.ColorPreview', 'Colors'),
        ]);
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if (empty($this->Code) && !empty($this->Title)) {
            $this->Code = strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', $this->Title));
            $this->Code = trim($this->Code, '-');
        }

        foreach ($this->getColorFieldNames() as $fieldName) {
            $this->{$fieldName} = $this->normalizeColor($this->{$fieldName});
        }
    }

    public function requireDefaultRecords() : void
    {
        $siteConfig = SiteConfig::current_site_config();
        if (!$siteConfig instanceof SiteConfig || !$siteConfig->exists()) {
            return;
        }

        foreach ($this->getDefaultColorSchemes() as $code => $data) {
            $scheme = self::get()->filter([
                'Code'         => $code,
                'SiteConfigID' => $siteConfig->ID,
            ])->first();

            if (!$scheme instanceof self) {
                $scheme = self::create();
                $scheme->Code         = $code;
                $scheme->SiteConfigID = $siteConfig->ID;
            }

            if (!$scheme->exists()) {
                foreach ($data as $fieldName => $value) {
                    $scheme->{$fieldName} = $value;
                }
                $scheme->write();
            }
        }
    }

    public function getColorPreview() : DBHTMLText
    {
        $colors = [];
        foreach ($this->getColorFieldNames() as $fieldName) {
            $color = $this->{$fieldName};
            if (!empty($color)) {
                $colors[] = sprintf(
                    '<span title="%s: %s" style="display:inline-block;width:24px;height:24px;border:1px solid #ccc;background:%s;"></span>',
                    $this->fieldLabel($fieldName),
                    $color,
                    $color
                );
            }
        }

        return DBHTMLText::create()->setValue(implode('', $colors));
    }

    public function getCustomCSS() : string
    {
        $colors = [
            'background' => $this->BgColor,
            'primary'    => $this->PrimaryColor,
            'secondary'  => $this->SecondaryColor,
            'alternate'  => $this->AlternateColor,
            'text'       => $this->TextColor,
            'link'       => $this->LinkColor,
            'border'     => $this->BorderColor,
        ];

        foreach ($colors as $key => $color) {
            $colors[$key] = $this->normalizeColor($color);
        }

        return <<<CSS
:root {
    --color-text-primary: {$colors['text']};
    --color-background: {$colors['background']};
    --color-background-secondary: {$colors['secondary']};
    --color-surface-card: {$colors['background']};
    --color-primary: {$colors['primary']};
    --color-primary-strong: {$colors['alternate']};
    --color-secondary: {$colors['secondary']};
    --color-alternate: {$colors['alternate']};
    --color-text: {$colors['text']};
    --color-text-muted: {$colors['alternate']};
    --color-link: {$colors['link']};
    --color-border: {$colors['border']};
    --iso-bg: {$colors['background']};
    --iso-gold: {$colors['primary']};
    --iso-gold-deep: {$colors['alternate']};
    --iso-gold-soft: {$colors['secondary']};
    --iso-text: {$colors['text']};
    --iso-muted: {$colors['alternate']};
    --iso-line: {$colors['border']};
    --iso-surface: {$colors['background']};
    --bs-body-bg: {$colors['background']};
    --bs-body-color: {$colors['text']};
    --bs-primary: {$colors['primary']};
    --bs-secondary: {$colors['secondary']};
    --bs-link-color: {$colors['link']};
    --bs-link-hover-color: {$colors['alternate']};
    --bs-border-color: {$colors['border']};
}

body {
    background-color: {$colors['background']};
    color: {$colors['text']};
}

a {
    color: {$colors['link']};
}

a:hover,
a:active,
a:focus {
    color: {$colors['alternate']};
}

.bg-primary {
    background-color: {$colors['primary']} !important;
}

.bg-secondary {
    background-color: {$colors['secondary']} !important;
}

.bg-alternate {
    background-color: {$colors['alternate']} !important;
}

.color-primary {
    color: {$colors['primary']};
}

.color-secondary {
    color: {$colors['secondary']};
}

.color-alternate {
    color: {$colors['alternate']};
}

#main-container > header {
    background: {$colors['background']};
    border-top-color: {$colors['primary']};
}

.btn-primary,
.btn-success {
    --bs-btn-bg: {$colors['primary']};
    --bs-btn-border-color: {$colors['primary']};
    --bs-btn-hover-bg: {$colors['alternate']};
    --bs-btn-hover-border-color: {$colors['alternate']};
}
CSS;
    }

    protected function getColorFieldNames() : array
    {
        return [
            'BgColor',
            'PrimaryColor',
            'SecondaryColor',
            'AlternateColor',
            'TextColor',
            'LinkColor',
            'BorderColor',
        ];
    }

    protected function normalizeColor(?string $color) : string
    {
        $color = trim((string) $color);
        if (preg_match('/^#[a-f0-9]{6}$/i', $color)) {
            return strtolower($color);
        }
        if (preg_match('/^#[a-f0-9]{3}$/i', $color)) {
            return strtolower($color);
        }

        return '#000000';
    }

    protected function getDefaultColorSchemes() : array
    {
        return [
            'creme' => [
                'Title'          => 'Color - Creme',
                'BgColor'        => '#fbf6f0',
                'PrimaryColor'   => '#ddcfb6',
                'SecondaryColor' => '#f3ede4',
                'AlternateColor' => '#c9b28d',
                'TextColor'      => '#3f3a3a',
                'LinkColor'      => '#3f3a3a',
                'BorderColor'    => '#e8dfd0',
            ],
            'green' => [
                'Title'          => 'Color - Green',
                'BgColor'        => '#ffffff',
                'PrimaryColor'   => '#009640',
                'SecondaryColor' => '#00aa3b',
                'AlternateColor' => '#666666',
                'TextColor'      => '#333333',
                'LinkColor'      => '#00a47b',
                'BorderColor'    => '#00a47b',
            ],
            'blue' => [
                'Title'          => 'Color - Blue',
                'BgColor'        => '#ffffff',
                'PrimaryColor'   => '#0088cc',
                'SecondaryColor' => '#005580',
                'AlternateColor' => '#666666',
                'TextColor'      => '#333333',
                'LinkColor'      => '#0088cc',
                'BorderColor'    => '#0088cc',
            ],
        ];
    }
}
