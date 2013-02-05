<?php

/**
 * Base controller
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */

use Dws\Slender\Api\Support\Util\UUID;
use Dws\Slender\Api\Controller\Helper\Params as ParamsHelper;
use Dws\Slender\Api\Validation\ValidationException;
use Illuminate\Support\MessageBag;

abstract class BaseController extends Controller
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
	
	protected $returnKey;

	public function __construct(BaseModel $model)
	{
		$this->model = $model;
	}
	
	public function view($id)
	{
		$record = $this->model->findById($id);
		return Response::json(array(
			$this->getReturnKey() => ($record ? array($record) : array()),
		));
	}

	public function index()
	{
		
		$filters = (ParamsHelper::getFilters()) ? ParamsHelper::getFilters() : array();
		$fields = (ParamsHelper::getFields()) ? ParamsHelper::getFields() : array();
		$orders = (ParamsHelper::getOrders()) ? ParamsHelper::getOrders() : array();
		$take = ParamsHelper::getTake();
		$skip = ParamsHelper::getSkip();

		$records = $this->model->findMany($filters, $fields, $orders, $take, $skip);
		
		return Response::json(array(
			$this->getReturnKey() => $records
		));
	}

	public function update($id)
	{
		$input = Input::json(true);

		$validator = Validator::make(
            $input,
            $this->model->getSchemaValidation()
        );
        if($validator->fails()){
            return $this->badRequest($validator->messages());
        }

		$entity = $this->model->update($id, $input);
		return Response::json(array(
			$this->getReturnKey() => array(
				$entity,
			),
		), self::HTTP_PUT_OK);
	}

	public function insert()
	{
		$input = Input::json(true);

		$validator = Validator::make(
            $input,
            $this->model->getSchemaValidation()
        );

        if($validator->fails()){
            return $this->badRequest($validator->messages());
        }

		$input['_id'] = UUID::v4();		
		$entity = $this->model->insert($input);
		return Response::json(array(
			$this->getReturnKey() => array(
				$entity,
			),
		), self::HTTP_POST_OK);
	}

	public function delete($id)
	{
		$this->getModel()->delete($id);
		return Response::json(array(
			'messages' => array(
				'ok',
			),
		), self::HTTP_DELETE_OK);
	}

	public function options()
	{
		$options = $this->model->options();
		return Response::json(array(
			'PUT' => array(
				$options,
			),
		), self::HTTP_OPTIONS_OK);
	}

	public function getSite()
	{
		if (null == $this->site) {
			throw new \Exception('Site must be set in subclasses');
			// @todo: extract from classame
		}
		return $this->site;
	}
	
	public function setSite($site)
	{
		$this->site = (string) $site;
		return $this;
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
	public function badRequest($messages){
		if($messages instanceof MessageBag){
            $messages->setFormat(':message');
            $messages = $messages->getMessages();
        }
		return Response::json(array(
	            'messages' => array(
	                $messages,
	            ),
	        ), 400);
	} 

}
