<?php

class Payment extends ActiveRecord\Model {
    static $belongs_to = [
        ['customer']
    ];

    public static function getPayment($payment = '') {
        if (is_numeric($payment)) {
            $payment = self::find($payment)->attributes();
        } else {
            $payment = self::find('first', ['conditions' => ['name = ?', $payment]]);
            $payment = $payment ? $payment->attributes() : false;
        }

        if ($payment) {
            return $payment;
        }
        return false;
    }

    public static function getPayments($perPage = 10, $range = 2) {
        $page = Input::get('page');
        if (!$page || !is_numeric($page) || $page < 0) {
            $page = 1;
        }
        if ($perPage) {
            $payments = self::find('all', ['limit' => $perPage, 'offset' => self::getOffset($page, $perPage)]);
        } else {
            $payments = self::find('all');
        }

        if ($payments) {
            $data['data'] = [];
            foreach ($payments as $key => $payment) {
                $data['data'][$key] = $payment->attributes();
            }
            if ($perPage && ($totalPages = self::getPages($perPage)) > 1) {
                $data['pagination'] = Tool::pagination($totalPages, $perPage, $page, $range, function($currentPage) {
                    return sprintf('#/admin/payment/page/%s', $currentPage);
                });
            }
            return $data;
        }
        return false;
    }

    public static function getPages($perPage = 10) {
        return ceil(self::count() / $perPage);
    }

    public static function getOffset($page = 1, $perPage = 10) {
        return ($page - 1)*$perPage;
    }
}