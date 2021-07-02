<?php

namespace SilverCart\Admin\Forms\GridField;

use SilverCart\Admin\Forms\GridField\GridFieldBatchAction;
use SilverCart\Model\Order\Order;
use SilverStripe\Forms\GridField\GridField;

/**
 * Batch action to mark an order as seen.
 *
 * @package SilverCart
 * @subpackage Admin_Forms_GridField_BatchActions
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 22.09.2017
 * @copyright 2017 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class GridFieldBatchAction_MarkAsSeen extends GridFieldBatchAction
{
    /**
     * Handles the action.
     * 
     * @param GridField $gridField GridField to handle action for
     * @param array     $recordIDs Record IDs to handle action for
     * @param array     $data      Data to handle action for
     * 
     * @return void
     */
    public function handle(GridField $gridField, array $recordIDs, array $data) : void
    {
        foreach ($recordIDs as $orderID) {
            $order = Order::get()->byID($orderID);
            if ($order->exists()) {
                $order->markAsSeen();
            }
        }
    }
}