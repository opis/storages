Opis Storages
=============
[![Latest Stable Version](https://poser.pugx.org/opis/storages/version.png)](https://packagist.org/packages/opis/storages)
[![Latest Unstable Version](https://poser.pugx.org/opis/storages/v/unstable.png)](//packagist.org/packages/opis/storages)
[![License](https://poser.pugx.org/opis/storages/license.png)](https://packagist.org/packages/opis/storages)

Storage implementations
------------------------
This package provides storage implementations for [Opis Cache](https://github.com/opis/cache),
[Opis Session](https://github.com/opis/session) and [Opis Config](https://github.com/opis/config) libraries.

### License

**Opis Storages** is licensed under the [Apache License, Version 2.0](http://www.apache.org/licenses/LICENSE-2.0). 

### Requirements

* PHP 5.3.* or higher
* [Opis Database](http://www.opis.io/database) ^3.0.0
* [Predis](https://github.com/nrk/predis) 1.0.*

### Installation

This library is available on [Packagist](https://packagist.org/packages/opis/storages) and can be installed using [Composer](http://getcomposer.org).

```json
{
    "require": {
        "opis/storages": "^1.1.0"
    }
}
```

### Provided storages

##### Opis Config

* Database storage

##### Opis Cache

* Redis storage
* Database storage

##### Opis Session

* Redis storage
* Database storage
