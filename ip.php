<?php

/**
 * Shows the visitor's IP
 * Sometimes useful for debugging
 */
echo '<pre>';
echo "\nREMOTE_ADDR = {$_SERVER['REMOTE_ADDR']}";
echo "\nREMOTE_ADDR hostname = ". gethostbyaddr($_SERVER['REMOTE_ADDR']);
echo "\nHTTP_X_FORWARDED_FOR = {$_SERVER['HTTP_X_FORWARDED_FOR']}";
echo "\nHTTP_X_FORWARDED_FOR hostname = ". gethostbyaddr($_SERVER['HTTP_X_FORWARDED_FOR']);
