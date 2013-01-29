<?php

namespace Dws\Slender\Api\Database;

use LMongo\Facades\LMongo;

/**
 * A class..
 *
 * @author Vadim Engoyan <vadim.engoyan@diamondwebservices.com>
 */
class DbRepository
{
	public function getCollection($connectionName){
		return LMongo::connection($connectionName);
	}
}
