# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant::Config.run do |config|
  config.vm.box = "centos-6.3-i386"
  config.vm.forward_port 80, 4003
  config.vm.forward_port 27017, 27019

  config.vm.provision :chef_solo do |chef|
	chef.cookbooks_path = "vagrant/cookbooks"
	chef.add_recipe("tv4::nameservers")
	chef.add_recipe("tv4::hostname")
	chef.add_recipe("tv4::bash")
	chef.add_recipe("tv4::epel")
	chef.add_recipe("tv4::scm")
#	chef.add_recipe("tv4::mongo")
	chef.add_recipe("tv4::php")
	chef.add_recipe("tv4::apache")
  end

end
