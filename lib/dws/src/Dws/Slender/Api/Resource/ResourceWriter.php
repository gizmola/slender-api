<?php namespace Dws\Slender\Api\Resource;

use Dws\Utils;

class ResourceWriter 
{

    const SUCCESS = 0;
    const CLASSNAME_INVALID = 1;
    const TEMPLATE_NOT_FOUND = 2;
    const CREATE_DIR_FAILED = 3;

    /**
    * configuration
    * @var array
    */
    protected $config;

    /**
    * __construct
    * @param array $config
    * @return void
    */
    public function __construct($config = null) 
    {
        $this->config = $config;
    }

    /**
    * getConfig
    * @return array
    */
    public function getConfig($name = null)
    {
        if ($name) return array_get($this->config, $name);
        return $this->config;    
    }

    /**
    * setConfig
    * @param array $config
    * @return Dws\Slender\Api\Resource\ResourceWriter
    */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;    
    }

    /**
    * convert a psr-0 namespace to a filepath
    * @param string $namespace
    * @return string
    */
    protected function classNameToPath($namespace)
    {
        $psr0 = $this->getConfig('psr-0');
        $namespace = str_replace("\\", "/", $namespace);
        return  Utils\FileSystem::extendPath($psr0, $namespace) . ".php";
    }

    protected function parseClassType($namespace)
    {
        
        if (strstr($namespace, 'Controller')) {
            return 'controller';
        } elseif (strstr($namespace, 'Model')) {
            return 'model';
        }

        return false;

    }

    /**
    * load a controller or model template
    * @param string $namespace
    * @return void
    */
    protected function loadTemplate($type) {
        
        $file =$this->getConfig('templates')[$type];

        if (!file_exists($file)) 
            return false;
        
        return file_get_contents($file);

    }

    /**
    * generate the output to be written to the class file
    * @param array $class
    * @param string $type
    */
    protected function generateClassOutput($class, $type, $template)
    {
        /*
        * get the values to be written to the template
        */
        $namespace = Utils\NamespaceHelper::classNamespace($class['class']);
        $baseClass = array_get($class, 'extends');
        $name = Utils\NamespaceHelper::shortName($class['class']);
        
        if ($type == 'model') {

            /*
            * get the collection the model manages  
            */
            $collection = $class['collection'];

            /*
            * if the class is extending an existing class
            * use $extendedSchema, otherwise use $schema
            * and extend from the base class
            */
            if ($baseClass) {    

                $schema =  'extendedSchema';  

            } else {

                $schema =  'schema';
                $baseClass = $this->getFallbackBaseClass($type);

            }

            /*
            * format the output of the file from the template
            */
            return sprintf($template, $namespace, $baseClass, $name, $collection, $schema);


        } elseif ($type == 'controller') {

            /*
            * set the returnKey
            */
            $returnKey = array_get($class, 'return-key'); 

            /*
            * if no no class to extend
            * extend from the base class
            */
            if (!$baseClass) {   
                $baseClass = $this->getFallbackBaseClass($type);
            }

            /*
            * format the output of the file from the template
            */
            return sprintf($template, $namespace, $baseClass, $name, $returnKey);   
        
        }

    }

    /**
    * write a controller of model file
    * @param array $class
    * @return void
    */
    public function writeClass($class)
    {
        
        /*
        * get the class type (controller or model)
        */        
        $type = $this->parseClassType($class['class']);

        /*
        * ensure we have a template type
        */
        if (!$type)
            return self::CLASSNAME_INVALID;

        $template = $this->loadTemplate($type);

        /*
        * ensure we have a template
        */
        if (!$template)
            return self::TEMPLATE_NOT_FOUND;

        /*
        * do the filesystem work 
        */
        $path = $this->classNameToPath($class['class']);
        if (!$this->makeDirectories(dirname($path)))
            return self::CREATE_DIR_FAILED;

        /*
        * get the output to write
        */
        $output = $this->generateClassOutput($class, $type, $template);

        /*
        * write the file
        */
        file_put_contents($path, $output);

    }

    /**
    * when a parent class is not provided, get the base class
    * @param string $type
    */
    protected function getFallbackBaseClass($type)
    {
        $className = 'Base' . ucfirst($type);
        $baseClass = $this->getConfig('fallback-namespace');
        $baseClass = Utils\NamespaceHelper::extend($baseClass, ucfirst($type));
        $baseClass = Utils\NamespaceHelper::extend($baseClass, $className); 
        return $baseClass; 
    }

    /**
    * recursively make directories when needed
    * @param $directory
    * @return bool
    */
    protected function makeDirectories($dir)
    {
        /*
        * if this is a new directory
        * create the necessary directories
        */
        if (!is_dir($dir)) {
            try {
                mkdir($dir, 0777, true);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;

    }

    /**
    * write a new configuration file
    * @param array $data
    * @return void
    */
    public function writeConfig($data)
    {
        $data = "<?php\n\nreturn " . var_export($data, true);
        $data = str_replace(["  ", "array (", '\\\\'], ["    ", "array(", '\\'], $data) . ";";
        $path = $this->getConfig('base-config-file');
        file_put_contents($path, $data);
    }

}