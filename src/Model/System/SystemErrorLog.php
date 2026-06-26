<?php

namespace SilverCart\Model\System;

use SilverCart\Admin\Controllers\ModelAdmin_ReadonlyInterface;
use SilverCart\Model\Order\Order;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

/**
 * System error log for critical operational issues (e.g. email/payment setup).
 *
 * @package SilverCart
 * @subpackage Model_System
 * @author Sebastian Diel
 * @license see license file in modules root directory
 *
 * @property string $Area
 * @property string $Severity
 * @property string $Message
 * @property string $Context
 * @property string $ExceptionClass
 * @property string $ExceptionMessage
 * @property string $RequestURL
 * @property string $UserIP
 *
 * @method Member Member()
 * @method Order  Order()
 */
class SystemErrorLog extends DataObject implements ModelAdmin_ReadonlyInterface
{
    /**
     * DB attributes
     *
     * @var array
     */
    private static $db = [
        'Area'             => 'Enum("Email,Payment,System","System")',
        'Severity'         => 'Enum("Error,Critical","Error")',
        'Message'          => 'Text',
        'Context'          => 'Text',
        'ExceptionClass'   => 'Varchar(255)',
        'ExceptionMessage' => 'Text',
        'RequestURL'       => 'Text',
        'UserIP'           => 'Varchar(64)',
    ];
    /**
     * has one relations
     *
     * @var array
     */
    private static $has_one = [
        'Member' => Member::class,
        'Order'  => Order::class,
    ];
    /**
     * Default sort field and direction
     *
     * @var string
     */
    private static $default_sort = 'Created DESC';
    /**
     * DB table name
     *
     * @var string
     */
    private static $table_name = 'SilvercartSystemErrorLog';
    /**
     * Summary fields
     *
     * @var array
     */
    private static $summary_fields = [
        'Created'     => 'Date/Time',
        'Area'        => 'Area',
        'Severity'    => 'Severity',
        'Message'     => 'Message',
        'Member.Name' => 'Member',
        'Order.ID'    => 'Order',
    ];
    /**
     * Searchable fields
     *
     * @var array
     */
    private static $searchable_fields = [
        'Area',
        'Severity',
        'Message',
        'ExceptionMessage',
        'Member.Email',
        'Order.ID',
    ];

    public static function addEmailFailure($recipient, string $subject, \Throwable $exception, array $context = []) : ?SystemErrorLog
    {
        $context['recipient'] = $recipient;
        $context['subject'] = $subject;
        return self::add('Email', 'E-Mail Versand fehlgeschlagen', $context, $exception);
    }

    public static function addPaymentFailure(string $message, array $context = [], ?\Throwable $exception = null) : ?SystemErrorLog
    {
        return self::add('Payment', $message, $context, $exception);
    }

    public static function add(string $area, string $message, array $context = [], ?\Throwable $exception = null) : ?SystemErrorLog
    {
        try {
            $log = SystemErrorLog::create();
            $log->Area = $area;
            $log->Severity = 'Error';
            $log->Message = $message;
            if (!empty($context)) {
                $log->Context = json_encode($context);
            }
            if ($exception !== null) {
                $log->ExceptionClass = get_class($exception);
                $log->ExceptionMessage = $exception->getMessage();
            }
            if (Controller::curr()) {
                $request = Controller::curr()->getRequest();
                if ($request) {
                    $log->RequestURL = $request->getURL(true);
                    $log->UserIP = $request->getIP();
                }
            }
            $log->write();
            return $log;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function canCreate($member = null, $context = []) : bool
    {
        return false;
    }

    public function canEdit($member = null, $context = []) : bool
    {
        return false;
    }

    public function canDelete($member = null, $context = []) : bool
    {
        return false;
    }
}
