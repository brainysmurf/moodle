DragonNet
====

### Setting up a DragonNet development/testing environment

#### Required Software
* [git](http://git-scm.com/)
* [Virtualbox](https://www.virtualbox.org/wiki/Downloads)
* [Vagrant](https://www.vagrantup.com/)
* Vagrant hostsupdater plugin. (Install by running `vagrant plugin install vagrant-hostsupdater`)

#### Setup

##### 1. Clone this repository
```bash

git clone https://github.com/classroomtechtools/dragonnet
cd moodle
git submodule init
git submodule update
```

##### 2. Grab a copy of the database
```bash
scp -C lcssisadmin@wiki.ssis-suzhou.net:~/latest.sql dragonnet.sql
```
Call it dragonnet.sql and put it in the moodle directory.

##### 3. Start the Vagrant server
```bash
vagrant up
```
NOTE: At this point you will need a username and password for the ssis/dragonnet-vagrant repo on Bitbucket. Ask one of us for access.

The first time you start the server it will provision itself (install all the required software) and import the database. This may take a long time.
If asked for a password, enter your local sudo password. This is so it can update your hosts file to make dragonnet.vagrant point to the virtual server.

##### 4. Use it
Go to https://dragonnet.vagrant in your browser.
If you see a warning about SSL certificates, ignore it and choose to proceed. This is because dragonnet.vagrant uses a self-signed certificate.
![](http://img.ctrlv.in/img/14/11/15/5466b71a45838.png)
If everything went right, you should see DragonNet. The green header reminds you that this is the test version not the live version.
![](http://img.ctrlv.in/img/14/11/15/5466b70d32d77.png)


#### Database

[pgAdmin](http://www.pgadmin.org/) is a useful tool for managing Postgres databases. You can connect to the database inside the virtual server using these settings in pgAdmin:

![](http://img.ctrlv.in/img/14/11/15/5466bcedb61a3.png)
The default database password is helloworld.
