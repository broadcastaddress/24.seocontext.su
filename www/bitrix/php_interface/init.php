<?php
/**
 * Created by PhpStorm.
 * User: artshar
 * Date: 16.03.17
 * Time: 0:53
 */

function test_dump($arg){
    global $USER;
    if ($USER->IsAdmin()){
        echo '<pre>';
            var_dump($arg);
        echo '</pre>';
    }
}