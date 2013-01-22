Slender
=======

Slender is an API framework built on top of Laravel 4.

In particular, Slender is an extension of Laravel that makes some opinionated choices for 
quick API development. 

Slender will: 

* Automate controller instantiation and action calls based upon a set of common 
REST route formats.
* Set response `content-type` headers to `application/json`.
* Create `application/json` specific handlers for `error()`, `notAllowed()`, `badRequest()` 
that are accessible from controllers.

**Note:** At present, the project and API is highly unstable. Not (yet!) recommended for production.

INSTALL
=======

Composer install
----------------

Add to your project's `composer.json`:

```
{
	"repositories" : [
		{
			"type" : "vcs",
			"url" : "git://github.com/dwsla/slender-api.git"
		}
	],
	"require" : {
		"dwsla/slender-api": "dev-master"
	}
}
```

To allow Slender to autoload, instantiate, and call actions on your controllers, you 
can add an `autoload` entry to your `composer.json`. Assuming your project structure 
is as follows:

```
<app>
	<src>
		<mylib>
			<My>
				<Controller>
					Some.php
					Other.php
<vendor>
	autoload.php
	// various imported packages
<public>
	index.php
```

then add the following into your `composer.json`:

```
"autoload": {
	"psr-0": {
		"My" : "app/src/mylib"
	}
}
```

Then perform a composer install/update:

```
$ php composer.phar install

or

$ php composer.phar update
```

depending upon the state of your project.

USAGE
=====

Your `public/index.php` could be as simple as:

```
<?php

require __DIR__ . '/../vendor/autoload.php;
$app = new Dws\Slender\Slender(array(
	'controllerNamespace' => 'My\Controller'
));
$app->run();

```

In all likelihood, you have some resources that are needed by your controllers, like
a DB-adapter. In that case, you can just pass them in under the `controllerResources` key:

```
<?php

require `../vendor/autoload.php`;
$db = my_db_adapter(); // however, you get your db adapter
$app = new Dws\Slender\Slender(array(
	'controllerNamespace' => 'My\Controller',
	'controllerResources' => array(
		'db' => $db,
	),
));
$app->run();

```

Below are samples of how a request is mapped to controller::action:

<pre>
GET		/some		=> My\Controller\Some::httpGet()
GET		/some/123	=> My\Controller\Some::httpGet(123)
POST	/some		=> My\Controller\Some::httpPost()
POST	/some/123	=> handled by notAllowed()
PUT		/some		=> handled by notAllowed()
PUT		/some/123	=> My\Controller\Some::httpPut(123)
DELETE	/some		=> handled by notAllowed()
DELETE	/some/123	=> My\Controller\Some::httpDelete(123)
</pre>

As you can see, controllers must implement an interface with this signature:

```
httpGet($id = null)
httpPost()
httpPut($id)
httpDelete($id)
```

An interface `Dws\Slender\Controller\ControllerInterface` is provided for easy IDE
consumption.


TESTS
=====

[PHPUnit](https://github.com/sebastianbergmann/phpunit/) unit-tests are in the `tests` directory.

```
$ cd tests
$ phpunit
```

Code coverage report is in the file `tests/log/report/index.html`

