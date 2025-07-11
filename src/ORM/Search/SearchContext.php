<?php

namespace SilverCart\ORM\Search;

use SilverCart\ORM\Filters\DateRangeSearchFilter;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\SelectField;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;

/**
 * Provides the ability to search between two dates.
 *
 * @package SilverCart
 * @subpackage ORM_Search
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 11.10.2017
 * @copyright 2017 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class SearchContext extends \SilverStripe\ORM\Search\SearchContext {

  /**
   * Replace the default form fields for the 'Created' search
   * field with a single text field which we can use to apply
   * jquery date range widget to.
   *
   * @return FieldList
   */
    public function getSearchFields() {
        $fields = ($this->fields) ? $this->fields : singleton($this->modelClass)->scaffoldSearchFields();

        if ($fields) {
            $dates = array ();

            foreach ($fields as $field) {
                $type = get_class(singleton($this->modelClass)->obj($field->getName()));
                if ($type == DBDate::class || $type == DBDatetime::class) {
                    $dates[] = $field;
                }
            }

            foreach ($dates as $d) {
                $fields->removeByName($d->getName());
                $myField = new TextField($d->getName(), $d->Title());
                //$myField->addExtraClass("Form_SearchForm_q_Created");
                $fields->push($myField);
            }
        }

        return $fields;
    }

    /**
     * Alter the existing SQL query object by adding some filters for the search
     * so that the query finds objects between two dates min and max
     *
     * @param array $searchParams  The search parameters
     * @param mixed $sort          The SQL sort statement
     * @param mixed $limit         The SQL limit statement
     * @param mixed $existingQuery The existing query
     *
     * @return DataList
     */
    public function getQuery($searchParams, $sort = false, $limit = false, $existingQuery = null) {
        $list             = parent::getQuery($searchParams, $sort, $limit, $existingQuery);
        $searchParamArray = (is_object($searchParams)) ? $searchParams->getVars() : $searchParams;

        foreach ($searchParamArray as $fieldName => $value) {
            if ($fieldName == 'Created') {
                $filter = $this->getFilter($fieldName);

                if ($filter && get_class($filter) == DateRangeSearchFilter::class) {
                    $min_val = null;
                    $max_val = null;

                    $filter->setModel($this->modelClass);

                    if (strpos($value, '-') === false) {
                        $min_val = $value;
                    } else {
                        preg_match('/([^\s]*)(\s-\s(.*))?/i', $value, $matches);

                        $min_val = (isset($matches[1])) ? $matches[1] : null;

                        if (isset($matches[3])) {
                            $max_val = $matches[3];
                        }
                    }

                    if ($min_val && $max_val) {
                        $filter->setMin($min_val);
                        $filter->setMax($max_val);
                        $list = $list->alterDataQuery(array($filter, 'apply'));
                    } else if ($min_val) {
                        $filter->setMin($min_val);
                        $list = $list->alterDataQuery(array($filter, 'apply'));
                    } else if ($max_val) {
                        $filter->setMax($max_val);
                        $list = $list->alterDataQuery(array($filter, 'apply'));
                    }
                }
            }
        }
        return $list;
    }
    
    /**
     * Workaroung to add proper summary support for MultiDropdownFields.
     * 
     * @return ArrayList
     */
    public function getSummary() {
        $list = ArrayList::create();
        foreach ($this->searchParams as $searchField => $searchValue) {
            if (empty($searchValue)) {
                continue;
            }
            $filter = $this->getFilter($searchField);
            if (!$filter) {
                continue;
            }

            $field = $this->fields->fieldByName($filter->getFullName());
            if (!$field) {
                continue;
            }

            // For dropdowns, checkboxes, etc, get the value that was presented to the user
            // e.g. not an ID
            if ($field->hasMethod('getSummary')) {
                $searchValue = $field->getSummary($searchValue);
            } elseif ($field instanceof SelectField) {
                $source = $field->getSource();
                if (isset($source[$searchValue])) {
                    $searchValue = $source[$searchValue];
                }
            } else {
                // For checkboxes, it suffices to simply include the field in the list, since it's binary
                if ($field instanceof CheckboxField) {
                    $searchValue = null;
                }
            }

            $list->push(ArrayData::create([
                'Field' => $field->Title(),
                'Value' => $searchValue,
            ]));
        }

        return $list;
    }
    
}

