README
======

Nothing yet.

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
* PHP 5.3.0+

License
-------
See [license.txt](license.txt)

Contributing
------------
Fork the repo, push your changes to your fork, and submit a pull request.
