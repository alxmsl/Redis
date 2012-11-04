Redis
=============
Simple wrapper class on phpredis extension. About phpredis see https://github.com/nicolasff/phpredis

Usage example
-------
    // Create Redis Client instance with you configuration settings
    $Redis = RedisFactory::createRedisByConfig(array(
        'host' => 'localhost',
        'port' => 6379,
    ));

    // Use Redis commands
    $Redis->set('test', '7');
    var_dump($Redis->get('test'));