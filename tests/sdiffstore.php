<?php
/**
 * Example for create difference set
 * @author alxmsl
 * @date 10/22/12
 */

include('../source/Autoloader.php');

use \Redis\Client\RedisFactory;

$Redis = RedisFactory::createRedisByConfig(array(
    'host' => 'localhost',
    'port' => 6379,
));

$Redis->sadd('test1', 1);
$Redis->sadd('test1', 2);
$Redis->sadd('test1', 3);

$Redis->sadd('test2', 2);

$Redis->sdiffstore('test3', array('test1', 'test2'));
var_dump($Redis->smembers('test3'));