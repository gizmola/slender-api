package "httpd" do
  not_if "which httpd"
  action :install
end

package "httpd-devel" do
  not_if "rpm -q http-devel"
  action :install
end

bash "setup_apache" do
  user "root"
  cwd "/tmp"
  code <<-EOH
    iptables -I INPUT -p tcp --dport 80 -j ACCEPT
    service iptables save
  EOH
end

template "/etc/httpd/conf/httpd.conf" do
  source "httpd.conf.erb"
  mode 0744
end

service "httpd" do
  action [:enable,:start]
end