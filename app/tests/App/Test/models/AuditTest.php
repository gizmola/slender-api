<?php

namespace App\Test\Model;

use App\Test\TestCase;
use Slender\API\Model\Audit;
use Dws\Utils\UUID;
use Slender\API\Model\Users;


/**
 * Test the Audit model
 *
 * @author Vadim Engoyan <vadim.engoyan@diamondwebservices.com>
 */
class AuditTest extends TestCase
{
    /**
     * @var Audit
     */
    protected $auditModel;
    
    /**
     * @var Users
     */
    protected $usersModel;


    public function setUp()
    {
        parent::setUp();
        $this->auditModel = new Audit();
        $this->usersModel = new Users();
    }


    public function testInsertAuditLog()
    {
        $uid = UUID::v4();

        $log = $this->auditModel->insert([
            'uid'           => $uid,
            "suid"          => [],  // Site specific user ID
            'before'        => 'test',
            'after'         => [
                    'test' => 'object has been changed to this'
                ],

        ]);

        $logId = $log['_id'];

        // confirm that log is in place
        $log = $this->auditModel->findById($logId, true);
        $this->assertEquals($uid, $log['uid']);
        $this->assertEquals('test', $log['before']);
        $this->assertEquals('object has been changed to this', $log['after']['test']);
    }

    public function testUserInsertEffectToAuditLog(){
        $anchor = UUID::v4();

        // create user
        $user = $this->usersModel->insert([
            'first_name' => 'John',
            'last_name' => $anchor,
            'email' => 'john@exmaple.com',
            'password' => 'test',
        ]);

        $userId = $user['_id'];

        $log = $this->auditModel->findMany([
                    ['after.last_name', $anchor],
                    ['after._id', $userId],
                ], [], [], $meta);

        $log = $log[0];
        $this->assertEquals(null, $log['before']);
        $this->assertEquals($userId, $log['after']['_id']);
        $this->assertEquals($anchor, $log['after']['last_name']);
    }

    public function testUserUpdateEffectToAuditLog(){
        $anchor = UUID::v4();

        // create user
        $user = $this->usersModel->insert([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@exmaple.com',
            'password' => 'test',
        ]);

        $userId = $user['_id'];
        
        $user2 = $this->usersModel->update($userId, [
            'first_name' => 'John II',
            'last_name' => $anchor,
            'email' => 'john@exmaple.com',
        ]);
        $meta = [];
        $log = $this->auditModel->findMany([
                    ['after.last_name', $anchor],
                    ['after._id', $userId],
                ], [], [], $meta);

        $log = $log[0];

        $this->assertEquals($userId, $log['before']['_id']);
        $this->assertEquals('John', $log['before']['first_name']);
        $this->assertEquals('Doe', $log['before']['last_name']);

        $this->assertEquals($userId, $log['after']['_id']);
        $this->assertEquals('John II', $log['after']['first_name']);
        $this->assertEquals($anchor, $log['after']['last_name']);

    }
}
