bash "install_mongo" do
  user "root"
  code <<-EOH
    yum install -y mongo-10gen-server
    chkconfig mongod on
    iptables -I INPUT -p tcp --dport 27017 -j ACCEPT
    service iptables save
    service mongod start
  EOH
  action :nothing
end

template "/etc/yum.repos.d/10gen.repo" do
  not_if "which mongo"
  source "10gen.repo"
  notifies :run, "bash[install_mongo]", :immediately
end