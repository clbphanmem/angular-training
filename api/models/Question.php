<?php
class Question extends ActiveRecord\Model {
    static $belongs_to = [
        ['category'],
        ['user']
    ];

    public static function getQuestion($question = '') {
        if (is_numeric($question)) {
            $question = self::find($question)->attributes();
        } else {
            $question = self::find('first', ['conditions' => ['title = ?', $question]]);
            $question = $question ? $question->attributes() : false;
        }

        if ($question) {
            $question['date'] = date('d-m-Y H:i:s', strtotime($question['created_at']));
            return $question;
        }
        return false;
    }

    public static function getQuestions($perPage = 10, $range = 2, $isBackend = true) {
        $page = Input::get('page');
        if (!$page || !is_numeric($page) || $page < 0) {
            $page = 1;
        }
        if ($perPage) {
            $questions = self::find('all', ['limit' => $perPage, 'offset' => self::getOffset($page, $perPage), 'order' => 'id desc']);
        } else {
            $questions = self::find('all');
        }

        if ($questions) {
            $data['data'] = [];
            foreach ($questions as $key => $question) {
                $data['data'][$key] = $question->attributes();
                $data['data'][$key]['category'] = $question->category->name;
                $data['data'][$key]['user'] = $question->user->email;
                $data['data'][$key]['date'] = date('d-m-Y H:i:s', strtotime($question->created_at));
                $data['data'][$key]['slug'] = Tool::VNConvert($question->title);
                if (!file_exists('../'. $data['data'][$key]['image'])) {
                    $data['data'][$key]['image'] = 'uploads/no_image.jpg';
                }
            }
            if ($isBackend && $perPage && ($totalPages = self::getPages($perPage)) > 1) {
                $data['pagination'] = Tool::pagination($totalPages, $perPage, $page, $range, function($currentPage) {
                    return sprintf('#/admin/question/page/%s', $currentPage);
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

    public static function getQuestionsByCategory($id = '', $perPage = 10, $range = 2) {
        $page = Input::get('page');
        if (!$page || !is_numeric($page) || $page < 0) {
            $page = 1;
        }
        if ($perPage) {
            $questions = self::find('all', [
                'limit' => $perPage,
                'offset' => self::getOffset($page, $perPage),
                'order' => 'id desc',
                'conditions' => ['category_id = ?', $id]
            ]);
        } else {
            $questions = self::find('all');
        }

        if ($questions) {
            $data['data'] = [];
            foreach ($questions as $key => $question) {
                $data['data'][$key] = $question->attributes();
                $data['data'][$key]['category'] = $question->category->name;
                $data['data'][$key]['user'] = $question->user->email;
                $data['data'][$key]['date'] = date('d-m-Y H:i:s', strtotime($question->created_at));
                $data['data'][$key]['slug'] = Tool::VNConvert($question->title);
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