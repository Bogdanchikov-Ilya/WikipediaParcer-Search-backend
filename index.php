<?php
//error_reporting(E_ALL);
//ini_set('display_startup_errors', 1);
//ini_set('display_errors', '1');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Allow-Methods: *');
header('Access-Control-Allow-Credentials: true');

header('Content-type: json/application');
require 'connect.php';
require 'functions.php';
$method = $_SERVER['REQUEST_METHOD'];
$q = $_GET['q'];
$params = explode('/', $q);

$type = $params[0];
$id = $params[1];


if($method === 'GET') {
    if($type === 'articles'){
        if(isset($id)) {

        } else{
            getArticles($connect);
        }
    }
} elseif ($method = 'POST') {
    if($type === 'articles') {
        addArticles($connect, $_POST);
    } elseif ($type === 'search'){
        search($connect, $_POST);
    }
}