bash "nameservers" do
  user "root"
  cwd "/etc/sysconfig/network-scripts"
  code <<-EOH
    echo 'DNS1=8.8.8.8' >> ifcfg-eth0
    echo 'DNS2=8.8.4.4' >> ifcfg-eth0
    ifdown eth0
    ifup eth0
  EOH
end