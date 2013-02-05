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

        if(isset($input['roles'])){
            if(!is_array($input['roles'])){
                $input['roles'] = (array) $input['roles'];
            }
            $roles = new Roles();
            $user_roles = $roles->whereIn('_id', $input['roles'])->get();
            $input['roles'] = array();
            $input['permissions'] = array();

            foreach ($user_roles as $key => $value) {
                $input['roles'][] = $value['_id'];
                array_walk_recursive($value['permissions'], function($item, $key)
                {
                    if($item == 0){
                        unset($key);
                    }
                });
                $input['permissions'] = array_replace_recursive($input['permissions'], $value['permissions']);
            }
        }

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
}