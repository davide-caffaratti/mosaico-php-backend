# Mosaico PHP Backend

This is a (now working) PHP backend for Mosaico

This has been forked from https://github.com/ainterpreting/mosaico-php-backend which seems to be completely dead and no-longer maintained.

Mosaico can be found at https://github.com/voidlabs/mosaico

First, install and set up Mosaico.  Then install these files on top of the Mosaico installation.

## Dependencies

It is expected that you are running Apache with mod_rewrite support enabled.

You also do need to have Imagemagick support enabled in your PHP configuration.

## New folders and files
```
backend-php/config.php
```
In this file are a few variables that you can adjust if necessary.  Please check this file and make sure all the paths are correct for your Mosaico installation, and that PHP can write files to those paths.  If they are wrong or PHP cannot write files to those paths, your image uploads *will not* work.

```
/backend-php/index.php
```
This is the PHP backend engine that handles the required functions:
* image uploads
* retrieving of a list of uploaded images
* downloading of the HTML email
* sending of the test email
* generating the placeholder images
* the resizing of images

The PHP backend also generates static resized images when downloading the HTML email or sending the test email.


## Modified files

```
editor.html
```
This example file has been slightly modified to work with the php backend. You may possibly need to configure this file as well.

## Other install requirements
You must also create the directories /upload, /dl, and /img in your mosaico main directory (not the backend-php directory) and add an .htaccess file to each of those directoryies with the following code:
```
RewriteEngine On
RewriteRule ^(.*)$ /backend-php/index.php [QSA,L]
```
I hope to remove this requirement soon as having to have these be actual directories is pretty stupid.