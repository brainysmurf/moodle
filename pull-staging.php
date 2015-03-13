<?php
require 'vendor/autoload.php';
$deployer = new \Tmd\AutoGitPull\Deployer(array(
    'directory' => '/var/www/dragonnet/moodle/',
    'logDirectory' => '/var/log/moodlepull/',
    'branch' => 'staging'
));
$deployer->postDeployCallback = function () {
    echo 'Yay!';
};
$deployer->deploy();
