<?php 
namespace Dws\Slender\Api\Validation;

use \Exception as GenericException;
use Illuminate\Support\MessageBag;

class ValidationException extends GenericException{

    protected $messages = array();

    public function __construct($message = null, $code = 0, Exception $previous = null){
        if($message instanceof MessageBag){
            $message->setFormat(':message');
            $this->messages = $message->getMessages();
        }

        parent::__construct("Validation Error", $code, $previous);
    }

    public function getMessages(){
        return $this->messages;
    }

}