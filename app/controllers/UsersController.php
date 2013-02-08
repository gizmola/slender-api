<?php
use Dws\Slender\Api\Support\Util\UUID;

class UsersController extends BaseController
{
	protected $returnKey = 'users';

    public function __construct(Users $model)
    {
        parent::__construct($model);
    }

    public function insert()
    {
        $input = Input::json(true);

// var_dump('-->>', $input);
        $validator = Validator::make(
            $input,
            $this->model->getSchemaValidation()
        );

        // var_dump($input, $this->model->getSchemaValidation(), $validator->fails());
        // die;

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
}