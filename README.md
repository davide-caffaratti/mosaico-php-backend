# Mosaico PHP Backend

This is a working PHP backend for Mosaico that can save the used templates in a mysql database.

This has been forked from https://github.com/markalston/mosaico-php-backend which seems to be no-longer maintained and has been rewrited using static php classes with autoloader.

It has been tested with mosaico 0.18.6 installed in /var/www/newsletter/ and with document root as /var/www/newsletter/ and the url app http://localhost/newsletter/ in my testing local server. If your apache setup is different you will probably have to change the urls and the paths and the mysql configuration in your /backend-php/lib/config.inc.php.

First, install and set up Mosaico.  
Then install these files on top of the Mosaico installation and create the database, using thr file cat_mosaico_tpl.sql, in your mysql server.


Mosaico can be found at https://github.com/voidlabs/mosaico


## Dependencies

It is expected that you are running PHP and have a working Mosaico installation in the main folder.

You also do need to have Imagemagick support enabled in your PHP configuration.


## New folders and files

```
/index.php 
```
This is the file where user can choose templates from master templates or from the listed model saved in the database.
The user can, also, update/rename/delete the model saved in the database


```
/editor.php 
```
This is the modified Mosaico editor needed for use the functions for save the used template in a mysql database


```
/backend-php/.htaccess
```
File for rewriting the url of the php-backend

```
/backend-php/index.php 
```
This is the PHP backend rewrited using various static class engine, used by the index.php files located in php-backend dir and handles the required functions:
* Uploads images
* Retrieving a list of uploaded images
* Downloading the HTML email
* Sending test email
* Generating the placeholder images
* Resizing images
* Saving the used template in mysql database
* Preview the current email
* Send the hash of the current email to your application back-office page


```
/backend-php/lib/ 
```
Folder with the necessary lib for Mosaico Server.


```
/backend-php/lib/Mosaico/ 
```
Folder with the classes used by Mosaico Server.


```
/backend-php/lib/interface-lang/ 
```
Folder with the translation in 3 languages(Italian, English and Spanish) used by Mosaico Server and all the Server system.


```
/backend-php/lib/config.inc.php 
```
In this file are a few variables that you need to adjust. Please check this file and make sure all the paths are correct for your Mosaico installation, and that PHP can write files to those paths. If they are wrong or PHP cannot write files to those paths, your image uploads will not work.


```
/backend-php/lib/autoload.inc.php 
```
Simply autoloader class.


```
/backend-php/lib/db.inc.php 
```
Class for manage the Mysql functions.


```
/backend-php/lib/server.inc.php 
```
Main class for the backend server.


```
/media/static/
```
Place where the static images are created

```
/upload/thumb/ 
```
Folder inside the upload folder with miniature images for gallery pickup.


The PHP backend also generates static resized images when downloading the HTML email or sending the test email.

## Modified files

if no need to use the save templates function editor.html no longer needs to be modified from the original mosaico one. 


## Backend images



| The index.php page with the model getting from mysql database  |
| ------------- |
| ![The index.php page](https://user-images.githubusercontent.com/82267325/191492919-6f32580b-f8d0-4b81-9bfd-41413b91f009.png) |



| The editor with the send email button  | The Email preview with link for send the email |
| ------------- | ------------- |
| ![Screenshot 2022-09-21 at 23-56-11 HTML Email Editor](https://user-images.githubusercontent.com/82267325/191620750-91e997c0-67c6-4b01-9a02-37ce0bd404f7.png) | ![Screenshot 2022-09-21 at 23-57-00 HTML Email Editor-Preview](https://user-images.githubusercontent.com/82267325/191620520-8fdecba9-5221-4623-a95f-2b3f187e3832.png) | 



