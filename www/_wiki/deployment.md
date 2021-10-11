# Deploying this application

The application is designed to be deployed in one of 5 ways.

 * As a 'localhost' test server behind a web-server for you to develop;
 * As a 'localhost' test server using PHP dev servers for you to develop;
 * As a running host with local resources (MySQL etc) - a Raspberry PI for example;
 * On the Google App Engine platform as a single app;
 * On the Google App Engine platform as a distributed app.

Once things have stabilised, I may look at [Digital Ocean](https://www.digitalocean.com/) as their scalable droplet solution seems a little cheaper than Googles as well as being easier for my brain to get to grips with.

In any event, regardless of the final destination you must install the software on a local host development system initially, so let's cover that below.

## Assumptions

This is not a cut-and-paste guide. For the purposes of this document the key assumption is that that you know a little bit about what you are doing, as well as a little bit about what you are trying to do. Commands provided here are based on a raspberry pi based install. This is primarily because that environment starts with very little, and it's very consistently this way. If you are running on windows, your command line interface is radically different and I don't have any exposure to that. If you run a Mac, you may be able to get things working, but the versions of PHP you will need comes from [homebrew](https://brew.sh/) and the apache you get is installed in lots of different ways to a regular unix. So basically I have to assume you have an operating system you know and a development environment you understand. 

The other reason for a Raspberry Pi is that the final standalone server that runs all the TOR services will be a Raspberry Pi. You could just use the setup script and have a Raspberry Pi up and running in 10 minutes.

## Prerequisites

You will need to set up a Google project because you will need to get a RECAPTCHA token (and if you want to deploy your application to google later). I can't cover that here as it may be quite convoluted if you've not done it before but there will be project selector on your [Google cloud console](https://console.cloud.google.com/home). 

You will need to [set up a V3 RECAPTCHA](https://www.google.com/recaptcha/admin) - the one that uses a score rather than the tick-box. You will need to ensure that `localhost` as well as all URL's you will use to access your system including those in dev are in the allowed domains list. The recommendation from google is that you use one RECAPTCHA for production and one for Dev.

You will need an SMTP email address to send system emails from. If you have set up the RECAPTCHA token and project above, then you already have a gmail account so that will be fine. If you use Muti-Factor Authentication, you will need to set up a application key for that email address - this will be your `$smtp_password` in the config files. How you do this will depend on whether you use GSuite or just plain gmail. Google provide [these instructions](https://support.google.com/accounts/answer/185833?hl=en)

For a local and single host based deployment you will require the following:

 * PHP 7.4 - the standard modules should be sufficient, but if you get errors, look to add the missing modules. Setting this up is a tutorial in itself. I install modules inline with the google module list. The command for this is below, but will only work if you have the correct repositories installed (see the assumptions section above, but this is covered in the Raspberry Pi install).
 * [Composer](https://getcomposer.org/) is required to ensure the application dependancies are met. How you get this will vary on your system.
 * Optionally, a web server, I'd recommend Apache2 as it's a lot simpler, but Nginx also works. If you do this, you will need to force every web call through the `index.php` file in the project root. Apache is easier to do this as you just need to enable a couple of modules.
 * You can skip the web server install and use the inbuilt PHP server, however this will only work on the local machine and will only run a single thread at any one time. It __REALLY__ is only for testing purposes.

```language-console
sudo apt install -y mariadb-server php7.4 php7.4-fpm php7.4-BCMath php7.4-bz2 php7.4-Calendar php7.4-cgi php7.4-ctype php7.4-cURL php7.4-dba php7.4-dom php7.4-enchant php7.4-Exif php7.4-fileinfo php7.4-FTP php7.4-GD php7.4-gettext php7.4-GMP php7.4-iconv php7.4-intl php7.4-json php7.4-LDAP php7.4-mbstring php7.4-mysql php7.4-OPcache php7.4-Phar php7.4-posix php7.4-Shmop php7.4-SimpleXML php7.4-SOAP php7.4-Sockets php7.4-tidy php7.4-tokenizer php7.4-XML php7.4-XMLreader php7.4-XMLrpc php7.4-XMLwriter php7.4-XSL 
```

# Local development
 
The scripts used in the application expect to be run from `/webroot/minertor` but if you're not stashing your files there, the site components will still work fine. The scripts should also work fine if you run them from the project home directory that GIT provides. Assuming you have GIT installed, and are in the correct directory (`/webroot` for the purposes here) let's go ahead and configure the application. I have assumed that the user you will be doing this all under is `pi` so adjust as you need.

```language-console
cd /webroot
sudo git clone https://github.com/nigeljohnson73/minertor.git
sudo chown -R pi:pi minertor
cd minertor
sudo mysql --user=root < res/setup_root.sql
sudo mysql -uroot -pEarl1er2day < res/setup_db.sql
cp res/install/config_* www
cd www
composer install
```

Now we need to make some directories for the blockchain to live in, and this does need to be built into the config somewhere, but isn't at the moment.

```language-console
sudo mkdir -p /var/minertor
sudo chown -R pi:pi /var/minertor
sudo chmod -R 777 /var/minertor
```

Finally, we need to deploy some keys and things. The command will ask you for a bundle from the dev server, don't worry, just press return as this is what you are creating.

```language-console
cd ..
sh/populate_config.sh
```

You will now be able to update the values for the RECAPTCHA and SMTP components you set up earlier. These will be in the `config_override.php` file.

That is the application configured. You now need to decide how you want to make it visible.

## Serving a local application

By far the easiest way to do this is via apache. Assuming you don't have it installed, you will need to do that first.

### Apache

Ensure the server is installed and the PHP module is linked up to it.

```language-console
sudo apt install -y apache2 libapache2-mod-php7.4 
```

The simplest way from here is just to juggle the directory Apache uses and adding the allow overrides section into the config. Don't just blindly do this without knowing what's going on - your config scripts may be in a different place.

```language-console
cd /var/www/
sudo mv html html_orig
sudo ln -s /webroot/minertor html
sudo bash -c 'cat >> /etc/apache2/apache2.conf' << EOF
<Directory /var/www/>
	Options Indexes FollowSymLinks
	AllowOverride All
	Require all granted
</Directory>
EOF
```

Now the key bits are to enable the rewrite module and restart apache.

```language-console
sudo a2enmod rewrite
sudo a2enmod actions
sudo /etc/init.d/apache2 restart
```

That should be it. You should be able to go to your browser and you should see a login screen.

Finally, if you're wanting to do the miner testing and conversion of transactions into blocks, you'll need to set up your crontab.

```language-console
* * * * * curl -o /tmp/minertor_tick.log http://localhost/cron/tick >/dev/null 2>&1
* * * * * curl -o /tmp/minertor_minute.log http://localhost/cron/every_minute >/dev/null 2>&1
3 * * * * curl -o /tmp/minertor_hour.log http://localhost/cron/every_hour >/dev/null 2>&1
9 3 * * * curl -o /tmp/minertor_day.log http://localhost/cron/every_day >/dev/null 2>&1
```

### Nginx

If you really must, then I assume you know how it all works... which is a step further than I do. This is the config that works for me. I provide no warranty, or explanation, other than it sends all requests through the `index.php` file in the root of the project.

```language-console
server {
    listen       80;
    server_name  _;
    root         /var/www/html;

    location / {
        fastcgi_connect_timeout 3s;
        fastcgi_read_timeout 10s;
        include fastcgi_params;
        fastcgi_param  SCRIPT_FILENAME  $document_root/index.php;
        fastcgi_pass unix:/run/php/php7.4-fpm.sock;
    }
}
```

You will also need the crontab entries listed above.

### Local PHP servers

You can bypass all the complexities of a webserver and run PHP in local server mode. This also kind of mimics how the google platorm will work in distributed mode. Note however that the restrictions on this are severe. You can only access the services from localhost, and you can only process one connection at a time. 

 * Start 3 terminal windows
 * Goto the main code directory in all terminals
 * `cd /webroot/minertor` 
 * In the first window, start the test server for the web service
 * `php -S localhost:8080 -t www www/index.php`
 * In the second window start a test server for the API service
 * `php -S localhost:8085 -t api api/index.php`
 * In the third window, start the test server for the cron handler
 * `php -S localhost:8090 -t cron cron/index.php`

You will need to adjust a few parameters to get the application communicating with itself. In the `config_localhost.php` file you will need to add a couple of lines.

```language-php
$api_host = "http://localhost:8085/api/";
$www_host = "http://localhost:8080/";
```

You will also need to edit your crontab if you're using them. Because of the single-threaded nature of the server, the 'tick' call is disabled for testing.

```language-console
#* * * * * curl -o /tmp/minertor_tick.log http://localhost:8090/cron/tick >/dev/null 2>&1
* * * * * curl -o /tmp/minertor_minute.log http://localhost:8090/cron/every_minute >/dev/null 2>&1
3 * * * * curl -o /tmp/minertor_hour.log http://localhost:8090/cron/every_hour >/dev/null 2>&1
9 3 * * * curl -o /tmp/minertor_day.log http://localhost:8090/cron/every_day >/dev/null 2>&1
```
