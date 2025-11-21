<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "check_login.php";

$logged_in = isset($_SESSION['username']);

$links = [
    'index' => ['url' => '../html/index.html', 'text' => 'Home'],
    'prenota' => ['url' => '../html/prenota.html', 'text' => 'Prenota'],
    'about_us' => ['url' => '../html/about_us.html', 'text' => 'About us'],
    'dove_trovarci' => ['url' => '../html/dove_trovarci.html', 'text' => 'Dove trovarci'],
];

if (!$logged_in) {
    $links['login'] = ['url' => '../html/login.html', 'text' => 'Dona ora'];
    unset($links['prenota']);
} else {
    unset($links['login']);
}
$menu_html = '';
foreach ($links as $key => $link_data) {
    $is_current = isset($current_page) && $current_page === $key;

    if ($is_current) {
        $menu_html .= '<li class="active"><span>' . $link_data['text'] . '</span></li>';
    } else {
        $menu_html .= '<li><a href="' . $link_data['url'] . '">' . $link_data['text'] . '</a></li>';
    }
}
$profilo_key = $logged_in ? 'profilo' : 'login';
$profilo_url = $logged_in ? '../html/profilo.html' : '../html/login.html';
$profilo_title = $logged_in ? 'Vai al tuo profilo' : 'Accedi';
$is_profilo_current = isset($current_page) && $current_page === $profilo_key;
$icon_html = '<span aria-hidden="true" class="icona-profilo">👤</span>';
$profilo_html = '<li class="profilo-icona-container">';
if ($is_profilo_current) {
    $profilo_html .= '<span class="active">' . $icon_html . '</span>';
} else {
    $profilo_html .= '<a href="' . $profilo_url . '" title="' . $profilo_title . '">' . $icon_html . '</a>';
}
$profilo_html .= '</li>';

?>