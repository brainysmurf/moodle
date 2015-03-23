#!/bin/bash

# Add sources
add-apt-repository -y ppa:nginx/stable
add-apt-repository -y ppa:ondrej/php5
add-apt-repository -y ppa:git-core/ppa

# postgres
echo "deb http://apt.postgresql.org/pub/repos/apt/ trusty-pgdg main" >> /etc/apt/sources.list.d/pgdg.list
wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add -

# Update sources
apt-get -y update

# Install packages
apt-get -y install nginx
apt-get -y install git
apt-get -y install postgresql-9.3 postgresql-client-9.3
apt-get -y install php5-common php5-cli php5-fpm php5
apt-get -y install php-pear php5-cgi php5-curl php5-dev php5-fpm php5-imap php5-intl php5-odbc php5-pgsql php5-sybase php5-xmlrpc php5-gd

# Download config repo
if [ -d "dragonnet-vagrant" ]; then
    cd dragonnet-vagrant
    git pull
else
    git clone git@bitbucket.org:ssis/dragonnet-vagrant.git
    cd dragonnet-vagrant
fi

# Remove default nginx site
rm /etc/nginx/sites-enabled/default

# Copy modified configs
cp -rT etc /etc

# Run startup stuff
/etc/rc.local

# Make the database
sudo -u postgres psql -c "create user moodle password 'helloworld';"
sudo -u postgres createdb dragonnet

if [ -f "/vagrant/dragonnet.sql" ]
then
    # Import database
    sudo -u postgres psql dragonnet < /vagrant/dragonnet.sql
fi

# Create Moodle data directory
mkdir -p /var/www/moodleclone/moodledata
chown www-data:www-data /var/www/moodleclone/moodledata

if [ ! -f /var/www/moodleclone/docroot/config.php ]; then
    # Copy moodle config
    cp moodle/config.php /var/www/moodleclone/docroot/config.php
    chown www-data:www-data /var/www/moodleclone/docroot/config.php
fi

# Add Moodle cron
hascron=`sudo -u www-data crontab -l | grep "php /var/www/moodleclone/docroot/admin/cron.php" | wc -l`
if [ $hascron -ne 0 ]
then
     echo "Moodle cron already configured"
else
     sudo -u www-data crontab -l | { cat; echo "*/5 * * * * php /var/www/moodleclone/docroot/admin/cron.php"; } | sudo -u www-data crontab -
     echo "Moodle cron installed"
fi

# Restart services
service nginx restart
service php5-fpm restart
service postgresql restart

date > /etc/vagrant_provisioned_at

echo "Good to go!"
