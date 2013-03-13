<?php

/*
* This file is part of the Jirafe.
* (c) Jirafe <http://www.jirafe.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

/**
 * Jirafe Event Validator
 *
 * @author Fooman Ltd
 */
class Jirafe_Event_Validator
{

    public function run($event, $throwException = false)
    {
        try {
            $decodedEvent = $this->validateBasics($event);
            $this->validateVersion($decodedEvent['v']);
            $this->validateAction($decodedEvent['a']);
            $this->validateData($decodedEvent['a'], $decodedEvent['d']);
        } catch (Jirafe_Exception $e) {
            if ($throwException) {
                throw new Jirafe_Exception ($e->getMessage());
            }
            return false;
        }
        return true;
    }

    protected function validateBasics($event)
    {
        if (is_array($event)) {
            throw new Jirafe_Exception('Event is expected in JSON format');
        }
        $decodedEvent = json_decode($event, true);
        if (is_null($decodedEvent)) {
            throw new Jirafe_Exception('Error in reading JSON');
        }
        if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new Jirafe_Exception('Error in reading JSON');
            }
        }
        if (!isset($decodedEvent['v'])) {
            throw new Jirafe_Exception('Event requires a version');
        }
        if (!isset($decodedEvent['a'])) {
            throw new Jirafe_Exception('Event requires an action');
        }
        if (!isset($decodedEvent['d'])) {
            throw new Jirafe_Exception('Event requires data');
        }
        return $decodedEvent;
    }

    protected function validateVersion($version)
    {
        if (!is_int($version)) {
            throw new Jirafe_Exception('Version is not an integer');
        }
        if ($version < 0) {
            throw new Jirafe_Exception('Version is negative');
        }
    }

    protected function validateTime($data)
    {
        if (isset($data['time']) && !empty($data['time'])) {
            if (!is_int($data['time']) || $data['time'] < 0) {
                throw new Jirafe_Exception('Time is not a Unix UTC timestamp');
            }
        }
    }

    protected function validateAction($action)
    {
        $supportedActions = array('orderCreate', 'orderUpdate', 'orderImport', 'refundCreate', 'refundImport', 'noop');
        if (!in_array($action, $supportedActions)) {
            throw new Jirafe_Exception('Action is not supported');
        }
    }

    protected function validateData($action, $data)
    {
        if ($action != 'noop' && empty($data)) {
            throw new Jirafe_Exception('Missing Data');
        }
        switch ($action) {
            case 'orderCreate':
                $this->validateOrderCreate($data);
                break;
            case 'orderUpdate':
                $this->validateOrderUpdate($data);
                break;
            case 'orderImport':
                $this->validateOrderImport($data);
                break;
            case 'refundCreate':
                $this->validateRefundCreate($data);
                break;
            case 'refundImport':
                $this->validateRefundImport($data);
                break;
        }
    }

    protected function validateOrderCreate($data)
    {
        $requiredFields = array('orderId', 'status', 'customerHash', 'time', 'grandTotal');
        foreach ($requiredFields as $requiredField) {
            if (!isset($data[$requiredField]) || empty ($data[$requiredField])) {
                throw new Jirafe_Exception('Required field ' . $requiredField . ' is missing or empty');
            }
        }
        if (strlen($data['customerHash']) != 32) {
            throw new Jirafe_Exception('customerHash is not in the expected format (md5)');
        }
        if (strlen($data['visitorId']) != 16 && !is_null($data['visitorId'])) {
            throw new Jirafe_Exception('visitorId is not in the expected format (md5)');
        }
        $this->validateTime($data);
        $this->validateAmounts($data);
        $this->validateItems($data);
        $this->validateStatus($data['status']);
    }

    protected function validateOrderUpdate($data)
    {
        if (!isset($data['orderId']) || empty ($data['orderId'])) {
            throw new Jirafe_Exception('Required field orderId is missing or empty');
        }

        $oneExtra = false;
        $extraFields = array('status', 'time', 'customerHash', 'grandTotal', 'subTotal', 'taxAmount', 'shippingAmount', 'discountAmount', 'items', 'customToken', 'customData');
        foreach ($extraFields as $extraField) {
            if (isset($data[$extraField]) && !empty($data[$extraField])) {
                $oneExtra = true;
            }
        }
        if (!$oneExtra) {
            throw new Jirafe_Exception('Update needs at least one of these fields ' . implode(',', $extraFields));
        }
        if (isset($data['status']) && !empty($data['status'])) {
            $this->validateStatus($data['status']);
        }
        $this->validateTime($data);
        $this->validateAmounts($data);
        $this->validateItems($data);
    }

    protected function validateOrderImport($data)
    {
        if (!isset($data['orders']) || empty($data['orders'])) {
            throw new Jirafe_Exception('Order events are missing');
        }
        foreach ($data['orders'] as $singleOrder) {
            $this->validateOrderCreate($singleOrder);
        }
    }

    protected function validateRefundCreate($data)
    {
        $requiredFields = array('refundId', 'orderId', 'time', 'grandTotal');
        foreach ($requiredFields as $requiredField) {
            if (!isset($data[$requiredField]) || empty ($data[$requiredField])) {
                throw new Jirafe_Exception('Required field ' . $requiredField . ' is missing or empty');
            }
        }
        $this->validateTime($data);
        $this->validateAmounts($data);
        $this->validateItems($data);
    }

    protected function validateRefundImport($data)
    {
        if (!isset($data['refunds']) || empty($data['refunds'])) {
            throw new Jirafe_Exception('Refund events are missing');
        }
        foreach ($data['refunds'] as $singleRefund) {
            $this->validateRefundCreate($singleRefund);
        }
    }

    protected function validateStatus($status)
    {
        $supportedStatuses = array('new', 'pendingPayment', 'processing', 'complete', 'closed', 'canceled', 'held', 'paymentReview', 'unknown');
        if (!in_array($status, $supportedStatuses)) {
            throw new Jirafe_Exception('Status ' . $status . ' is not one of ' . implode(',', $supportedStatuses));
        }
    }

    protected function validateAmounts($data)
    {
        $amounts = array('grandTotal', 'subTotal', 'taxAmount', 'shippingAmount', 'discountAmount');
        foreach ($amounts as $amount) {
            if (isset ($data[$amount])) {
                if (!$this->validateAmount($data[$amount])) {
                    throw new Jirafe_Exception($amount . ' is not in the format xxx.yyyy');
                }
            }
        }
    }

    protected function validateAmount($amount)
    {
        return preg_match('/^[0-9]+\.[0-9]{4}$/im', $amount);
    }

    protected function validateStrings($data)
    {
        $stringFields = array('orderId', 'refundId', 'customerHash', 'visitorId');
        foreach ($stringFields as $stringField) {
            if (isset ($data[$stringField])) {
                if (!$this->validateEncoding($data[$stringField])) {
                    throw new Jirafe_Exception($stringField . ' is not UTF-8 encoded');
                }
            }
        }
    }

    protected function validateEncoding($string)
    {
        return $string == @iconv('UTF-8', 'UTF-8', $string);
    }

    protected function validateItems($data)
    {
        $requiredFields = array('sku', 'name', 'price', 'quantity');
        $stringFields = array('sku', 'name', 'category');
        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                foreach ($requiredFields as $requiredField) {
                    if (!isset($item[$requiredField]) || empty($requiredField)) {
                        throw new Jirafe_Exception($requiredField . ' is missing for item');
                    }
                }
                foreach ($stringFields as $stringField) {
                    if (isset($item[$stringField])) {
                        if (!$this->validateEncoding($item[$stringField])) {
                            throw new Jirafe_Exception($stringField . ' is not UTF-8 encoded');
                        }
                    }
                }
            }
        }
    }
}
