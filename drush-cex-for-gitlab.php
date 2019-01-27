#!/usr/bin/env php
<?php

class Gitlab {
    function __construct($domain, $token) {
        $this->domain = $domain;
        $this->token = $token;
    }
    function ping() {

    }

    function curl($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . $this->domain . '/api/v4'
            . $url . '?private_token=' . $this->token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        return json_decode($data);
    }
    function me() {
        return $this->curl('/user');
    }
    function cex() {
        shell_exec();
    }
}

$gitlab = new Gitlab(getenv('GITLAB_DOMAIN'), getenv('GITLAB_TOKEN'));

var_dump($gitlab->me());


