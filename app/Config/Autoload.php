<?php

namespace Config;

use CodeIgniter\Config\AutoloadConfig;


 // AUTOLOADER CONFIGURATION

class Autoload extends AutoloadConfig
{
  
     // Namespaces

    public $psr4 = [
        APP_NAMESPACE => APPPATH,
    ];

     // Class Map
   
    public $classmap = [];

     // Files
   
    public $files = [];

     //Helpers
  
    public $helpers = ['csrf', 'url', 'html', 'news', 'auth', 'pagination'];
}
