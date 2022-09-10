# Mosaico PHP Backend

This is a working PHP backend for Mosaico

This has been forked from https://github.com/markalston/mosaico-php-backend which seems to be no-longer maintained and has been rewrited using static php classes.

It has been tested with mosaico 0.18.6 installed in /var/www/mosaico and with document root as /var/www/mosaico.  If your apache setup is different you will probably have to change the paths in config.php.

Mosaico can be found at https://github.com/voidlabs/mosaico

First, install and set up Mosaico.  Then install these files on top of the Mosaico installation.

This install includes the directories upload, dl, and img, media (which should be copied into your main mosaico directory) 

## Dependencies

It is expected that you are running PHP and have Mosaico installed in the main folder.

You also do need to have Imagemagick support enabled in your PHP configuration.

## New folders and files

```
backend-php/config.inc.php 
```
In this file are a few variables that you can adjust if necessary. Please check this file and make sure all the paths are correct for your Mosaico installation, and that PHP can write files to those paths. If they are wrong or PHP cannot write files to those paths, your image uploads will not work.

```
backend-php/server.inc.php  
```
This is the PHP backend rewrited using various static class engine, used by the index.php files located in upload dir, img dir and dl dir, that handles the required functions:
* image uploads
* retrieving of a list of uploaded images
* downloading of the HTML email
* sending of the test email
* generating the placeholder images
* the resizing of images

```
/upload/index.php
```
* This file is used for image uploads and retrieving of a list of uploaded images calling using the class Mosaico_Server::get('uploads')

```php
<?php
include('../backend-php/server.inc.php');
Mosaico_Server::get('uploads');
```

```
/img/index.php
```
* This file is used for generating the placeholder images and the resizing of images using class Mosaico_Server::get('img')

```php
<?php
include('../backend-php/server.inc.php');
Mosaico_Server::get('img');
```

```
/dl/index.php
```
* This file is used for downloading of the HTML emailand sending of the test email using the class Mosaico_Server::get('dl')

```php
<?php
include('../backend-php/server.inc.php');
Mosaico_Server::get('dl');
```

```
/media/static/
```
Place where the static images are created


The PHP backend also generates static resized images when downloading the HTML email or sending the test email.

## Modified files

Editor.html no longer needs to be modified from the original mosaico one. 
