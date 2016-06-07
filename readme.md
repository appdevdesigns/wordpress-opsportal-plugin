# WP Ops Portal

> WordPress plugin for Ops Portal


### Prerequisite
* php v5.3.0 or v7.0.x
* WordPress v4.0 or above
* php CURL Extension

### Setup this project on localhost
* **Install [WordPress](https://roots.io/bedrock/)**
```bash
composer create-project roots/bedrock wordpress "1.6.*" --prefer-dist
```
* Copy ```.env.example``` to ```.env``` and update environment variables
```bash
cd wordpress
cp .env.example .env
nano .env
```
* Create a virtual host (example: wp-test.local) that points to ```web``` folder
* Open ```http://wp-test.local``` and go through WordPress installation process, it should not ask you database credentials because you already have them in ```.env```
* You can also check ```./config/environments/development.php``` file for constants

* **Clone this plugin inside WordPress plugins folder**
```bash
cd web/app/plugins
git clone https://github.com/ithands/wp-ops-portal.git
cd wp-ops-portal
git checkout dev
```
* Give write permissions on ```logs``` folder if you want to debug CURL
```bash
sudo chmod -R 775 logs
sudo chown -R www-data:www-data logs
```

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
[License](LICENSE.txt)

