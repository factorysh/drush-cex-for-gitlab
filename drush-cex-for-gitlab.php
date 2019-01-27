#!/usr/bin/env php
<?php

class Gitlab {
    function __construct($domain, $token, $project) {
        $this->domain = $domain;
        $this->token = $token;
        $this->project = $project;
    }
    function curl($command, $url, $body=nil) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . $this->domain . '/api/v4'
            . $url . '?private_token=' . $this->token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        if($command == 'POST') {
            $payload = json_encode($body);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        }
        $data = curl_exec($ch);
        curl_close($ch);
        return json_decode($data);
    }
    function get($url) {
        return $this->curl('GET', $url);
    }
    function post($url, $body) {
        return $this->curl('POST', $url, $body);
    }
    function me() {
        return $this->get('/user');
    }
    function cex() {
        $secret = base64_encode(random_bytes(32));
        $me = $this->me(); // Who I am?
        shell_exec("drush config:export --destination /tmp/$secret");
        $settings = file_get_contents("/tmp/$secret/settings.php");
        $branch = "cex_$secret";
        //https://docs.gitlab.com/ce/api/commits.html#create-a-commit-with-multiple-files-and-actions
        $this->post('/projects/' . urlencode($this->project) . '/repository/commits', array(
            'author_email' => $me['email'],
            'author_name' => $me['name'],
            'commit_message' => 'Let me commit that for you',
            'branch' => $branch,
            'start_branch' => 'master',
            'actions' => array(
                array(
                    'action' => 'update',
                    'file_path' => 'settings.php',
                    'content' => $settings,
                    )
            )
        ));
        //https://docs.gitlab.com/ce/api/merge_requests.html#create-mr
        $this->post('/projects/' . urlencode($this->project) . '/merge_requests', array(
            'source_branch' => $branch,
            'target_branch' => 'master',
            'title' => 'Update settings ' . $secret,
            'remove_source_branch' => true,
            ''
        ));
        rmdir("/tmp/$secret");
    }
}

$gitlab = new Gitlab(getenv('GITLAB_DOMAIN'),
                     getenv('GITLAB_TOKEN'),
                     getenv('GITLAB_PROJECT'));

var_dump($gitlab->me());