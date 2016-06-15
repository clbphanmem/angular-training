<?php

class UserController {
	public function index() {
		$data['users'] = User::getUsers(false, 10);
		echo App::toJson($data);
	}

	public function create() {
		$data['errors'] = [];
		$data['messages'] = [];
		$email = Input::get('email');
		$password = Input::get('password');
		$confirm_password = Input::get('confirm_password');
		$level = Input::get('level');

		if (!$email) {
			$data['errors'][] = 'Email không được bỏ trống';
		}

		if ($email && User::getUser($email)) {
			$data['errors'][] = 'Email đã trùng, vui lòng chọn Email khác';
		}

		if (!$password || !$confirm_password || $password != $confirm_password) {
			$data['errors'][] = 'Mật khẩu không trùng hoặc bỏ trống';
		}

		if (!$level) {
			$data['errors'][] = 'Good bye, Hacker!!!';
		}

		if (!$data['errors']) {
			User::create([
				'email' => $email,
				'password' => App::encryptPassword($password),
				'level' => $level
			]);
			$data['messages'][] = 'Tạo người dùng "' . $email . '" thành công';
		}
		echo App::toJson($data);
	}

	public function update($id) {
		$data['errors'] = [];
		$data['messages'] = [];
		$email = Input::get('email');
		$password = Input::get('password');
		$confirm_password = Input::get('confirm_password');
		$level = Input::get('level');

		if (!$email) {
			$data['errors'][] = 'Email không được bỏ trống';
		}

		$current_user = User::find($id);
		$another_user = User::getUser($email);
		if ($email && $another_user && $another_user['id'] != $id) {
			$data['errors'][] = 'Email đã trùng, vui lòng chọn Email khác';
		}

		if ($password && $password != $confirm_password) {
			$data['errors'][] = 'Mật khẩu không trùng hoặc bỏ trống';
		}

		if (!$level) {
			$data['errors'][] = 'Good bye, Hacker!!!';
		}

		if (!$data['errors']) {
			$current_user->email = $email;
			if ($password && $confirm_password) {
				$current_user->password = App::encryptPassword($password);
			}			
			$current_user->level = $level;
			$current_user->save();
			$data['messages'][] = 'Cập nhật người dùng "' . $email . '" thành công';
		}
		echo App::toJson($data);
	}

	public function show($id) {
		$user = User::getUser($id);
		$data['errors'] = [];
		$data['messages'] = [];
		if ($user) {
			$data['user'] = $user;
		}
		echo App::toJson($data);
	}

	public function delete($id) {
		$data['errors'] = [];
		$data['messages'] = [];
		if (Input::get()) {
			$user = User::find($id);
			$data['messages'][] = 'Xóa thành công người dùng "' . $user->email . '"';
			$user->delete();
		}
		echo App::toJson($data);
	}
}