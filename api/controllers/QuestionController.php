<?php

class QuestionController {
    public function index() {
        $data['posts'] = Post::getPosts(10);
        echo App::toJson($data);
    }

    public function create() {
        $data['errors'] = [];
        $data['messages'] = [];
        $title = Input::get('title');
        $intro = Input::get('intro');
        $content = Input::get('content');
        $category_id = Input::get('category_id');
        $image = Session::get('hash');

        if (!$title) {
            $data['errors'][] = 'Tên bài viết không được bỏ trống';
        }

        if ($title && Post::getPost($title)) {
            $data['errors'][] = 'Tên bài viết đã trùng, vui lòng chọn bài viết khác';
        }

        if (!$intro) {
            $data['errors'][] = 'Mô tả bài viết không được bỏ trống';
        }

        if (!$content) {
            $data['errors'][] = 'Nội dung bài viết không được bỏ trống';
        }


        if (!$category_id) {
            $data['errors'][] = 'Vui lòng chọn chuyên mục dùm cái!';
        }

        if (!$data['errors']) {
            Post::create([
                'title' => $title,
                'intro' => $intro,
                'content' => $content,
                'image' => $image,
                'category_id' => $category_id,
                'user_id' => Session::get('user')['id']
            ]);
            $data['messages'][] = 'Tạo bài viết "' . $title . '" thành công';
        }
        echo App::toJson($data);
    }

    public function update($id) {
        $data['errors'] = [];
        $data['messages'] = [];
        $title = Input::get('title');
        $intro = Input::get('intro');
        $content = Input::get('content');
        $category_id = Input::get('category_id');
        $image = Input::get('image');

        if (!$title) {
            $data['errors'][] = 'Tên bài viết không được bỏ trống';
        }

        $current_post = Post::find($id);
        $another_post = Post::getPost($title);
        if ($title && $another_post && $another_post['id'] != $id) {
            $data['errors'][] = 'Bài viết đã trùng, vui lòng chọn bài viết khác';
        }

        if (!$intro) {
            $data['errors'][] = 'Mô tả bài viết không được bỏ trống';
        }

        if (!$content) {
            $data['errors'][] = 'Nội dung bài viết không được bỏ trống';
        }


        if (!$category_id) {
            $data['errors'][] = 'Vui lòng chọn chuyên mục dùm cái!';
        }

        if (!$data['errors']) {
            $current_post->title = $title;
            $current_post->intro = $intro;
            $current_post->content = $content;
            $current_post->category_id = $category_id;
            if ($current_post->image != $image) {
                if ($current_post->image && file_exists("../$current_post->image")) {
                    unlink("../$current_post->image");
                }
                $current_post->image = $image;
            }
            $current_post->save();
            $data['messages'][] = 'Cập nhật bài viết "' . $title . '" thành công';
        }
        echo App::toJson($data);
    }

    public function show($id) {
        $post = Post::getPost($id);
        $categories = Category::getCategories(false);
        Session::update('hash', App::getHash());
        $data['errors'] = [];
        $data['messages'] = [];
        if ($post) {
            $data['post'] = $post;
            $data['categories'] = $categories;
        }
        echo App::toJson($data);
    }

    public function delete($id) {
        $data['errors'] = [];
        $data['messages'] = [];
        if (Input::get()) {
            $post = Post::find($id);
            $data['messages'][] = 'Xóa thành công bài viết "' . $post->title . '"';
            $post->delete();
        }
        echo App::toJson($data);
    }

    public function creator() {
        $data['categories'] = Category::getCategories(false);
        Session::update('hash', App::getHash());
        echo App::toJson($data);
    }

    public function upload() {
        $data['errors'] = [];
        $hash = Session::get('hash');
        if (!$hash) {
            $data['errors'][] = 'Có lỗi xảy ra';
        } else {
            $file = $_FILES['file'];
            $verify = Upload::verify($file);
            if ($verify !== true) {
                switch ($verify) {
                    case 'maxsize':
                        $data['errors'][] = 'Dung lượng quá mức cho phép';
                        break;
                    case 'mimes':
                        $data['errors'][] = 'Định dạng không cho phép';
                        break;
                }
            }

            if (!$data['errors']) {
                $data['file']['temp'] = Upload::put($file, '../uploads/temp');
                $data['file']['name'] = $file['name'];
                $data['file']['hash'] = $hash;
                $time = time();
                // Tạo sự kiện
                Event::create([
                    'name' => 'image',
                    'action' => 'save',
                    'data' => json_encode($data['file']),
                    'created_at' => Tool::convertToTimestamp($time),
                    'expired_at' => Tool::convertToTimestamp($time + Event::$eventExpired),
                ]);
            }
        }
        echo App::toJson($data);
    }
}