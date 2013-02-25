bash "install_memcached" do
  user "root"
  code <<-EOH
    yum install -y memcached
    iptables -I INPUT -p tcp --dport 27018 -j ACCEPT
    service iptables save
    service memcached start
  EOH
  action :nothing
end

template "/etc/sysconfig/memcached" do
  not_if "which memcached"
  source "memcached.erb"
  notifies :run, "bash[install_memcached]", :immediately
end