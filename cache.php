<?php
$cache_lifetime = 3600; // Durée de vie du cache en secondes

function open_api($url) {
    global $cache_lifetime;
    $cache_file = 'cache/' . md5($url) . '.cache';

    if (file_exists($cache_file) && (filemtime($cache_file) + $cache_lifetime) > time()) {
        $data = file_get_contents($cache_file);
    } else {
        $data = file_get_contents($url);
        if ($data !== false) {
            file_put_contents($cache_file, $data);
        } else {
            throw new Exception("Impossible de se connecter à l'API.");
        }
    }
    return json_decode($data, true);
}
