<?php

namespace Redis;

if (!extension_loaded('redis')) {
    throw new \RuntimeException('php redis extension not found');
}

// append Cli autoloader
spl_autoload_register(array('\Redis\Redis', 'autoload'));

/**
 * Base class
 * @author alxmsl
 * @date 11/1/12
 */
final class Redis {
    /**
     * @var array array of available classes
     */
    private static $classes = array(
    );

    /**
     * Component autoloader
     * @param string $className claass name
     */
    public static function autoload($className) {
        if (array_key_exists($className, self::$classes)) {
            $fileName = realpath(dirname(__FILE__)) . '/' . self::$classes[$className];
            if (file_exists($fileName)) {
                include $fileName;
            }
        }
    }
}
