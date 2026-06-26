<?php

namespace SilverCart\View;

use SilverStripe\TemplateEngine\SSTemplateParser;

/**
 * Extends the original SSTemplateParser to provide a configuration property to disable
 * the XML_val cache for field labels.
 * This is implemented to allow sending a shop email in multiple languages within a 
 * single PHP runtime.
 * 
 * @package SilverCart
 * @subpackage View
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 28.09.2020
 * @copyright 2020 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class SCTemplateParser extends SSTemplateParser
{
    use \SilverStripe\Core\Config\Configurable;
    /**
     * Disable the template parser cache for field labels by setting this configuration
     * property to true.
     *
     * @var bool
     */
    private static $disable_field_label_cache = false;
    /**
     * Add property names to this list to exclude them from template parser caching.
     *
     * @var array
     */
    private static $disable_cache_for_properties = [
        'SalutationText',
    ];
    
    /**
     * The basic generated PHP of LookupStep and LastLookupStep is the same, except that LookupStep calls 'obj' to
     * get the next ViewableData in the sequence, and LastLookupStep calls different methods (XML_val, hasValue, obj)
     * depending on the context the lookup is used in.
     * @see SSTemplateParser::LoLookup_AddLookupStep()
     * 
     * The configuration property disable_field_label_cache (@see $this->config()->disable_field_label_cache)
     * is used to determine whether to use the property cache for the XML_val template processor call.
     * When sending shop emails, the i18n context can change multiple times within a single PHP runtime.
     * In this case, the same object's field labels can be accesses in different i18n contexts.
     * If the cache is enabled, the first i18n context field label result will stay in cache and every email
     * will display the i18n context field labels of the first email's language.
     * 
     * @param array  &$res   Resource data
     * @param array  $sub    Sub resource data
     * @param string $method Target method to call
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 28.09.2020
     */
    public function Lookup_AddLookupStep(&$res, $sub, $method) : void
    {
        $res['LookupSteps'][] = $sub;
        $property             = $sub['Call']['Method']['text'];
        if (isset($sub['Call']['CallArguments'])
         && $arguments = $sub['Call']['CallArguments']['php']
        ) {
            $res['php'] .= "->{$method}('{$property}', [{$arguments}])";
        } else {
            $res['php'] .= "->{$method}('{$property}', [])";
        }
    }

    /**
     * Adds a fallback for legacy SilverCart include paths.
     *
     * SilverStripe 6 no longer resolves <% include SilverCart/Model/Pages/Foo %>
     * to SilverCart/Model/Pages/Includes/Foo automatically.
     *
     * @param array &$res Resource data
     *
     * @return void
     */
    public function Include__finalise(&$res) : void
    {
        $template      = $res['template'];
        $arguments     = $res['arguments'];
        $templateNames = '[["type" => "Includes", ' . $template . '], ' . $template;
        $templateName  = trim($template, "'");
        $prefix        = 'SilverCart/Model/Pages/';

        if (str_starts_with($templateName, $prefix)
         && !str_contains($templateName, '/Includes/')
        ) {
            $legacyTemplateName = "'{$prefix}Includes/" . substr($templateName, strlen($prefix)) . "'";
            $templateNames .= ', ["type" => "Includes", ' . $legacyTemplateName . '], ' . $legacyTemplateName;
        }

        $templateNames .= ']';

        $res['php'] = '$val .= \\SilverStripe\\TemplateEngine\\SSTemplateEngine::execute_template('
            . $templateNames
            . ', $scope->getCurrentItem(), ['
            . implode(',', $arguments)
            . "], \$scope, true);\n";

        if ($this->includeDebuggingComments) {
            $res['php'] =
                '$val .= \'<!-- include ' . addslashes($template ?? '') . ' -->\';' . "\n" .
                $res['php'] .
                '$val .= \'<!-- end include ' . addslashes($template ?? '') . ' -->\';' . "\n";
        }
    }
}
