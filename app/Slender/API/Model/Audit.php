<?php

namespace Slender\API\Model;

use Dws\Utils\UUID;

class Audit extends BaseModel
{
    protected $collectionName = 'audit';

    protected $timestamp = false;

    protected $schema = [
        'uid'           => ['required'],
        "suid"          => [],  // Site specific user ID
        'before'        => [],
        'after'         => ['required'],

        'timestamp' => ['datetime'],
    ];

    /**
     * Insert data into the collection
     *
     * @param array $data
     * @return array
     */
    public function insert(array $data)
    {
        $data['timestamp'] = new \MongoDate();

        if(!isset($data['uid'])){
            $client = $this->getClientUser();
            if(!is_null($client) && isset($client['_id'])){
                $data['uid'] = $client['_id'];
            }
        }
        $data['_id'] = UUID::v4();

        $id = $this->getCollection()->insert($data);
        $entity = $this->findById($id);
        return $entity;
    }

    /**
     * Update data of the record
     *
     * @param string $id
     * @param array $data
     * @return array
     */
    public function update($id, array $data)
    {
        return null;
    }
}
