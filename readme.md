
# APPMONITOR :: Client Checks #


# Description #

This repository with client checks extends the appmonitor repository. It contains checks for default php web aplications. This prevents to write the same checks for another instance of the same product again.

The client checks exist for

* Concrete 5 (Cms)
* Matomo (Analytics)


# Requirements #

You need to install the Appmonitor first.



The application monitor is an additional monitoring to the classic monitoring of a servers and its services. It makes checks from the point of view of the application. With its credentials and permissions.
https://github.com/iml-it/appmonitor

The client and server directory is needed for a monitoring server.

The client directory is needed on your application server.


# Installation #

## Install Appmonitor client ##

Install the appmonitor client on your web application.
https://github.com/iml-it/appmonitor

In the appmonitor client folder I expect the following structure:


```
|   (other checks)
|   general_include.php
|
\---classes
        appmonitor-checks.class.php
        appmonitor-client.class.php
```

## Add a custom check ##

Each check consists of 2 files:

* a script *check-[product].php* that contains the logic for checks checks
* a config-sample *check-[product].settings.sample.php* file.

What you need to do is:

- Download Archive of https://github.com/iml-it/appmonitor-clients
- Copy the needed files for your web app into the appmonitor client directory.
- Create a copy of the sample file and set your webroot
- Test json output
- Add https://exaple.com/appmonitor/client/check-[product].php in you appmonitor webgui


## Example: for the CMS Concrete5 ##


Copy check-concrete5* to have these files in the client directory:


```
|   check-concrete5.php
|   check-concrete5.settings.sample.php
|   general_include.php
|   readme.md
|
\---classes
        appmonitor-checks.class.php
        appmonitor-client.class.php
```

Copy *check-concrete5.settings.sample.php* to *check-concrete5.settings.php* (without ".sample").

Edit *check-concrete5.settings.php* and change application root if it is not the webroot.

Open https://exaple.com/appmonitor/client/check-concrete5.php in your webbrowser.
If this OK got to your appmonitor server gui - tab "setup" and and add the same url for a new client check.