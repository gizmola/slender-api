# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant::Config.run do |config|
  config.vm.box = "centos-6.3-i386"
  config.vm.forward_port 80, 4003
  config.vm.forward_port 27017, 27019
  config.vm.forward_port 9000, 35000
#  config.vm.network :hostonly, "192.168.1.101"

  config.vm.provision :chef_solo do |chef|
	chef.cookbooks_path = "vagrant/cookbooks"
	chef.add_recipe("yum::epel")
	chef.add_recipe("yum::remi")
	chef.add_recipe("slender-api::nameservers")
	chef.add_recipe("slender-api::hostname")
	chef.add_recipe("slender-api::bash")
	chef.add_recipe("slender-api::scm")
	chef.add_recipe("slender-api::mongo")
	chef.add_recipe("slender-api::memcached")
	chef.add_recipe("slender-api::php")
	chef.add_recipe("slender-api::mongo")
	chef.add_recipe("slender-api::apache")
  end

end
