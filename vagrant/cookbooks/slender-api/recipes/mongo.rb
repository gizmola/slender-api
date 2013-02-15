bash "install_mongo" do
  user "root"
  code <<-EOH
    yum install -y mongo-10gen-server
    chkconfig mongod on
    iptables -I INPUT -p tcp --dport 27017 -j ACCEPT
    service iptables save
    service mongod start
        pear channel-discover pear.phing.info
        pear install --alldeps phing/phing 
        pear channel-discover pear.pdepend.org
        pear install pdepend/PHP_Depend
        pear channel-discover pear.phpmd.org
        pear install phpmd/PHP_PMD
  EOH
  action :nothing
end

template "/etc/yum.repos.d/10gen.repo" do
  not_if "which mongo"
  source "10gen.repo"
  notifies :run, "bash[install_mongo]", :immediately
end