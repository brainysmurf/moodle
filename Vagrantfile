# -*- mode: ruby -*-
# vi: set ft=ruby :

# Install the 'hostsupdater' plugin for vagrant then run 'vagrant up' in this directory!
# $ vagrant plugin install vagrant-hostsupdater

VAGRANTFILE_API_VERSION = "2"



Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

  # All Vagrant configuration is done here. The most common configuration
  # options are documented and commented below. For a complete reference,
  # please see the online documentation at vagrantup.com.

  # The base box to use
  config.vm.box = "ubuntu/trusty64"

  config.vm.provision "shell",
    path: "https://raw.githubusercontent.com/classroomtechtools/dragonnet/master/provision.sh"

  config.vm.network "private_network", ip: "192.168.70.10"
  config.vm.hostname = "dragonnet.vagrant"

  # Makes it accessible locally at dragonnet.vagrant (modifies the hosts file)
  config.hostsupdater.aliases = ["dragonnet.vagrant"]

  # Makes this directory available at /var/www/moodleclone/docroot on the virtual server
  config.vm.synced_folder "./", "/var/www/moodleclone/docroot",  owner: "www-data", group: "www-data"
  config.vm.synced_folder "../ssispowerschoolsyncer", "/home/vagrant/ssispowerschoolsyncer",  owner: "vagrant", group: "vagrant"
  config.vm.synced_folder "../powerschool", "/home/vagrant/powerschool",  owner: "vagrant", group: "vagrant"

  # This allows the virtual machine to use the host OS's VPN connection
  config.vm.provider :virtualbox do |vb|
      vb.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
      vb.customize ["modifyvm", :id, "--memory", "2048"]
  end

  config.vm.define :dragonnet do |t|
  end

end
