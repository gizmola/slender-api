# install the mongodb pecl
php_pear "mongo" do
  action :install
end

template "#{node['php']['ext_conf_dir']}/20-mongo.ini" do
  source "mongo.ini.erb"
  owner "root"
  group "root"
  mode "0644"
end