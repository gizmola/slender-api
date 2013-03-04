<?php

namespace Slender\API\Controller;

use \App;
use \Input;
use \Response;
use \Validator;
use Dws\Slender\Api\Auth\Permissions;
use Dws\Slender\Api\Controller\Helper\Params as ParamsHelper;
// use Dws\Slender\Api\Validation\ValidationException;
// use Dws\Slender\Api\Route\SiteBasedResources\RouteException;
use Illuminate\Support\MessageBag;
use Slender\API\Model\BaseModel;

/**
 * Base controller
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
abstract class BaseController extends \Controller
{
	const HTTP_GET_OK = 200;
	const HTTP_POST_OK = 201;
	const HTTP_PUT_OK = 201;
	const HTTP_DELETE_OK = 200;
	const HTTP_UPDATE_OK = 204;
	// const HTTP_DELETE_OK = 204;
	const HTTP_OPTIONS_OK = 200;

	/**
	 * @var BaseModel
	 */
	protected $model;

    /**
     *
     * @var string
     */
	protected $returnKey;

    /**
     * @var array
     */
    protected $bodyData;

    /**
     * The client user making the request
     *
     * @var array
     */
    protected $clientUser;

    /**
     * Constructor
     *
     * @param \App\Controller\BaseModel $model
     */
	public function __construct(BaseModel $model)
	{
		$this->model = $model;
	}

    /**
     * Handles HTTP GET method on a singular endpoint
     *
     * @param string $id
     * @return mixed
     */
	public function view($id)
	{
		$record = $this->model->findById($id);
		// @TODO: make it work with unit test
		// if($record)
		// {
			return Response::json(array(
				$this->getReturnKey() => ($record ? array($record) : array()),
			));
		// }else{
		// 	$msg = sprintf('Unable to find record %s for %s', $id, $this->getReturnKey());
		// 	throw new RouteException($msg);
		// }
	}

    /**
     * Handles HTTP GET method on a plural-endpoint
     *
     * @return mixed
     */
	public function index()
	{

		$where = (ParamsHelper::getWhere()) ? ParamsHelper::getWhere() : [];
		$fields = (ParamsHelper::getFields()) ? ParamsHelper::getFields() : [];
		$orders = (ParamsHelper::getOrders()) ? ParamsHelper::getOrders() : [];
		$aggregate = (ParamsHelper::getAggregate()) ? ParamsHelper::getAggregate() : [];
		$take = ParamsHelper::getTake();
		$skip = ParamsHelper::getSkip();
		$with = ParamsHelper::getWith();

		$meta = [];

		$records = $this->model->findMany($where, $fields, $orders, $meta, $aggregate, $take, $skip, $with);

		$result = [
			$this->getReturnKey() => $records
		];

		if($meta)
		{
			$result['meta'] = $meta;
		}

		return Response::json($result);
	}

    /**
     * Handles HTTP PUT method in a singular endpoint
     *
     * @param string $id
     * @return mixed
     */
	public function update($id)
	{
        $input = $this->getJsonBodyData();

        $validator = $this->makeCustomValidator($input);

        if ($validator->fails()) {
            return $this->badRequest($validator->messages());
        }

		$entity = $this->model->update($id, $input);
		return Response::json(array(
			$this->getReturnKey() => array(
				$entity,
			),
		), self::HTTP_PUT_OK);
	}

    /**
     * Handles HTTP POST method on a plural endpoint
     *
     * @return mixed
     */
	public function insert()
	{
        $input = $this->getJsonBodyData();

        $validator = Validator::make(
            $input,
            $this->model->getSchemaValidation()
        );

        if ($validator->fails()) {
            return $this->badRequest($validator->messages());
        }

		$entity = $this->model->insert($input);
		return Response::json(array(
			$this->getReturnKey() => array(
				$entity,
			),
		), self::HTTP_POST_OK);
	}

    /**
     * Handles HTTP DELETE method on a singular endpoint
     *
     * @param string $id
     * @return type mixed
     */
	public function delete($id)
	{
		$this->getModel()->delete($id);
		return Response::json(array(
			'messages' => array(
				'ok',
			),
		), self::HTTP_DELETE_OK);
	}

    /**
     * Handles HTTP OPTIONS method on plural endpoint
     *
     * @return mixed
     */
	public function options()
	{
		$options = $this->model->options();
		return Response::json(array(
			'PUT' => $options,
		), self::HTTP_OPTIONS_OK);
	}

	public function getModel()
	{
		if (null == $this->model) {
			throw new \Exception('Model not set');
			// @todo: extract from classame
		}
		return $this->model;
	}

	public function setModel($model)
	{
		$this->model = $model;
		return $model;
	}

	public function getReturnKey()
	{
		if (null == $this->returnKey) {
			throw new \Exception('Return key not set');
			// @todo: extract from classame
		}
		return $this->returnKey;
	}

	public function setReturnKey($returnKey)
	{
		$this->returnKey = (string) $returnKey;
		return $this;
	}

	// @TODO: try to find better way which works for App and PHPUnit
	public function badRequest($messages)
    {
		if ($messages instanceof MessageBag) {
            $messages->setFormat(':message');
            $messages = $messages->getMessages();
        }
		return Response::json(array(
            'messages' => $messages,
        ), 400);
	}

	// @TODO: try to find better way which works for App and PHPUnit
	public function unauthorizedRequest($messages)
    {
		if ($messages instanceof MessageBag) {
            $messages->setFormat(':message');
            $messages = $messages->getMessages();
        }
		return Response::json([
            'messages' => [
                $messages,
            ],
        ], 401);
	}

    public function getJsonBodyData()
    {
        if (null === $this->bodyData) {
            $data = Input::json(true);
            if (null === $data) {
                $this->badRequest([
                    'Empty/invalid JSON body',
                ]);
            }
            $this->bodyData = $data;
        }
        return $this->bodyData;
    }

    /**
     * Get the client-user making the request
     *
     * @return array
     */
    public function getClientUser()
    {
        if (null === $this->clientUser) {
            try {
                $this->clientUser = App::make('client-user');
            } catch (\Exception $e) {
            }
        }
        return $this->clientUser;
    }

    /**
     * Set the client user making the request
     *
     * @param array $clientUser
     * @return \Slender\API\Controller\BaseController
     */
    public function setClientUser($clientUser)
    {
        $this->clientUser = $clientUser;
        return $this;
    }

    protected function validatePayloadAgainstClient($input)
    {
        // get client user permissions
        $clientUser = $this->getClientUser();

        // The client-user is populated by the common-permission filter, which doesn't
        // run during unit-tests. So, just skip this if he hasn't been populated.
        if (!$clientUser) {
            return true;
        }
        $clientPermissions = new Permissions($clientUser['permissions']);

        $proposedPermissions = new Permissions($input['permissions']);

        return $clientPermissions->isAtLeast($proposedPermissions);
    }

    /**
     * Filters the schema's validation by the keys of the input.
     * Useful for partial updates.
     *
     * @param array$input
     * @return \Validator
     * @throws \Exception
     */
    protected function makeCustomValidator($input)
    {
        $valid = [];
        foreach ($this->model->getSchemaValidation() as $k => $v) {
            if (in_array($k, array_keys($input))) {
                $valid[$k] = $v;
            }
        }
        if (empty($valid)) {
            throw new \Exception("No valid parameters sent");
        }

        return Validator::make(
            $input,
            $valid
        );
    }
}
