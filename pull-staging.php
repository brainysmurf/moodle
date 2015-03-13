<?php
require 'vendor/autoload.php';
$deployer = new \Tmd\AutoGitPull\Deployer(array(
    'deployUser' => 'www-data',
    'directory' => '/var/www/dragonnet/moodle/',
    'logDirectory' => '/var/log/moodlepull/',
    'notifyEmails' => array(
    ),
    'branch' => 'staging'
));
$deployer->postDeployCallback = function () {
    echo 'Yay!';
};
$deployer->deploy();
