package "git" do
  not_if "which git"
  action :install
end

package "subversion" do
  not_if "which svn"
  action :install
end