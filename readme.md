# WP Ops Portal

> A WordPress plugin to enable the OpsPortal to run inside.


### Prerequisite
* php v5.3.0+ or v7.0.x
* apache v2.4 with ```proxy_http``` module enabled
* MySQL v5.6
* WordPress v4.0 or above
* php [cURL Extension](http://php.net/manual/en/book.curl.php) to talk with APIs on Ops Portal
* [Composer](https://getcomposer.org/download/) (If you want to use modern WordPress)
* [wp-cli](http://wp-cli.org/#installing) (If you want to use command line in WordPress)
* A running instance of Ops Portal on the same domain where WordPress is running.
    * If your WordPress is running on ```http://example.com```
    * The Ops Portal instance should be running on subdomain like: ```http://opsportal.example.com```
* _WordPress and Ops Portal may have its own additional requirements_

### Setup this project on localhost
* **Install [WordPress](https://codex.wordpress.org/Installing_WordPress)** good old way
* **Install [WordPress](https://github.com/roots/bedrock#installation)** modern way, steps below
* Download WordPress core and dependencies
```bash
cd ~
composer create-project roots/bedrock wordpress "1.7.*"
```
* Copy ```.env.example``` to ```.env``` and update environment variables
```bash
cd wordpress
cp .env.example .env
nano .env
```
* Create a [virtual host](https://httpd.apache.org/docs/current/vhosts/) named ```wp-test.local``` that points to ```wordpress/web``` folder
```
# Ubuntu, apache v2.4
# Example virtual host file
# /etc/apache2/sites-available/wordpress.conf
<VirtualHost *:80>

	ServerName wp-test.local
	ServerAdmin webmaster@localhost
	DocumentRoot /home/user_name/projects/wordpress/web

	<Directory "/home/user_name/projects/wordpress/web/">
		Order allow,deny
		AllowOverride All
		Allow from all
		Require all granted
	</Directory>

	ErrorLog ${APACHE_LOG_DIR}/error.log
	CustomLog ${APACHE_LOG_DIR}/access.log combined

</VirtualHost>

```
* Make an entry in ```/etc/hosts```
```
127.0.0.1 wp-test.local
```
* Enable this vhost ```sudo a2ensite wordpress.conf```
* Don't forget to restart apache service after creating vhost
* Seed WordPress database via [WP CLI](https://wp-cli.org/commands/core/install/)
```
wp core install --url='http://wp-test.local' --title='WP Test' --admin_user='yourname' --admin_password='password' --admin_email='admin@wp.local' --skip-email
```
* You can skip above command and manually install WordPress via web wizard
* Login to wp-admin ```http://wp-test.local/wp/wp-admin```  with credentials you pass in command above
* You can also check ```./wordpress/config/environments/development.php``` file for various php constants

* **Clone this plugin inside WordPress plugins folder**
```bash
cd web/app/plugins
git clone https://github.com/appdevdesigns/wordpress-opsportal-plugin.git
cd wordpress-opsportal-plugin
git checkout dev
```
* Set write permissions on ```logs``` folder, if you want to debug CURL calls
```bash
sudo chmod -R 755 logs
sudo chown -R www-data:www-data logs
```
* Plugin can write CURL response in ```logs``` folder for debugging

* **Install Ops Portal**
* Follow [this](https://github.com/appdevdesigns/opsportal_docs/blob/master/develop/develop_setup.md) guide to install Ops Portal
* Install [opstool-wordpress-plugin](https://github.com/appdevdesigns/opstool-wordpress-plugin), (node module)
```
cd sails
npm install git://github.com/appdevdesigns/opstool-wordpress-plugin#develop
```
* OpsPortal (sails) may need some additional configurations, for example cors, csrf etc.
* OpsPortal (sails) also requires to have authKeys setup in ```config/appdev.js```
```
'authKeys': {
  'admin': '<YOUR SECRET KEY HERE>'
 }
```
* Ops Portal (sails) should be running on the sub-domain like: ```opsportal.wp-test.local``` in order to share cookies with WordPress
* Use [reverse proxy](http://stackoverflow.com/questions/8541182/apache-redirect-to-another-port) apache method to configure Ops Portal to run on subdomain
```
   # Ubuntu, apache v2.4
   # /etc/apache2/site-available/opsportal.conf
   <VirtualHost *:80>
     ProxyPreserveHost On
     ProxyRequests Off

     ServerName opsportal.wp-test.local
     ServerAlias opsportal.wp-test.local

     ProxyPass / http://localhost:1337/
     ProxyPassReverse / http://localhost:1337/

   </VirtualHost>
```
* Make an entry in ```/etc/hosts```
```
127.0.0.1 opsportal.wp-test.local
```
* Enable this vhost ```sudo a2ensite opsportal.conf```
* Make sure apache [proxy module](https://httpd.apache.org/docs/current/mod/mod_proxy.html) is enabled
* Don't forget to restart apache service after creating vhost
* Start sails
```
cd ~/projects/sails
sails lift
```
* _Some command and paths may vary from platform to platform_

### How to install this plugin in WordPress - End User guide
- Download the plugin (zip) from GitHub (master branch for stable version)
- Login to WordPress Admin panel (wp-admin)
- Go through menus Plugins->Add New->Upload Plugin
- Upload the .zip file there.
- You can also use FTP to upload the zip contents
- Activate the plugin when asked
- Go through menus Settings->Ops Portal
- Configure plugin options and Save settings
- Add ```[ops_portal]``` short-code on a page to see Ops Portal in action

### WP CLI usage
* This plugin supports [WP CLI](http://wp-cli.org/)
* 1. Check sync status
```
wp opsportal status
# Also show list of users
wp opsportal status --list
```
* 2. Bulk Sync users to Ops Portal
```
wp opsportal sync
```

### Multilingual Support
* This plugin is multilingual, be sure to follow [guidelines](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/)
* Use [Poedit](https://poedit.net/download) to update translations before releasing new version

