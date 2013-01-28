<?php

/**
 * Base controller
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
abstract class BaseController extends Controller
{
	protected $site;
	
	protected $modelClass;
	
	protected $returnKey;
	
	public function httpGetSingular($id)
	{
		$model = $this->getModel();
		$record = $model::find(array( '_id' => $id));
		return Response::json(array(
			$this->getReturnKey() => array(
				$record
			),
		));
	}

	public function httpGetPlural()
	{
		die("<p>Debug :: " . __FILE__ . "(" . __LINE__ . ") :: " . __FUNCTION__ . " :: message</p>");
	}

	public function httpPutSingular($id)
	{
		die("<p>Debug :: " . __FILE__ . "(" . __LINE__ . ") :: " . __FUNCTION__ . " :: message</p>");
	}

	public function httpDeleteSingular($id)
	{
		die("<p>Debug :: " . __FILE__ . "(" . __LINE__ . ") :: " . __FUNCTION__ . " :: message</p>");
	}

	public function httpOptionsSingular($id)
	{
		die("<p>Debug :: " . __FILE__ . "(" . __LINE__ . ") :: " . __FUNCTION__ . " :: message</p>");
	}

	public function httpOptionsPlural()
	{
		die("<p>Debug :: " . __FILE__ . "(" . __LINE__ . ") :: " . __FUNCTION__ . " :: message</p>");
	}

	public function getSite()
	{
		if (null == $this->site) {
			throw new \Exception('Site must be set in subclasses');
		}
		return $site;
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
		}
		return $this->model;
	}
	
	public function setModel($model)
	{
		$this->model = (string) $model;
		return $model;
	}
	
	public function getReturnKey()
	{
		if (null == $this->returnKey) {
			throw new \Exception('Return key not set');
		}
		return $this->returnKey;
	}

	public function setReturnKey($returnKey)
	{
		$this->returnKey = (string) $returnKey;
		return $this;
	}
}
