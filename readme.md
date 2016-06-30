# WP Ops Portal

> WordPress plugin for Ops Portal


### Prerequisite
* php v5.3.0+ or v7.0.x
* WordPress v4.0 or above
* php CURL Extension
* A running instance of Ops Portal

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
wp core install --url='http://wp-test.local' --title='WP Test' --admin_user='admin' --admin_password='admin' --admin_email='admin@wp.local' --skip-email
```
* Open ```http://wp-test.local/wp/wp-admin``` and login to wp-admin
* You can also check ```./config/environments/development.php``` file for various constants

* **Clone this plugin inside WordPress plugins folder**
```bash
cd web/app/plugins
git clone https://github.com/ithands/wp-ops-portal.git
cd wp-ops-portal
git checkout dev
```
* Set write permissions on ```logs``` folder if you want to debug CURL
```bash
sudo chmod -R 755 logs
sudo chown -R www-data:www-data logs
```

* **Install Ops Portal**
* Follow [this](https://github.com/appdevdesigns/opsportal_docs/blob/master/develop/develop_setup.md) guide to install Ops Portal
* Install [opstool-wordpress-plugin](https://github.com/appdevdesigns/opstool-wordpress-plugin)

### Plugin Installation Guide for End User
- Download the plugin (zip) from GitHub (master branch)
- Login to WordPress Admin panel
- Go through menus Plugin->Add New->Upload Plugin
- Upload the .zip file there.
- Activate the plugin when asked
- Go through Settings->Ops portal
- Configure plugin options and Save settings
- Add ```[ops_portal]``` short-code on a page to see ops portal in action


#### License
MIT License

