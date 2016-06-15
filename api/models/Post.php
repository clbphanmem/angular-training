<?php
class Post extends ActiveRecord\Model {
	static $belongs_to = [
		['category'],
		['user']
	];

	public static function getPost($post = '') {
		if (is_numeric($post)) {
			$post = self::find($post)->attributes();
		} else {
			$post = self::find('first', ['conditions' => ['title = ?', $post]]);
			$post = $post ? $post->attributes() : false;
		}

		if ($post) {		
			$post['date'] = date('d-m-Y H:i:s', strtotime($post['created_at']));
			return $post;
		}
		return false;
	}

	public static function getPosts($perPage = 10, $range = 2, $isBackend = true) {
		$page = Input::get('page');
		if (!$page || !is_numeric($page) || $page < 0) {
			$page = 1;
		}
		if ($perPage) {
			$posts = self::find('all', ['limit' => $perPage, 'offset' => self::getOffset($page, $perPage), 'order' => 'id desc']);
		} else {
			$posts = self::find('all');
		}

		if ($posts) {
			$data['data'] = [];
			foreach ($posts as $key => $post) {
				$data['data'][$key] = $post->attributes();
				$data['data'][$key]['category'] = $post->category->name;
				$data['data'][$key]['user'] = $post->user->email;
				$data['data'][$key]['date'] = date('d-m-Y H:i:s', strtotime($post->created_at));
				$data['data'][$key]['slug'] = Tool::VNConvert($post->title);
				if (!file_exists('../'. $data['data'][$key]['image'])) {
					$data['data'][$key]['image'] = 'uploads/no_image.jpg';
				}
			}
			if ($isBackend && $perPage && ($totalPages = self::getPages($perPage)) > 1) { 
				$data['pagination'] = Tool::pagination($totalPages, $perPage, $page, $range, function($currentPage) {
					return sprintf('#/admin/post/page/%s', $currentPage);
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

	public static function getPostsByCategory($id = '', $perPage = 10, $range = 2) {
		$page = Input::get('page');
		if (!$page || !is_numeric($page) || $page < 0) {
			$page = 1;
		}
		if ($perPage) {
			$posts = self::find('all', [
				'limit' => $perPage, 
				'offset' => self::getOffset($page, $perPage), 
				'order' => 'id desc',
				'conditions' => ['category_id = ?', $id]	
			]);
		} else {
			$posts = self::find('all');
		}

		if ($posts) {
			$data['data'] = [];
			foreach ($posts as $key => $post) {
				$data['data'][$key] = $post->attributes();
				$data['data'][$key]['category'] = $post->category->name;
				$data['data'][$key]['user'] = $post->user->email;
				$data['data'][$key]['date'] = date('d-m-Y H:i:s', strtotime($post->created_at));
				$data['data'][$key]['slug'] = Tool::VNConvert($post->title);
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