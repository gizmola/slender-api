<?php

namespace Slender\API\Controller;

class AuditController extends BaseController
{
	protected $returnKey = 'audit';

    /**
     * Handles HTTP PUT method in a singular endpoint
     *
     * @param string $id
     * @return mixed
     */
    public function update($id)
    {
        return $this->unauthorizedRequest([
            'Unauthorized: cannot update/insert audit log',
        ]);
    }

    /**
     * Handles HTTP POST method on a plural endpoint
     *
     * @return mixed
     */
    public function insert($input = null)
    {
        return $this->unauthorizedRequest([
            'Unauthorized: cannot update/insert audit log',
        ]);
    }

}
