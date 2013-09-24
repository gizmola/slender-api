Slender API
===
Build Status
============

| Branch    | Status                                                                                                                                                                                            |
| --------- |:-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|
| Master    | [![Build Status](https://circleci.com/gh/dwsla/slender-api/tree/master.png?circle-token=a2befce0815c6078a9a7e1cfba74dc61abbc7337)](https://circleci.com/gh/startupdevs/fox-fapi/tree/master)      |

From your terminal
```
git clone git@github.com:dwsla/slender-api.git
cd slender-api
```

* If needed, download & install virtual box 4.2.x: http://www.virtualbox.org/wiki/Downloads
* If needed, Download & install vagrant latest: http://downloads.vagrantup.com/

From the terminal (inside slender-api dir):
* vagrant box add centos-6.3-i386 https://opscode-vm.s3.amazonaws.com/vagrant/boxes/opscode-centos-6.3-i386.box #Only if you havent used this box before.
* vagrant up
* vagrant ssh (if on windows: http://vagrantup.com/v1/docs/getting-started/ssh.html)
* Verify that the storage/views directory is writable.


Once you ssh in, cd to /vagrant and run:
```
php composer.phar --verbose install
```

This should give you a working Slender API on http://localhost:4003 from the host, and http://localhost:80 from inside the VM.


Issues?
---------------------

Make sure you have right permissions for `storage` directory
```
chmod -R 777 app/storage
```

If you get Class 'MongoClient' not found
```
try: sudo pecl install mongo
```
from inside of vagrant

#Parent Child Relations

resource.php
------
Note that a parent must must define a relation type for each child (has-many, has-one)
```
'profiles' => [
    'controller' => [
      'class' => 'Slender\API\Controller\ProfilesController',
    ],
    'model' => [
        'class' => 'Slender\API\Model\Profiles',
        'parents' => [
           'users' => [
               'class' => 'Slender\API\Model\Users',
           ],
        ],
        'children' => [
           'locations' => [
               'class' => 'Slender\API\Model\Locations',
               'embed' => true,
               'embedKey' => 'locations',
               'type' => 'has-many',
           ],
        ],
    ],
],
```

#CONSUMERS
<a href="https://github.com/dwsla/slender-api/wiki/Client-Consumers">client consumers guide</a>
