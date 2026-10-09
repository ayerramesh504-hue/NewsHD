<?php

namespace Config;

use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Cache\Handlers\ApcuHandler;
use CodeIgniter\Cache\Handlers\DummyHandler;
use CodeIgniter\Cache\Handlers\FileHandler;
use CodeIgniter\Cache\Handlers\MemcachedHandler;
use CodeIgniter\Cache\Handlers\PredisHandler;
use CodeIgniter\Cache\Handlers\RedisHandler;
use CodeIgniter\Cache\Handlers\WincacheHandler;
use CodeIgniter\Config\BaseConfig;

class Cache extends BaseConfig
{
   
     //Primary Handler
  
    public string $handler = 'file';

     // Backup Handler
    
    public string $backupHandler = 'dummy';

     //Key Prefix
   
    public string $prefix = '';

     // Default TTL
    
    public int $ttl = 60;

   
     // Reserved Characters
    
    public string $reservedCharacters = '{}()/\@:';

   
     // File settings
  
    public array $file = [
        'storePath' => WRITEPATH . 'cache/',
        'mode'      => 0640,
    ];

   
     // Memcached settings
   
    public array $memcached = [
        'host'   => '127.0.0.1',
        'port'   => 11211,
        'weight' => 1,
        'raw'    => false,
    ];

     // Redis settings

    public array $redis = [
        'host'       => '127.0.0.1',
        'password'   => null,
        'port'       => 6379,
        'timeout'    => 0,
        'async'      => false, 
        'persistent' => false,
        'database'   => 0,
    ];

     // Available Cache Handlers
  
    public array $validHandlers = [
        'apcu'      => ApcuHandler::class,
        'dummy'     => DummyHandler::class,
        'file'      => FileHandler::class,
        'memcached' => MemcachedHandler::class,
        'predis'    => PredisHandler::class,
        'redis'     => RedisHandler::class,
        'wincache'  => WincacheHandler::class,
    ];

 
     // Web Page Caching: Cache Include Query String

    public $cacheQueryString = false;

    
     // Web Page Caching: Cache Status Codes
  
    public array $cacheStatusCodes = [];
}
