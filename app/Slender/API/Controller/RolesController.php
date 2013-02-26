<?php

namespace Slender\API\Controller;

use \App;
use Dws\Slender\Api\Auth\Permissions;
use Slender\API\Model\Users;

class RolesController extends \Slender\API\Controller\BaseController
{
	protected $returnKey = 'roles';

    public function update($id)
    {
        $this->checkPayloadAgainstClientPermissions();
        return parent::update($id);
    }

    public function insert()
    {
        $this->checkPayloadAgainstClientPermissions();
        return parent::insert();
    }

    protected function checkPayloadAgainstClientPermissions()
    {
        // get client user permissions
        $clientUser = App::make('client-user');

        // The client-user is populated by the common-permission filter, which doesn't
        // run during unit-tests. So, just skip this if he hasn't been populated.
        if (!$clientUser) {
            return;
        }
        $clientPermissions = new Permissions($clientUser['permissions']);

        // get payload
        $input = $this->getJsonBodyData();

        Permissions::normalize($input['permissions']);

        // check aggregate role permissions against payload permissions
        if (!$clientPermissions->isAtLeast($input['permissions'])) {
            Response::json([
                'messages' => [
                    'Unauthorized: proposed permissions in excess of client permissions',
                ],
            ], 401);
        }
    }
}