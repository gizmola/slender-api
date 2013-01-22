execute "create-yum-cache" do
  command "yum -q makecache"
  action :nothing
end

ruby_block "reload-internal-yum-cache" do
  block do
    Chef::Provider::Package::Yum::YumCache.instance.reload
  end
  action :nothing
end

bash "setup_epel" do
  not_if {File.exists?("/etc/yum.repos.d/remi.repo")}
  user "root"
  cwd "/tmp"
  code <<-EOH
    wget http://dl.fedoraproject.org/pub/epel/6/i386/epel-release-6-8.noarch.rpm
    wget http://rpms.famillecollet.com/enterprise/remi-release-6.rpm
    rpm -Uvh remi-release-6*.rpm
    rpm -Uvh epel-release-6*.rpm
    sed -i s/enabled=0/enabled=1/ /etc/yum.repos.d/remi.repo
    yum clean all
  EOH
  notifies :run, "execute[create-yum-cache]", :immediately
  notifies :create, resources(:ruby_block => "reload-internal-yum-cache"), :immediately
end
