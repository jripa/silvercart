<?php

namespace SilverCart\Admin\Forms\GridField;

use SilverCart\Admin\Model\Config;
use SilverCart\Model\Order\Order;
use SilverCart\ORM\FieldType\DBMoney;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\FieldType\DBField;

class GridFieldOrderTotalSummary implements GridField_HTMLProvider
{
    public function getHTMLFragments($gridField): array
    {
        if (!$gridField instanceof GridField) {
            return [];
        }

        if ($gridField->getModelClass() !== Order::class) {
            return [];
        }

        $state = $gridField->getState();
        $filters = $state->GridFieldFilterHeader->Columns->toArray() ?? [];
        $hasFilters = false;
        foreach ($filters as $value) {
            if ($value !== null && $value !== '') {
                $hasFilters = true;
                break;
            }
        }
        if (!$hasFilters) {
            return [];
        }

        $list = $gridField->getManipulatedList();
        if (!$list instanceof DataList) {
            return [];
        }

        $amount = (float) $list->sum('AmountTotalAmount');
        $currency = Config::DefaultCurrency();
        $money = DBField::create_field(DBMoney::class, [
            'Currency' => $currency,
            'Amount'   => $amount,
        ]);
        $label = _t(self::class . '.TOTAL_AMOUNT', 'Total amount (filtered)');
        $html = sprintf(
            '<div type="button" class="btn btn-secondary btn-sm silvercart-order-total-summary" disabled="disabled">%s: %s</div>',
            $label,
            $money->Nice()
        );

        return [
            'buttons-before-left' => $html,
        ];
    }
}
