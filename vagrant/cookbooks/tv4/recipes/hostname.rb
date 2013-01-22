bash "set_hostname" do
  user "root"
  code <<-EOH
    hostname tv4.local
  EOH
end