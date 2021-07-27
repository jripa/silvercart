<?php

namespace SilverCart\Reports;

use SilverCart\Dev\Tools;
use SilverCart\Model\Customer\DeletedCustomer;
use SilverCart\Model\Customer\DeletedCustomerReason;
use SilverCart\Model\Pages\Page;
use SilverCart\Model\Product\Product;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DB;
use SilverStripe\Reports\Report;
use SilverStripe\View\ArrayData;

/**
 * Report to show reasons for customer deletions.
 * 
 * @package SilverCart
 * @subpackage Reports
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 12.07.2021
 * @copyright 2021 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class DeletedCustomersReport extends Report
{
    const REASON_FILTER_KEY = 'reason';
    const MONTH_FILTER_KEY  = 'month';
    const YEAR_FILTER_KEY   = 'year';
    
    /**
     * Returns the title.
     * 
     * @return string
     */
    public function title()
    {
        return _t(__CLASS__ . '.Title', 'Deleted Customers');
    }

    /**
     * Returns the source records.
     * 
     * @param array $params Params
     * 
     * @return ArrayList
     */
    public function sourceRecords($params = []) : ArrayList
    {
        $tableDeletedCustomer = DeletedCustomer::config()->table_name;
        
        $whereParts = [];
        if (isset($params[static::REASON_FILTER_KEY])) {
            $whereParts[] = "ReasonID = {$params[static::REASON_FILTER_KEY]}";
        }
        if (isset($params[static::YEAR_FILTER_KEY])) {
            if (isset($params[static::MONTH_FILTER_KEY])) {
                $lastDay = date('t', strtotime("{$params[static::YEAR_FILTER_KEY]}-{$params[static::MONTH_FILTER_KEY]}-01"));
                $whereParts[] = "Created BETWEEN '{$params[static::YEAR_FILTER_KEY]}-{$params[static::MONTH_FILTER_KEY]}-01' AND '{$params[static::YEAR_FILTER_KEY]}-{$params[static::MONTH_FILTER_KEY]}-{$lastDay}'";
            } else {
                $whereParts[] = "Created BETWEEN '{$params[static::YEAR_FILTER_KEY]}-01-01' AND '{$params[static::YEAR_FILTER_KEY]}-12-31'";
            }
        }
        $where = implode(' AND ', $whereParts);
        if (!empty($where)) {
            $where = "WHERE {$where}";
        }
        $months = DB::query("SELECT YEAR(Created) AS CreatedYear, MONTH(Created) AS CreatedMonth, ReasonID, COUNT(ID) AS TotalCount FROM {$tableDeletedCustomer} {$where} GROUP BY CreatedYear, CreatedMonth, ReasonID ORDER BY CreatedYear DESC, CreatedMonth DESC");
        $output = ArrayList::create();
        foreach ($months as $month) {
            $output->push(ArrayData::create([
                'Year'   => $month['CreatedYear'],
                'Month'  => $month['CreatedMonth'],
                'Reason' => $month['ReasonID'],
                'Total'  => $month['TotalCount'],
            ]));
        }
        return $output;
    }

    /**
     * Returns the columns.
     * 
     * @return array
     */
    public function columns() : array
    {
        $reasons = DeletedCustomerReason::get()->map('ID', 'Reason')->toArray();
        $report  = $this;
        
        return [
            'Year' => [
                'title'      => _t(Page::class . '.YEAR', 'Year'),
                'formatting' => function ($value, $item) use ($report) {
                    return sprintf(
                        '<a class="grid-field__link" href="%s" title="%s">%s</a>',
                        $report->getLink(
                            '?filters[' . $report::YEAR_FILTER_KEY . ']=' . $item->Year
                        ),
                        $item->Year,
                        $item->Year
                    );
                },
            ],
            'Month' => [
                'title'      => _t(Page::class . '.MONTH', 'Month'),
                'formatting' => function ($value, $item) use ($report) {
                    Tools::switchLocale(false);
                    $month = strftime('%B', strtotime(date("Y-{$item->Month}-01")));
                    Tools::switchLocale(false);
                    return sprintf(
                        '<a class="grid-field__link" href="%s" title="%s">%s</a>',
                        $report->getLink(
                            '?filters[' . $report::MONTH_FILTER_KEY . ']=' . $item->Month .
                            '&filters[' . $report::YEAR_FILTER_KEY . ']=' . $item->Year
                        ),
                        $item->Month,
                        $month
                    );
                },
            ],
            'Reason' => [
                'title'      => DeletedCustomerReason::singleton()->fieldLabel('Reason'),
                'formatting' => function ($value, $item) use ($report, $reasons) {
                    $reasonText = array_key_exists($item->Reason, $reasons) ? $reasons[$item->Reason] : _t(DeletedCustomerReason::class . '.DifferentReason', 'Different reason');
                    return sprintf(
                        '<a class="grid-field__link" href="%s" title="%s">%s</a>',
                        $report->getLink(
                            '?filters[' . $report::REASON_FILTER_KEY . ']=' . $item->Reason
                        ),
                        $reasonText,
                        $reasonText
                    );
                },
            ],
            'Total' => [
                'title' => _t(Product::class . '.QUANTITY', 'Quantity'),
            ],
        ];
    }
}
