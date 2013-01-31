<?php

/**
 * Base controller
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */

use Dws\Slender\Api\Support\Util\UUID;

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
		$records = $this->model->findMany(array(),array());
		return Response::json(array(
			$this->getReturnKey() => $records
		));
	}

	public function update($id)
	{
		$input = Input::json(true);
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
		die("<p>Debug :: " . __FILE__ . "(" . __LINE__ . ") :: " . __FUNCTION__ . " :: message</p>");
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
}
