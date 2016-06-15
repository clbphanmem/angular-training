<?php
class Answer extends ActiveRecord\Model {
    static $belongs_to = [
        ['category'],
        ['user']
    ];

    public static function getAnswer($answer = '') {
        if (is_numeric($answer)) {
            $answer = self::find($answer)->attributes();
        } else {
            $answer = self::find('first', ['conditions' => ['title = ?', $answer]]);
            $answer = $answer ? $answer->attributes() : false;
        }

        if ($answer) {
            $answer['date'] = date('d-m-Y H:i:s', strtotime($answer['created_at']));
            return $answer;
        }
        return false;
    }

    public static function getAnswers($perPage = 10, $range = 2, $isBackend = true) {
        $page = Input::get('page');
        if (!$page || !is_numeric($page) || $page < 0) {
            $page = 1;
        }
        if ($perPage) {
            $answers = self::find('all', ['limit' => $perPage, 'offset' => self::getOffset($page, $perPage), 'order' => 'id desc']);
        } else {
            $answers = self::find('all');
        }

        if ($answers) {
            $data['data'] = [];
            foreach ($answers as $key => $answer) {
                $data['data'][$key] = $answer->attributes();
                $data['data'][$key]['category'] = $answer->category->name;
                $data['data'][$key]['user'] = $answer->user->email;
                $data['data'][$key]['date'] = date('d-m-Y H:i:s', strtotime($answer->created_at));
                $data['data'][$key]['slug'] = Tool::VNConvert($answer->title);
                if (!file_exists('../'. $data['data'][$key]['image'])) {
                    $data['data'][$key]['image'] = 'uploads/no_image.jpg';
                }
            }
            if ($isBackend && $perPage && ($totalPages = self::getPages($perPage)) > 1) {
                $data['pagination'] = Tool::pagination($totalPages, $perPage, $page, $range, function($currentPage) {
                    return sprintf('#/admin/answer/page/%s', $currentPage);
                });
            } elseif (!$isBackend && $perPage && ($totalPages = self::getPages($perPage)) > 1) {
                $data['pagination'] = Tool::pagination($totalPages, $perPage, $page, $range, function($currentPage) {
                    return sprintf('#/page/%s', $currentPage);
                });
            }
            return $data;
        }
        return false;
    }

    public static function getAnswersByCategory($id = '', $perPage = 10, $range = 2) {
        $page = Input::get('page');
        if (!$page || !is_numeric($page) || $page < 0) {
            $page = 1;
        }
        if ($perPage) {
            $answers = self::find('all', [
                'limit' => $perPage,
                'offset' => self::getOffset($page, $perPage),
                'order' => 'id desc',
                'conditions' => ['category_id = ?', $id]
            ]);
        } else {
            $answers = self::find('all');
        }

        if ($answers) {
            $data['data'] = [];
            foreach ($answers as $key => $answer) {
                $data['data'][$key] = $answer->attributes();
                $data['data'][$key]['category'] = $answer->category->name;
                $data['data'][$key]['user'] = $answer->user->email;
                $data['data'][$key]['date'] = date('d-m-Y H:i:s', strtotime($answer->created_at));
                $data['data'][$key]['slug'] = Tool::VNConvert($answer->title);
                if (!file_exists('../'. $data['data'][$key]['image'])) {
                    $data['data'][$key]['image'] = 'uploads/no_image.jpg';
                }
            }
            if ($perPage && ($totalPages = self::getPages($perPage, ['conditions' => ['category_id = ?', $id]])) > 1) {
                $data['pagination'] = Tool::pagination($totalPages, $perPage, $page, $range, function($currentPage) use ($id) {
                    return sprintf('#/category/%s/page/%s', $id, $currentPage);
                });
            }
            return $data;
        }
        return false;
    }

    public static function getPages($perPage = 10, $conditions = []) {
        if ($conditions) {
            return ceil(self::count($conditions) / $perPage);
        } else {
            return ceil(self::count() / $perPage);
        }
    }

    public static function getOffset($page = 1, $perPage = 10) {
        return ($page - 1)*$perPage;
    }
}