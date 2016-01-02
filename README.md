# Scabbia Framework Version 1.5

[Scabbia Framework](https://github.com/eserozvataf/scabbia1) is an open source PHP framework project under GPL license. It had been under development by [Eser Ozvataf](http://eser.ozvataf.com/) for 2 years and reached version 1.5 on stable branch. Its active development is frozen but small bugfixes will be available in time.

## Installation

**Step 1:**

On Terminal or Command Prompt:
``` bash
git clone https://github.com/eserozvataf/scabbia1-skeleton project
```

Alternatively [Scabbia Skeleton](https://github.com/eserozvataf/scabbia1-skeleton/archive/master.zip) package can be downloaded directly.

**Step 2:**

``` bash
cd project
php scabbia update
```

**Step 3:**

Make `application/writable` and `application/locale` directories writable.

``` bash
chmod 0777 -R application/writable
chmod 0777 -R application/locale
```

**Step 4:**

Open `application/config/datasources.json` file to update the database configuration parameters.

a sample mysql database configuration:
```json
{
    "datasourceList": [
        {
            "id":           "dbconn",
            "interface":    "pdo",
            "persistent":   true,
            "overrideCase": "natural",
            "pdoString":    "mysql:host=localhost;dbname=project",
            "username":     "root",
            "password":     "123456",
            "initCommand":  "SET NAMES utf8",
            "errors":       "exception"
        }
    ]
}
```


## Requirements
* PHP 5.3.3+ (http://www.php.net/)
* Composer Dependency Manager** (http://getcomposer.org/)

** Skeleton application auto-installs this requirement with other dependencies.


## Dependencies
* psr/log: PSR-3 Logger Interface (http://www.php-fig.org/)
* facebook/php-sdk: Facebook PHP SDK
* dflydev/markdown: Markdown Parser
* mustache/mustache: Mustache Parser
* trekksoft/potomoco: gettext compiler
* leafo/lessphp: LESS compiler


## Bundled Components
* fonts/KabobExtrabold.ttf
* twitter/bootstrap
* twitter/hogan
* twitter/typeahead
* introjs
* jquery
* cleditor
* flot
* jquery.maskedinput
* jquery.tablesorter
* jquery.validation
* laroux.js
* mapbox
* normalize.css
* reset.css


## Optional PHP Extensions
* curl: http communication
* gd: image manipulation
* gettext: translation
* intl: i18n features
* mbstring: multibyte string manipulation
* memcache: caching features
* mongo: mongodb support
* soap: soap protocol
* sockets: socket communication
* tokenizer: lexical analysis


## Links
* [Contributor List](contributors.md)
* [License Information](LICENSE)


## Contributing
It is publicly open for any contribution. Bugfixes, new features and extra modules are welcome. All contributions should be filed on the [eserozvataf/scabbia1](https://github.com/eserozvataf/scabbia1) repository.

* To contribute to code: Fork the repo, push your changes to your fork, and submit a pull request.
* To report a bug: If something does not work, please report it using GitHub issues.
* To support: [![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=BXNMWG56V6LYS)
