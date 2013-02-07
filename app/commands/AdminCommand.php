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

        $name = $this->ask('Enter username:');

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

        //TODO: store superadin data in db
        $this->info('Storing ' . $name . ':' . $password . ' in db');
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