<?php
global $system;

$_SESSION = array();
if(ini_get(option: 'session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}
session_destroy();
header(header: "Location: {$system->router->base_url}");