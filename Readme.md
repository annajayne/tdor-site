# Trans Lives Matter: Remembering Our DeadThis is the source of a website ([https://tdor.translivesmatter.info/](https://tdor.translivesmatter.info/)) intended to serve as a companion resource for Transgender Day of Remembrance (TDoR) events.It came about as a result of the experience of preparing for the 2017 vigil in Bournemouth (UK) - as part of which we presented what we knew about each victim on a memorial card so that people at the vigil could light a candle for each of them.You can read what happened in the blogpost [**TDoR 2017: Say their names. Learn their stories. Remember them**](https://medium.com/@annajayne/tdor-2017-say-their-names-learn-their-stories-remember-them-b81d50fd8ef).## Getting StartedThe project is written in PHP, and uses a simple MySQL database.Source code is stored in a Git repository. To start working on the code, first clone the project:Using Git:```git clone https://github.com/annajayne/tdor-site.git```*or*, using Mercurial (with the [hggit extension](https://hg-git.github.io/)):```hg clone git://github.com/annajayne/tdor-site.git```This will give you a copy of the source code and associated resources, but no data (we'll come to that shortly).### PrerequisitesThe project has been tested with PHP 5.3.29 + MySQL 5.5.59  and later.The site was developed in Visual Studio 2015 with the PHP Tools extension (1.26.10606.2015 or later) but both are far from essential. If you wish to work on the command line or in another dev environment that's fine too - but either way you'll probably also need the PHP debugger xDebug if you prefer not to resort to (ugh) printf style debugging.On dev machines a MySQL Workbench, PHPMyAdmin or equivalent installation is optional, but useful.As an alternative to Visual Studio + PHP Tools combination on Windows, try [EasyPHP DevServer](http://www.easyphp.org/) OR [WAMP](http://www.wampserver.com/en/). Regardless, if any changes are needed to support your workflow/platform I'll do my best to accommodate them.### InstallationTo work on the project you first need to install PHP and MySQL. If you are using Visual Studio + PHP Tools, you can do this in one step - the PHP Tools installer can be downloaded as a 30 day trial from [https://www.devsense.com/](https://www.devsense.com/). If you are not using Visual Studio + PHP Tools, you'll need to configure the xDebug PHP debugger manually.[Visual Studio specific] To test if it's working, just create a skeleton project and hit F5. If you see the homepage, your environment is working!## Line endings, tabs and stuff.This is a spaces not tabs project.I'm not overly bothered about line endings, but if I had to choose I'd go with LF rather than CRLF for obvious reasons.## Running the testsI haven't written any tests for this project as yet (I know...) but I hope to change that once I get the hang of running PHP locally and figure out how to effectively test this sort of project. PHPUnit is the likely vehicle for this.## DeploymentThere are several steps to deployment on a remote host, but you can skip some of these if you are developing locally:1. Upload the code (not necessary for local dev environments)2. Configure MySQL credentials3. Upload sample data4. Create and configure the MySQL database###1.  Uploading the code (remote hosts only)Code can be uploaded manually, but to make it easier there is a **Deploy** folder containing a (Windows only for now, but I'll be happy to reimplement in Python if that would make life easier for anyone) **MakeDeploymentZip.bat** script. This will zip up all source files and resources needed *apart* from **deploy.php** (located in the same folder) and **db_credentials.php** (located in the TDoR folder).To deploy the files, run **MakeDeploymentZip.bat**, then copy the resultant **tdor_deploy.zip** to the server, along with **deploy.php**. Once both files have been uploaded, run deploy.php to extract the files. The site will then be ready to run once the database connection has been configured.Leave the resulting page open in your browser as it includes a "Rebuild Database" link you'll need later.###2.  Configuring MySQL credentialsEdit  **db_credentials.php** with the credentials for your MySQL database, as defined in MySQL Workbench (told you it would come in useful!) or in your web host's control panel.Upload this file into the root directory alongside deploy.php etc.###3.  Uploading sample dataYou can obtain sample data from the  [tdor-data repo](https://github.com/annajayne/tdor-data). The relevant bits of the spreadsheet are .csv files (one for each month) and a folder containing photos of some of the victims.Upload a copy of the files to a **data** folder under the root of the project (if you upload a zipfile, that works too).###4.  Creating and configuring the MySQL databaseBy now you should be ready to extract the data files uploaded above and configure the database. Click the "Rebuild Database" link generated by deploy.php to extract and import the contents of tdor_2018.zip and check if there are any errors. If there are none, the site *should* be ready to go.## ContributingPlease read [**Contributing.md**](Contributing.md) for details on our code of conduct, and the process for submitting pull requests to us.## VersioningNot yet defined, though using [SemVer](http://semver.org/) for versioning might be a good idea. ## Authors* **Anna-Jayne Metcalfe** - *Initial work* - [@annajayne](https://twitter.com/annajayne)## LicenseThis project is licensed under the MIT License - see the [**Licence.md](Licence.md) file for details.## Acknowledgments* A huge thanks to [**Revd. Dwayne Morgan**](http://www.inclusive.church/our-pastor/) of [**Inclusive Community Church**](http://inclusive.church), whose idea to produce memorial cards for each victim for the Bournemouth TDoR 2017 vigil was the spark that lit this particular flame.* A hat tip to anyone whose code was used, in particular to Neil Rosenstech for his [**A Most Simple PHP MVC Beginners Tutorial**](https://web.archive.org/web/20180428063826/http://requiremind.com/a-most-simple-php-mvc-beginners-tutorial/), which formed the starting point of this project.* A *massive* thanks to the members and admins of the [**Trans Violence News**](https://www.facebook.com/groups/1570448163283501) Facebook group, without whose support this site would not be possible. There is just too much horror in the stories behind TDoR for any one person to cope with alone.* Thanks to the many, many trans activists and allies worldwide who put so much effort into reporting on and drawing attention to violence against trans people. You know who you are. x