README
======

Scabbia Framework is an open source PHP framework which is still under development.

Scabbia follows the accepted standards of PHP Framework Interop Group (http://www.php-fig.org/).


Installation
------------
On *nix:
``` bash
curl -s http://getcomposer.org/installer | php
php composer.phar require larukedi/scabbia-framework:dev-development
```

On Windows:
``` bat
composer require larukedi/scabbia-framework:dev-development
```


Usage
-----
``` php
$loader = require 'vendor/autoload.php';
Scabbia\Framework::load($loader);
```


Requirements
------------
* PHP 5.3.0+ (http://www.php.net/)
* Composer Dependency Manager (http://getcomposer.org/)


Includes
------------
* psr/log: PSR-3 Logger Interface (http://www.php-fig.org/)


License
-------
See [license.txt](license.txt)


Contributing
------------
Fork the repo, push your changes to your fork, and submit a pull request.
