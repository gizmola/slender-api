bash "composer" do
    user "root"
    not_if "which composer"
    code <<-EOH
        curl -sS https://getcomposer.org/installer | php
        mv composer.phar /usr/local/bin/composer
    EOH
end