# Mosaico PHP Backend

This is a working PHP backend for Mosaico

This has been forked from https://github.com/markalston/mosaico-php-backend which seems to be no-longer maintained and has been rewrited using static php classes.

It has been tested with mosaico 0.18.6 installed in /var/www/mosaico and with document root as /var/www/mosaico.  If your apache setup is different you will probably have to change the paths in config.php.

Mosaico can be found at https://github.com/voidlabs/mosaico

First, install and set up Mosaico.  Then install these files on top of the Mosaico installation.

This install includes the directories upload, dl, and img, media (which should be copied into your main mosaico directory) 

## Dependencies

It is expected that you are running PHP and have a working Mosaico installation in the main folder.

You also do need to have Imagemagick support enabled in your PHP configuration.

## New folders and files

```
backend-php/index.php 
```
This is the PHP backend rewrited using various static class engine, used by the index.php files located in php-backend dir and handles the required functions:
* image uploads
* retrieving of a list of uploaded images
* downloading of the HTML email
* sending of the test email
* generating the placeholder images
* the resizing of images
* the saving of the used template in mysql database


```
backend-php/index.php 
```
This is the file where user can choose templates from master templates or from the listed model saved in the database.
The user can, also, update/rename/delete the model saved in the database


```
backend-php/lib/config.inc.php 
```
In this file are a few variables that you can adjust if necessary. Please check this file and make sure all the paths are correct for your Mosaico installation, and that PHP can write files to those paths. If they are wrong or PHP cannot write files to those paths, your image uploads will not work.


```
/media/static/
```
Place where the static images are created


The PHP backend also generates static resized images when downloading the HTML email or sending the test email.

## Modified files

Editor.html no longer needs to be modified from the original mosaico one. 
