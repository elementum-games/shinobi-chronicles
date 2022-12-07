# Overview

[Contributing](#contributing)  
[Local Setup](#local-setup)

## Contributing

Feel free to tackle any of the issues at https://github.com/levimeahan/shinobi-chronicles/issues - Assign yourself 
or drop a comment mentioning you're working on it so others know. You can also drop any questions on the issue to 
clarify exactly what needs to be done.

You can also create a new issue if there's something you want to add that's not on the list and get the yay/nay from a 
maintainer on it to avoid building something that's not going to get approved.

### Recommended workflow

1. Fork repo on GitHub (top right)  
  ![image](https://i.imgur.com/0tiyX6N.png) 
2. Clone your fork to your local machine (you can find the URL to clone under "Code")  
  ![image](https://i.imgur.com/VCUgDnG.png)
3. Create a new branch for your changes
4. Make the changes and test them on your local environment, then commit them to the branch
5. Push the branch to your forked repo on GH
4. Open a pull request from your fork/branch to the primary repo (`levimeahan/shinobi-chronicles`) and `main` branch
   - There's a "Contribute" button on the main page of your fork you can use to compare and open a pull request
    ![image](https://i.imgur.com/z5SCPuQ.png)

5. After pull request is merged (and before starting work on a new feature if it's been more than a day or two) update your local fork from the original repo  
   - You can either do this via the command line, or the GitHub UI
   - For command line:
     - First, add a remote of the primary repo to your local clone
       - `git remote add upstream https://github.com/levimeahan/shinobi-chronicles.git`
     - Then when you want to update your local, run
       - `git pull upstream main`
   - For GUI
     - Click the "Fetch upstream" prompt on the GitHub page for your fork
       - ![image](https://i.imgur.com/cQJ0zGc.png) 
     - Pull the changes from your GitHub fork to your local


## Local Setup

You need an environment with four things:
- A remote fork of the SC repository (e.g. `your-username/shinobi-chronicles`), on your GitHub account
- A local clone of your fork 
- A PHP local web server
- A MySQL database 

You can use any IDE and setup you want if you have the know-how, but this guide lays out one way to get 
started quickly with minimal PHP environment/Git knowledge.

1. Fork repo
2. Download PhpStorm (https://www.jetbrains.com/phpstorm/)
3. Setup PhpStorm 
   - Open from VCS -> GitHub
   - login to your account
   - choose shinobi-chronicles repository from the list
4. Download XAMPP (https://www.apachefriends.org/index.html)
5. Setup Apache in XAMPP
   - Go to apache > config > httpd.conf
   - Find DocumentRoot and change the path "C:\xampp\htdocs" to your shinobi-chronicles directory
     - (You can right-click the top folder in PhpStorm after opening it > copy path > absolute path)
   - Change the default path below in <Directory "C:\xampp\htdocs"> to your shinobi-chronicles directory
6. Setup MySQL in XAMPP
   - Go to MySQL > Admin/PhpMyAdmin > user accounts 
   - Go to add user
   - Enter the name `shinobi_chronicles` and give it a password (doesn't matter what it is, can just be "password") > check "create database with same name and grant all permissions"
7. Import the DB in XAMPP > MySQL
   - Select the database `shinobi_chronicles` in PhpMyAdmin
   - Click "Import" at the top menu
   - Click "Choose file" and navigate to your SC directory
     - Select `shinobi-chronicles/db/V1__schost_game_structure.sql` and proceed with the import
8. Setup game config in PhpStorm
   - Find `secure/vars.sample.php`
   - Make a copy of it as `secure/vars.php` 
   - Change the values to the ones from your database:
     - user = shinobi_chronicles 
     - database = shinobi_chronicles 
     - password = (whatever you set as your password)
9. Run it
    - First, make sure Apache and MySQL are both started in XAMPP
    - Then navigate to http://localhost/ and you should see the game come up
    

### Installing PHP Manually

**Ubuntu**
https://www.linode.com/docs/guides/install-php-8-for-apache-and-nginx-on-ubuntu/

**Windows**
https://www.php.net/downloads.php  
Then go to "Windows downloads"

### Composer

We use composer for managing some dependencies and info about PHP version. Download
at 
https://getcomposer.org/download/
and run `composer install`

### Docker compose
If you want to use docker before starting php set up your database with 
```
docker compose up -d
```

This will set up mysql and perform database migration in order to initialize database with needed data.
