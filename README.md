Slender API
===
Build staus
=====

Master: [![Build Status](http://potterskolen.dk/buildstatus.php?job=Slender-API)](http://ci.diamondwebservices.com:8080/job/Slender-API/)
Setup
=====

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

Send Parent/Child data on PUT and POST
--------------
The format for both PUT and POST are identical. Simply provide a "_parents" or "_children" associative array. 
```
curl -X POST -H Authentication:slender --data '{"zip":"90200", "_parents":{"profiles":["9a2b43f1-a8d3-46a3-9289-6289e91234e8"]}}' http://localhost:80/eb/locations/e4373954-79a4-4309-8536-6a78a15fa8b7

curl -X PUT -H Authentication:slender --data '{"birthday" : "1/19/2005", "_children": {"locations":["e4373954-79a4-4309-8536-6a78a15fa8b7"]}}' http://localhost:80/eb/profiles/9a2b43f1-a8d3-46a3-9289-6289e91234e8
```

#Query Parameters

WHERE
-----
### select persons 40 yrs old:
```
/endpoint?where[]=age:40
```
### select persons older that 40 yrs old
```
/endpoint?where[]=age:gt:40
```
### select persons 40 or older
```
/endpoint?where[]=age:gte:40
```
### select persons 40 or older who own a red car (car is embeded object)
```
/endpoint?where[]=age:gte:40&car.color:red
```
FIELDS
------

### select age, gender and car color only (car is embeded object)
```
/endpoint?fields:age,gender,car.color
```

ORDER
-----
### order by last name ascending and first name descending
```
/endpoint?order=lastname:asc,firstname:desc
```

TAKE & SKIP
----
### select the first 10 documents
```
/endpoint?take=10
```
### select the second 10 documents
```
/endpoint?skip=10&take=10
```

Aggregates
---------
### count only requires on param
```
/endpoint?aggregate=count
```

### the rest require the field
```
/endpoint?aggregate=max:age
```
