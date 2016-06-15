<?php

class Customer extends ActiveRecord\Model {
    static $has_many = [
        ['payments']
    ];

    public static function getCustomer($customer = '') {
        if (is_numeric($customer)) {
            $customer = self::find($customer)->attributes();
        } else {
            $customer = self::find('first', ['conditions' => ['name = ?', $customer]]);
            $customer = $customer ? $customer->attributes() : false;
        }

        if ($customer) {
            return $customer;
        }
        return false;
    }

    public static function getCustomers($perPage = 10, $range = 2) {
        $page = Input::get('page');
        if (!$page || !is_numeric($page) || $page < 0) {
            $page = 1;
        }
        if ($perPage) {
            $customers = self::find('all', ['limit' => $perPage, 'offset' => self::getOffset($page, $perPage)]);
        } else {
            $customers = self::find('all');
        }

        if ($customers) {
            $data['data'] = [];
            foreach ($customers as $key => $customer) {
                $data['data'][$key] = $customer->attributes();
            }
            if ($perPage && ($totalPages = self::getPages($perPage)) > 1) {
                $data['pagination'] = Tool::pagination($totalPages, $perPage, $page, $range, function($currentPage) {
                    return sprintf('#/admin/customer/page/%s', $currentPage);
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