<?php

class Event extends ActiveRecord\Model {
    /**
     * Các phương thức hợp lệ
     * @var array
     */
    protected static $eventTypes = [
        'image' => ['save', 'remove']
    ];
    public static $eventExpired = 10; //seconds

    /**
     * Thực hiện các Event
     */
    public static function disPatcher() {
        $events = self::getEvents();
        self::callFunc($events);
    }


    /**
     * Lấy toàn bộ Event
     * @return Object
     */
    public static function getEvents() {
        return self::all(['order' => 'id desc']);
    }

    /**
     * @param $events Object Đối tượng chứa Event
     */
    public static function callFunc($events) {
        foreach ($events as $event) {
            // Event vẫn còn thời hạn
            if (time() <= strtotime($event->expired_at)) {
                if (array_key_exists($event->name, self::$eventTypes) && in_array($event->action, self::$eventTypes[$event->name])) {
                    if (call_user_func_array(['Event', "{$event->name}_{$event->action}"], [$event->data])) {
                        // Sau khi thực hiện thành công sẽ xóa Event
                        $event->delete();
                    }
                }
            } else {
            // Nếu như Event hết hạn
                if (array_key_exists($event->name, self::$eventTypes) && in_array($event->action, self::$eventTypes[$event->name])) {
                    if (call_user_func_array(['Event', "{$event->name}_remove"], [$event->data])) {
                        // Sau khi thực hiện thành công sẽ xóa Event
                        $event->delete();
                    }
                }
            }
        }
    }


    /**
     * Phương thức lưu hình ảnh từ thư mục tạm
     * @param $data String Đối số mặc định
     * @return bool
     * @throws \ActiveRecord\RecordNotFound
     */
    public static function image_save($data) {
        $data = json_decode($data);
        $post = Post::find('first', array('conditions' => ['image = ?', $data->hash]));
        if ($post) {
            $url = Upload::moveWithDate("../{$data->temp}", "../uploads");
            $post->image = $url;
            $post->save();
            return true;
        }
        return false;
    }

    /**
     * Phương thức xóa hình ảnh sau khi hết hạn
     * @param $data String Đối số mặc định
     * @return bool
     */
    public static function image_remove($data) {
        $data = json_decode($data);
        unlink("../$data->temp");
        return true;
    }
}