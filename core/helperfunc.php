<?php

function dd($value) {
    echo "<pre>";
    var_dump($value);
    echo "</pre>";

    die();
}

function urlIs($value) {
    return $_SERVER['REQUEST_URI'] === $value;
}

function base_path($path) {
    return BASE_PATH . $path;
}

function home_path($path) {
    return HOME_PATH . $path;
}

function view($path, $attributes = []) {
    extract($attributes);
    require home_path('views/' . $path);
}