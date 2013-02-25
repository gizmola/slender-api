package "php-devel" do
  action :nothing
end

package "php" do
  not_if "which php"
  action :install
  notifies :install, "package[php-devel]", :immediately
end

package "php-xml" do
  not_if "php -m | grep xmlreader"
  action :install
end

package "php-mbstring" do
  not_if "php -m | grep mbstring"
  action :install
end

package "php-memcached" do
  not_if "php -m | grep memcached"
  action :install
end

package "php-pear" do
  not_if "which pecl"
  action :install
end

package "php-pecl-mongo" do
  not_if "php -m | grep mongo"
  action :install
end

package "php-pecl-xdebug" do
  not_if "php -m | grep Xdebug"
  action :install
end

package "php-pecl-xhprof" do
  not_if "php -m | grep xhprof"
  action :install
end

template "/etc/php.d/xdebug.ini" do
  source "xdebug.ini.erb"
  mode 0744
end

template "/etc/php.d/mongo.ini" do
  source "mongo.ini.erb"
  mode 0744
end

template "/etc/php.d/memcached.ini" do
  source "memcached.ini.erb"
  mode 0744
end

template "/etc/php.d/slender-api.ini" do
  source "php.ini.erb"
  mode 0744
end

package "php-mcrypt" do
  not_if "php -m | grep mcrypt"
  action :install
end