<?php

namespace Slender\API\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Dws\Slender\Api\Auth\Permissions;
use Slender\API\Model\Roles;
use Slender\API\Model\Users;

use Illuminate\Console\Command;
//use Symfony\Component\Console\Input\InputOption;
//use Symfony\Component\Console\Input\InputArgument;
//use Symfony\Component\HttpKernel\Client as BaseClient;

class AdminCommand extends Command
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'add-admin-user';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Setup cli key script to insert admin user and role.';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
        
        $confirmed = false;
        $password = null;

        $file = $this->argument('file');

        if ($file) {
            
            $data = file_get_contents($file);

            /*
            * if the file doesn't exist, exit out
            * with a code other than success (0)
            */
            if ($data === false) {
                die(1); 
            }

            $data = json_decode($data,true);
            $first_name = $data['first_name'];
            $last_name = $data['last_name'];
            $email = $data['email'];
            $password = $data['password'];

        } else {

            $this->info('Generating superadmin user');
            //Get email from console
            $first_name = $this->ask('Enter First Name: ');
            $last_name = $this->ask('Enter Last Name: ');
            $email = $this->ask('Enter Email: ');


            //Get password from console
            while (!$confirmed) {
                if (!$password) {
                    $password = $this->secret('Enter Password: ');
                }

                $password2 = $this->secret('Confirm Password: ');
                if ($password == $password2) {
                    $confirmed = true;
                } else {
                    $this->error('The passwords you entered do not match');
                    if (!$this->confirm('Do you wish to reconfirm? [yes - reconfirm password|no - reenter password]')) {
                        $password = null;
                    }
                }
            }

        }

        $adminPermissions = [
            '_global' => [
                'read'      => 1,
                'write'     => 1,
                'delete'    => 1,
            ],
        ];

        Permissions::normalize($adminPermissions);

        $roleData = [
            'name' => 'Global Admin Role',
            'permissions' => $adminPermissions,
        ];

        $roles = new Roles();

        $entity = $roles->getCollection()->where('name', $roleData['name'])->first();

        if (!$entity){
            $entity = $roles->insert($roleData);
        }

        $userData = [
            'first_name'    => $first_name,
            'last_name'     => $last_name,
            'email'         => $email,
            'password'      => $password,
            'roles'         => [(string) $entity['_id']]
        ];

        $users = new Users();
        $entity = $users->insert($userData);

        if ($entity['key']){

            if ($file) {

                $this->info($entity['key']);
                
            } else {

                $this->info('*---------------------------------------------------------------------------*');
                $this->info('');
                $this->info('User has been successfully created!');
                $this->info('Your Auth Key is: '. $entity['key']);
                $this->info('');
                $this->info('*---------------------------------------------------------------------------*');

            }

        }
	}

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('file', InputArgument::OPTIONAL, 'Provide a preconfigured user', null),
        );
    }

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array();
	}
}
