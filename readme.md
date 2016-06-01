# WP Ops Portal

> WordPress plugin for Ops Portal


### Prerequisite
* php v5.3.0 or v7.0.x
* WordPress v4.0 or above
* php CURL Extension

### Setup on localhost

* Clone on your machine
```
git clone https://github.com/ithands/wp-ops-portal.git
cd wp-ops-portal
git checkout dev

```

* Give write permissions on ```logs``` folder if you want to debug CURL
```
sudo chmod -R 775 logs
sudo chown -R www-data:www-date logs
```

### Plugin Installation Guide
- Download the plugin (zip) from github (master branch)
- Login to WordPress Admin panel
- Go through menus Plugin->Add New->Upload Plugin
- Upload the .zip file there.
- Activate the plugin when asked
- Go through Settings->Ops portal
- Set BaseURL of Ops Portal (pre-installed)
- Save settings
- Add ```[ops_portal]``` short-code on a page to see ops portal in action


#### License
[License](LICENSE.txt)

