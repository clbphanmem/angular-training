<?php

class PaymentController {
    public function index() {
        $data['categories'] = Category::getCategories(10);
        echo App::toJson($data);
    }

    public function create() {
        $data['errors'] = [];
        $data['messages'] = [];
        $name = Input::get('name');
        $order = Input::get('order');

        if (!$name) {
            $data['errors'][] = 'Chuyên mục không được bỏ trống';
        }


        if (!$order || !is_numeric($order)) {
            $order = 0;
        }

        if ($name && Category::getCategory($name)) {
            $data['errors'][] = 'Chuyên mục đã trùng, vui lòng chọn chuyên mục khác';
        }

        if (!$data['errors']) {
            Category::create([
                'name' => $name,
                'order' => $order
            ]);
            $data['messages'][] = 'Tạo chuyên mục "' . $name . '" thành công';
        }
        echo App::toJson($data);
    }

    public function update($id) {
        $data['errors'] = [];
        $data['messages'] = [];
        $name = Input::get('name');
        $order = Input::get('order');

        if (!$name) {
            $data['errors'][] = 'Chuyên mục không được bỏ trống';
        }

        if (!$order || !is_numeric($order)) {
            $order = 0;
        }

        $current_category = Category::find($id);
        $another_category = Category::getCategory($name);
        if ($name && $another_category && $another_category['id'] != $id) {
            $data['errors'][] = 'Chuyên mục đã trùng, vui lòng chọn chuyên mục khác';
        }

        if (!$data['errors']) {
            $current_category->name = $name;
            $current_category->order = $order;
            $current_category->save();
            $data['messages'][] = 'Cập nhật chuyên mục "' . $name . '" thành công';
        }
        echo App::toJson($data);
    }

    public function show($id) {
        $category = Category::getCategory($id);
        $data['errors'] = [];
        $data['messages'] = [];
        if ($category) {
            $data['category'] = $category;
        }
        echo App::toJson($data);
    }

    public function delete($id) {
        $data['errors'] = [];
        $data['messages'] = [];
        if (Input::get()) {
            $category = Category::find($id);
            $data['messages'][] = 'Xóa thành công chuyên mục "' . $category->name . '"';
            $category->delete();
        }
        echo App::toJson($data);
    }
}