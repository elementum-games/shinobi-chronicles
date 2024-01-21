<?php
global $system;

$login_error_text = "";
$login_message_text = "";
$register_error_text = "";
$reset_error_text = "";
$initial_home_view = "none";
$register_pre_fill = [];
$home_links = [];
$home_links['news_api'] = $system->router->api_links['news'];
$home_links['logout'] = $system->router->base_url . "?logout=1";
$home_links['profile'] = $system->router->getUrl('profile');
$home_links['github'] = $system->router->links['github'];
$home_links['discord'] = $system->router->links['discord'];
$home_links['support'] = $system->router->base_url . "support.php";

requrie_once ('login.php');
reuqire_once ('new_register.php');
require ('/templates/home.php');
