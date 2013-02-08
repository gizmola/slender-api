<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class AdminCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'admin';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Setup cli key script to insert admin user and role.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

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
        $email = $this->ask('Enter email:');

        //Get password from console
        while (!$confirmed) {
            if (!$password) {
                $password = $this->ask('Enter password:');
            }

            $password2 = $this->ask('Confirm password:');
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
        ];

        $roleData = [
            'name' => 'Admin Role',
            'permissions' => $adminPermissions
        ];

        //Store role in db
        $response = $this->call('POST', '/roles', array(), array(), array(), json_encode($roleData));
        $response = json_decode($response->getContent(), true);

        $roleResponse = $response['roles'][0];

        $userData = [
            'first_name'    => 'Admin',
            'last_name'     => '',
            'email'         => $email,
            'password'      => $password,
            'roles'         => [$roleResponse['_id']],
            'permissions'   => $adminPermissions
        ];

        //Store user in db
        $response = $this->call('POST', '/users', array(), array(), array(), json_encode($userData));
        /*$response = json_decode($response->getContent(), true);
        $userResponse = $response['users'][0];*/

        $this->info('Storing in db');
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