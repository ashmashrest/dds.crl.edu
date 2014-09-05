CRL DDS Application
=======================

Introduction
------------
This is a CRL Digital delivery systems build on Zend Framework 2.

Web Server Setup
----------------
- Clone config/autoload/local.php.dist and update the config/autoload/local.php 

External Projects Used
----------------------
pdf.js
Shibboleth module for ZF2

### Apache Setup

To setup apache, setup a virtual host to point to the public/ directory of the
project and you should be ready to go! It should look something like below:

    <VirtualHost *:80>
        ServerName localhost
        DocumentRoot /path/to/zf2-tutorial/public
        SetEnv APPLICATION_ENV "development"
        <Directory /path/to/zf2-tutorial/public>
            DirectoryIndex index.php
            AllowOverride All
            Order allow,deny
            Allow from all
        </Directory>
    </VirtualHost>
