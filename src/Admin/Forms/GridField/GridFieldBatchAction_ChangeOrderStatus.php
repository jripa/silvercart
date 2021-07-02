<?php

namespace SilverCart\Admin\Forms\GridField;

use SilverCart\Model\Order\Order;
use SilverCart\Model\Order\OrderStatus;
use SilverCart\Admin\Forms\GridField\GridFieldBatchAction;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\ORM\FieldType\DBHTMLText;

/**
 * Batch action to change an orders status.
 *
 * @package SilverCart
 * @subpackage Admin_Forms_GridField_BatchActions
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 22.09.2017
 * @copyright 2017 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class GridFieldBatchAction_ChangeOrderStatus extends GridFieldBatchAction
{
    /**
     * Returns the markup of the callback form fields.
     * 
     * @return string
     */
    public function getCallbackFormFields() : DBHTMLText
    {
        return $this->render([
            'Dropdown' => $this->getDataObjectAsDropdownField(OrderStatus::class),
        ]);
    }
    
    /**
     * Is used to call javascript requirements of an action.
     * 
     * @return void
     */
    public function RequireJavascript() : void
    {
        $this->RequireDefaultJavascript();
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
    public function handle(GridField $gridField, array $recordIDs, array $data) : void
    {
        $orderStatusID = $data[Convert::raw2att(OrderStatus::class)];
        foreach ($recordIDs as $orderID) {
            $order = Order::get()->byID($orderID);
            if ($order->exists()) {
                $order->OrderStatusID = $orderStatusID;
                $order->write();
                $order->markAsSeen();
            }
        }
    }
}