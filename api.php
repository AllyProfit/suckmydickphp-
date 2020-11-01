<?php
session_start();

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once "./libs/DataBase/autoload.php";
require_once "./libs/simple-api/autoload.php";

use DigitalStars\DataBase\DB;
use DigitalStars\SimpleAPI;

header('Access-Control-Expose-Headers: Access-Control-Allow-Origin', false);
header('Access-Control-Allow-Origin: *', false);
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept', false);
header('Access-Control-Allow-Credentials: true');

$db_type = 'mysql';
$db_name = 'learner16';
$login = 'learner16';
$pass = 'AU9Eod9IsNVw23V156U6';
$ip = 'localhost';

$db = new DB("$db_type:host=$ip;dbname=$db_name", $login, $pass,
    [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]]

);

$api = new SimpleAPI();
switch ($api->module) {
    case 'auth':
        $data = $api->params(['login', 'password']);
        
        $res = $db->row('SELECT * FROM users WHERE login=?s and password=?s',
            [$data['login'], $data['password']]
        );

        if($res) {
            $_SESSION['login'] = $res['login'];
            $_SESSION['name'] = $res['name'];
        }

        $api->answer['res'] = $res;
        break;
    case 'reg':
        $data = $api->params(['login', 'password', 'name']);
        
        $res = $db->query('INSERT INTO users (login, name, password) VALUES (?as)', [
            [$data['login'], $data['name'], $data['password']]
        ]);

        $answer = [
            'status' => ($res) ? 200 : 409,
        ];

        $api->answer['res'] = $answer;
        break;
    case 'operator':
        if(isset($_SESSION['name']) && isset($_SESSION['login'])) {
            $data = $api->params(['operator', 'valueDown', 'valueUp']);
            $expression = $data['valueUp'] . $data['operator'] . $data['valueDown'];
            $api->answer['res'] = eval('return ' . $expression . ';');
        } else {
            $api->answer['res'] = [
                'status' => 401,
                'status_msg' => 'Unauthorized',
            ];
        }
        break;
    case 'checkUser': 
        if(isset($_SESSION['name']) && isset($_SESSION['login'])) {
            $api->answer['res'] = [
                'name' => $_SESSION['name'],
                'login' => $_SESSION['name'],
            ];
        } else {
            $api->answer['res'] = [
                'status' => 401,
                'status_msg' => 'Unauthorized',
            ];
        }
        break;
    case 'logout':
        session_destroy();
        break;
        }