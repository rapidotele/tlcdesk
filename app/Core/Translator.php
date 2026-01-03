<?php

namespace App\Core;

class Translator {
    private static $instance = null;
    private $locale = 'en';
    private $messages = [];
    private $fallback = 'en';

    private function __construct() {}

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function setLocale($locale) {
        $this->locale = $locale;
        $this->loadMessages($locale);
        // Also load fallback if different
        if ($locale !== $this->fallback) {
            $this->loadMessages($this->fallback);
        }
    }

    public function getLocale() {
        return $this->locale;
    }

    private function loadMessages($locale) {
        if (isset($this->messages[$locale])) return;

        $file = ROOT_PATH . "/locales/$locale/messages.json";
        if (file_exists($file)) {
            $json = file_get_contents($file);
            $this->messages[$locale] = json_decode($json, true) ?? [];
        } else {
            $this->messages[$locale] = [];
        }
    }

    public function get($key, $params = []) {
        $text = $key;

        // Try current locale
        if (isset($this->messages[$this->locale][$key])) {
            $text = $this->messages[$this->locale][$key];
        }
        // Try fallback
        elseif (isset($this->messages[$this->fallback][$key])) {
            $text = $this->messages[$this->fallback][$key];
        }

        // Replace params
        foreach ($params as $k => $v) {
            $text = str_replace(":$k", $v, $text);
        }

        return $text;
    }

    // Static helper
    public static function t($key, $params = []) {
        return self::getInstance()->get($key, $params);
    }
}
