<?php
namespace OpenCloud;

error_reporting(0);

define('RAXSDK_OBJSTORE_NAME','cloudFiles');
define('RAXSDK_OBJSTORE_REGION','DFW');

require_once('rackspace.php');
require_once('objectstore.php');

class OpencloudClient {

    protected $config;
    protected $connection;
    protected $container;

    public function __construct($config)
    {
        $this->config = $config;
        $this->connect();
        $this->setContainer();


    }

    public function connect()
    {
        $this->connection = new Rackspace(
            $this->config['authUrl'],
            array(
                'username' => $this->config['username'],
                'apiKey' => $this->config['key']
            )
        );
    }

    public function setContainer()
    {
        $ostore = $this->connection->ObjectStore();

        $cont = $ostore->Container();
        $cont->Create(array('name' => $this->config['container']));
        $this->container = $cont;
    }

    public function uploadFile($target, $newName = '')
    {
        if (is_file($target)) {
            $mime = mime_content_type($target);
            if (!$newName) {
                $newName = basename($target);
            }
            $obj = $this->container->DataObject();
            $obj->Create(
                array(
                    'name' => $newName,
                    'content_type' => $mime
                ),
                $target
            );
        }
    }

    public function downloadFile($target, $fileName)
    {
        $obj = $this->container->DataObject($fileName);
        $obj->SaveToFilename($target);
    }

    public function deleteFile($fileName)
    {
        $obj = $this->container->DataObject($fileName);
        $obj->Delete();
    }

    public function getList()
    {
        //$objlist = $this->container->ObjectList(array('prefix'=>'photo'))
        $files = array();
        $objlist = $this->container->ObjectList();
        while($object = $objlist->Next()) {
            $item = array(
                'name' => $object->name,
                'bytes' => $object->bytes,
                'content_type' => $object->content_type,
                'last_modified' => $object->last_modified
            );
            $files[] = $item;;
        }

        return $files;
    }
}


