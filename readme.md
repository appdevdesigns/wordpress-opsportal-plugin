# WP Ops Portal

> WordPress plugin for Ops Portal


### Prerequisite
* php v5.3.0+ or v7.0.x
* apache 2.4 with ```proxy_http``` module enabled
* mySql 5.6
* WordPress v4.0 or above
* php CURL Extension to talk with APIs on Ops Portal
* A running instance of Ops Portal on the same domain where WordPress is running.

### Setup this project on localhost
* **Install [WordPress](https://roots.io/bedrock/)**
```bash
composer create-project roots/bedrock wordpress "1.6.*"
```
* Copy ```.env.example``` to ```.env``` and update environment variables
```bash
cd wordpress
cp .env.example .env
nano .env
```
* Create a virtual host named ```wp-test.local``` that points to ```wordpress/web``` folder
* Install WordPress via [WP CLI](https://wp-cli.org/commands/core/install/)
```
wp core install --url='http://wp-test.local' --title='WP Test' --admin_user='yourname' --admin_password='password' --admin_email='admin@wp.local' --skip-email
```
* Login to wp-admin ```http://wp-test.local/wp/wp-admin```
* You can also check ```./config/environments/development.php``` file for various WP constants

* **Clone this plugin inside WordPress plugins folder**
```bash
cd web/app/plugins
git clone https://github.com/appdevdesigns/wordpress-opsportal-plugin.git
cd wordpress-opsportal-plugin
git checkout dev
```
* Set write permissions on ```logs``` folder if you want to debug CURL
```bash
sudo chmod -R 755 logs
sudo chown -R www-data:www-data logs
```

* **Install Ops Portal**
* Follow [this](https://github.com/appdevdesigns/opsportal_docs/blob/master/develop/develop_setup.md) guide to install Ops Portal
* Install [opstool-wordpress-plugin](https://github.com/appdevdesigns/opstool-wordpress-plugin), (develop branch)

* Ops Portal should be running on the sub-domain like: ```opsportal.wp-test.local``` in order to share cookies with WordPress
* Use [this](http://stackoverflow.com/questions/8541182/apache-redirect-to-another-port) apache conf file to configure Ops Portal
```
   # /etc/apache2/site-available/ops-portal.conf
   <VirtualHost *:80>
     ProxyPreserveHost On
     ProxyRequests Off

     ServerName opsportal.wp-test.local
     ServerAlias opsportal.wp-test.local

     ProxyPass / http://localhost:1337/
     ProxyPassReverse / http://localhost:1337/

   </VirtualHost>
```
* Make sure apache proxy module is enabled ```sudo a2enmod proxy_http```


### How to install this plugin in WordPress
- Download the plugin (zip) from GitHub (master branch)
- Login to WordPress Admin panel (wp-admin)
- Go through menus Plugin->Add New->Upload Plugin
- Upload the .zip file there.
- Activate the plugin when asked
- Go through Settings->Ops portal
- Configure plugin options and Save settings
- Add ```[ops_portal]``` short-code on a page to see ops portal in action

### WP CLI usage
* This plugin supports [WP CLI](http://wp-cli.org/)
* See commands available
```
wp opsportal
```
* Check sync status
```
wp opsportal status
```
* Bulk Sync users to Ops Portal
```
wp opsportal sync
```

#### License
MIT License

