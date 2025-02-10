<?php

namespace App\Core;

class Session
{
    public static function start() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start(); 
        }
    }

    public static function set(string $key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function delete(string $key)
    {
        unset($_SESSION[$key]);
    }

    public static function destroy()
    {
        session_destroy();
        $_SESSION = [];
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
}