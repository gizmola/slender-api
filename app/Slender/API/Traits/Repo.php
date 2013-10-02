<?php namespace Slender\API\Traits;

use Dws\Slender\Api\Model\Factory as RepoManagerFactory;

trait Repo {


    /**
    * repository manager factory
    *
    * @var RepoManagerFactory
    */
    protected $repoManagerFactory;

    /**
    * array of repo managers
    *
    * @var array Dws\Slender\Api\Model\BaseModel
    */
    protected $repos = [];
    
    /**
    * get a repository manager factory
    * 
    * @return RepoManagerFactory
    */
    public function getRepoManagerFactory()
    {
        
        if (!isset($this->repoManagerFactory))
            $this->repoManagerFactory = new RepoManagerFactory();

        return $this->repoManagerFactory;
    
    }

    /**
    * set the repository manager factory
    *
    * @param RepoManagerFactory
    * @return Slender\API\Event\Subscriber\PushSubscriber
    */
    public function setRepoManagerFactory($factory)
    {
        
        $this->repoManagerFactory = $factory;
        return $this;   
    
    }

    /**
    * get a repo manager
    *
    * @return Dws\Slender\Api\Model\BaseModel
    */
    public function getRepoManager($name)
    {

        if (!isset($this->repos[$name]))
            $this->repos[$name] = $this->getRepoManagerFactory()
                ->build($name, $this->getSite());

        return $this->repos[$name];

    }

    /**
    * set a repo manager
    *
    * @param string $name
    * @param Dws\Slender\Api\Model\BaseModel $manager
    * @return Slender\API\Event\Subscriber\PushSubscriber
    */
    public function setRepoManager($name, $manager)
    {
        
        $this->repos[$name] = $manager;
        return $this;

    }

}