case node['platform_family']
when "rhel", "fedora"
  %w{ zlib-devel }.each do |pkg|
    package pkg do
      action :install
    end
  end
  php_pear "xdebug" do
    action :install
  end
when "debian"
  package "php5-xdebug" do
    action :install
  end
end