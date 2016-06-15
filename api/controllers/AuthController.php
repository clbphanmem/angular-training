<?php

class AuthController {
	public function login() {
		$data['errors'] = [];
		$email = Input::get('email');
		$password = Input::get('password');

		if (!$email) {
			$data['errors'][] = 'Vui lòng nhập Email';
		}

		if (!$password) {
			$data['errors'][] = 'Vui lòng nhập Password';
		}

		if ($email && $password && !$data['errors']) {
			$user = User::getUser($email, true);
			if ($user && App::verifyPassword($password, $user['password'])) {
				if ($user['level'] == 1) {
					unset($user['password']);
					Session::update('user', [
						'id' => $user['id'],
						'email' => $user['email'],
						'level' => $user['level']
					]);
					$data['isLogin'] = true;
				} else {
					$data['isLogin'] = false;
					$data['errors'][] = 'Người dùng không đủ quyền hạn.';
				}
			} else {
				$data['isLogin'] = false;
				$data['errors'][] = 'Thông tin người dùng không chính xác.';
			}
		}

		echo App::toJson($data);
	}

	public function logout() {
		Session::destroy();
	}

	public function isLogin() {
		$data['isLogin'] = true;
		if (!User::isLogin()) {
			$data['isLogin'] = false;
			$data['errors'][] = 'Bạn không được phép truy cập';
		}
		echo App::toJson($data);
	}
}