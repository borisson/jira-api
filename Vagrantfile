# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant::Config.run do |config|
  config.vm.box = "ctors_squeeze64_2012-12-03"
  config.vm.box_url = "http://ctors.net/squeeze64_2012_12_03.box"

  # Use :gui for showing a display for easy debugging of vagrant
  # config.vm.boot_mode = :gui

  # Some VirtualBoxes seem to need this
  config.vm.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]

  # whithout this symlinks can't be created on the shared folder
  config.vm.customize ["setextradata", :id, "VBoxInternal2/SharedFoldersEnableSymlinksCreate/v-root", "1"]

  # allow external connections to the machine
  config.vm.forward_port 80, 8080

  config.vm.define :jiraapi do |jiraapi_config|
    jiraapi_config.vm.host_name = "www.jiraapi.dev"

    jiraapi_config.vm.network :hostonly, "192.168.33.10"

    # Pass installation procedure over to Puppet (see `puppet/manifests/jiraapi.pp`)
    jiraapi_config.vm.provision :puppet do |puppet|
      puppet.manifests_path = "puppet/manifests"
      puppet.module_path = "puppet/modules"
      puppet.manifest_file = "jiraapi.pp"
      puppet.options = [
        '--verbose',
        # '--debug',
      ]
    end
  end
end
