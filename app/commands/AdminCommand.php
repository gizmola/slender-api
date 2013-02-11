<?php

use App\Model\Roles;
use App\Model\Users;

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
        $this->info('Generating superadmin user');
        $confirmed = false;
        $password = null;


        //Get email from console
        $first_name = $this->ask('Enter First Name:');
        $last_name = $this->ask('Enter Last Name:');
        $email = $this->ask('Enter Email:');


        //Get password from console
        while (!$confirmed) {
            if (!$password) {
                $password = $this->ask('Enter Password:');
            }

            $password2 = $this->ask('Confirm Password:');
            if ($password == $password2) {
                $confirmed = true;
            }
            else {
                $this->error('The passwords you entered do not match');
                if (!$this->confirm('Do you wish to reconfirm? [yes - reconfirm password|no - reenter password]'))
                {
                    $password = null;
                }
            }
        }

        $adminPermissions = [
            'global' => [
                'users' => [
                    'read'      => 1,
                    'write'     => 1,
                    'delete'    => 1,
                ],
                'roles' => [
                    'read'      => 1,
                    'write'     => 1,
                    'delete'    => 1,
                ],
                'sites' => [
                    'read'      => 1,
                    'write'     => 1,
                    'delete'    => 1,
                ],
            ]
        ];

        $roleData = [
            'name' => 'Global Admin Role',
            'permissions' => $adminPermissions
        ];

        $roles = new Roles();

        $entity = $roles->getCollection()->where('name', $roleData['name'])->first();
        
        if(!$entity)
        {
            $entity = $roles->insert($roleData);
        }

        $userData = [
            'first_name'    => $first_name,
            'last_name'     => $last_name,
            'email'         => $email,
            'password'      => $password,
            'roles'         => [(string)$entity['_id']]
        ];

        $users = new Users();
        $entity = $users->insert($userData);

        if($entity['key']){
            $this->info('*---------------------------------------------------------------------------*');
            $this->info('');
            $this->info('User has been successfully created!');
            $this->info('Your Auth Key is: '. $entity['key']);
            $this->info('');
            $this->info('*---------------------------------------------------------------------------*');
        }
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array();
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