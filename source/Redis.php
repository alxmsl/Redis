<?php

namespace Redis\Client;

/**
 * Class for redis client
 * @author alxmsl
 * @date 11/1/12
 */
final class Redis extends \Redis {
    /**
     * @var string redis instance hostname
     */
    private $host = '';

    /**
     * @var int redis instance port
     */
    private $port = -1;

    /**
     * @var float redis instance connect timeout
     */
    private $connectTimeout = 0;

    /**
     * @var int number of tries for connect to redis instance
     */
    private $connectTries = 1;

    /**
     * @var bool use persistence connection, or not
     */
    private $persistent = false;

    /**
     * @var \Redis phpredis object instance
     */
    private $Redis = null;

    /**
     * Getter of phpredis object
     * @return \Redis phpredis object instance
     * @throws RedisNotConfiguredException if any of required redis connect parameters are loose
     */
    private function getRedis() {
        if (is_null($this->Redis)) {
            if ($this->isConfigured()) {
                $this->Redis = new \Redis();
                $this->reconnect();
            } else {
                throw new RedisNotConfiguredException();
            }
        }
        return $this->Redis;
    }

    /**
     * Check required connection parameters configuration method
     * @return bool check result
     */
    private function isConfigured() {
        return !empty($this->host) && $this->port >= 0 && $this->port <= 65535;
    }

    /**
     * Reconnect to the redis instance
     * @return bool connection result. Always true.
     * @throws RedisConnectException if connection could not established by RedisException cause
     * @throws RedisTriesOverConnectException if connection could not established because tries was over
     */
    private function reconnect() {
        $count = 0;
        do {
            $count += 1;
            try {
                if ($this->persistent) {
                    $result = $this->Redis->pconnect($this->host, $this->port, $this->connectTimeout);
                } else {
                    $result = $this->Redis->connect($this->host, $this->port, $this->connectTimeout);
                }
            } catch (\RedisException $ex) {
                throw new RedisConnectException();
            }
            if ($result === true) {
                return true;
            }
        } while ($count < $this->connectTries);

        $this->Redis = null;
        throw new RedisTriesOverConnectException();
    }

    /**
     * Setter of connection timeout parameter
     * @param float $connectTimeout connection timeout value
     * @throws \InvalidArgumentException
     * @return PhpAutoload self
     */
    public function setConnectTimeout($connectTimeout) {
        $this->connectTimeout = (float) $connectTimeout;
        if ($this->connectTimeout < 0) {
            throw new \InvalidArgumentException();
        }
        return $this;
    }

    /**
     * Getter of connection timeout exception
     * @return float connect timeout value
     */
    public function getConnectTimeout() {
        return $this->connectTimeout;
    }

    /**
     * Setter of number of connection tries
     * @param int $connectTries connection tries count
     * @throws \InvalidArgumentException
     * @return PhpAutoload self
     */
    public function setConnectTries($connectTries) {
        $this->connectTries = (int) $connectTries;
        if ($this->connectTries < 1) {
            throw new \InvalidArgumentException();
        }
        return $this;
    }

    /**
     * Getter of number of connection tries
     * @return int connection tries count
     */
    public function getConnectTries() {
        return $this->connectTries;
    }

    /**
     * Setter for redis instance hostname or ip address
     * @param string $host hostname or ip address
     * @throws \InvalidArgumentException
     * @return PhpAutoload self
     */
    public function setHost($host) {
        $this->host = (string) $host;
        if (empty($this->host)) {
            throw new \InvalidArgumentException();
        }
        return $this;
    }

    /**
     * Getter of redis instance hostname
     * @return string redis instance hostname or ip address
     */
    public function getHost() {
        return $this->host;
    }

    /**
     * Setter of redis instance connection port
     * @param int $port redis instance connection port
     * @throws \InvalidArgumentException
     * @return PhpAutoload self
     */
    public function setPort($port) {
        $this->port = $port;
        if ($this->port < 0 || $this->port > 65535) {
            throw new \InvalidArgumentException();
        }
        return $this;
    }

    /**
     * Getter of redis instance connection port
     * @return int redis instance connection port
     */
    public function getPort() {
        return $this->port;
    }

    /**
     * Use persistent connection or not
     * @param bool $persistent if is set to true, pconnect will use, overwise not
     * @return PhpAutoload self
     */
    public function setPersistent($persistent) {
        $this->persistent = (bool) $persistent;
        return $this;
    }

    /**
     * Use persistent connection or not
     * @return bool if is set to true, pconnect will use, overwise not
     */
    public function getPersistent() {
        return $this->persistent;
    }

    /*
     * phpredis interface implementation
     */

    /**
     * Increment key value
     * @param string $key key
     * @return int current value
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function incr($key, $value = 1) {
        $value = (int) $value;
        try {
            $result = ($value > 1)
                ? $this->getRedis()->incrBy($key, $value)
                : $this->getRedis()->incr($key);
            if ($result !== false) {
                return $result;
            }
            throw new RedisConnectException();
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Get key value
     * @param string $key key
     * @return mixed key value
     * @throws RedisConnectException exception on connection to redis instance
     * @throws RedisKeyNotFoundException when key not found
     */
    public function get($key) {
        try {
            $result = $this->getRedis()->get($key);
            if ($result === false) {
                throw new RedisKeyNotFoundException();
            }
            return $result;
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Set key value
     * @param string $key key
     * @param mixed $value value
     * @param int $timeout ttl timeout in milliseconds
     * @return bool operation result
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function set($key, $value, $timeout = 0) {
        try {
            $result = ($timeout == 0)
                ? $this->getRedis()->set($key, $value)
                : $this->getRedis()->psetex($key, $timeout, $value);
            if ($result !== false) {
                return $result;
            }
            throw new RedisConnectException();
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Set key value if not exists
     * @param string $key key
     * @param mixed $value value
     * @return bool returns true, if operation complete succesfull, else false
     * @throws RedisConnectException
     */
    public function setnx($key, $value) {
        try {
            return $this->getRedis()->setnx($key, $value);
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Set key bit
     * @param string $key key
     * @param int $offset bit offset
     * @param int $value bit value. May be 0 or 1
     * @return int bit value before operation complete
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function setbit($key, $offset, $value) {
        $offset = (int) $offset;
        $value = (int) (bool) $value;
        try {
            $result = $this->getRedis()->setBit($key, $offset, $value);
            if ($result !== false) {
                return $result;
            }
            throw new RedisConnectException();
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Evaluate Lua code
     * @param string $sha SHA1 string of Lua code
     * @param array $arguments array of Lua script arguments
     * @return mixed code execution result
     * @throws RedisConnectException exception on connection to redis instance
     * @throws RedisScriptExecutionException when script execution faled
     */
    public function evalSha($sha, array $arguments = array()) {
        try {
            if (empty($arguments)) {
                $result = $this->getRedis()->evalSha($sha);
            } else {
                $result = $this->getRedis()->evalSha($sha, $arguments, count($arguments));
            }

            $lastError = $this->getRedis()->getLastError();
            $this->getRedis()->clearLastError();
            if (is_null($lastError)) {
                return $result;
            }
            throw new RedisScriptExecutionException($lastError);
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }
}

class PhpRedisException extends \Exception {}
class RedisConnectException extends PhpRedisException {}
final class RedisTriesOverConnectException extends RedisConnectException {}
final class RedisNotConfiguredException extends PhpRedisException {}
final class RedisKeyNotFoundException extends PhpRedisException {}
final class RedisScriptExecutionException extends PhpRedisException {}