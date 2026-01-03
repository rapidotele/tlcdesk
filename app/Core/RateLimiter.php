<?php

namespace App\Core;

class RateLimiter {
    private $file;

    public function __construct() {
        $this->file = ROOT_PATH . '/storage/logs/ratelimit.json';
        if (!file_exists($this->file)) {
            file_put_contents($this->file, json_encode([]));
        }
    }

    public function check($key, $limit = 5, $seconds = 60) {
        $data = json_decode(file_get_contents($this->file), true);
        $now = time();

        // Cleanup old
        foreach ($data as $k => $attempts) {
            $data[$k] = array_filter($attempts, function($ts) use ($now, $seconds) {
                return $ts > ($now - $seconds);
            });
            if (empty($data[$k])) unset($data[$k]);
        }

        if (isset($data[$key]) && count($data[$key]) >= $limit) {
            file_put_contents($this->file, json_encode($data));
            return false;
        }

        $data[$key][] = $now;
        file_put_contents($this->file, json_encode($data));
        return true;
    }
}
