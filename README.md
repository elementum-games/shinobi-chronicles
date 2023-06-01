# Overview

[Contributing](#contributing)  
[Local Setup](#local-setup)

## Contributing

Feel free to tackle any of the issues at https://github.com/levimeahan/shinobi-chronicles/issues - Assign yourself 
or drop a comment mentioning you're working on it so others know. You can also drop any questions on the issue to 
clarify exactly what needs to be done.

You can also create a new issue if there's something you want to add that's not on the list and get the yay/nay from a 
maintainer on it to avoid building something that's not going to get approved.

Once you've created a pull request, ping `@Lsmjudoka` on the [#sc-coding](https://discord.gg/EnHxMGHhqe) Discord channel for review.

### Large Features

If you want to work on a large feature (either in the Issues tab or not), the recommendation is to write up a proposal with the details (from a gameplay perspective) and post it in the [#sc-coding](https://discord.gg/EnHxMGHhqe) channel on discord, tagging @Lsmjudoka for review. Then the details of the feature and compensation can be agreed upon before work is started.

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

You need an environment with four to five things:
- A remote fork of the SC repository (e.g. `your-username/shinobi-chronicles`), on your GitHub account
- A local clone of your fork 
- A PHP local web server
- A MySQL database 
- A locally installed copy of Node.js and NPM (only if you are working on React-based UIs like combat)

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
7. Setup game config in PhpStorm
    - Find `secure/vars.sample.php`
    - Make a copy of it as `secure/vars.php`
    - Change the values to the ones from your database:
        - user = shinobi_chronicles
        - database = shinobi_chronicles
        - password = (whatever you set as your password)
8. Install Composer
    - Windows:
        - Download and run https://getcomposer.org/Composer-Setup.exe
    - Mac:
        - See directions here https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos
9. Use Composer to install important dependencies
   - Open your command line/terminal, navigate to your SC directory, and run `composer install`
10. Import the DB using Phinx
    - *(Phinx is automatically installed when you run `composer install` in the previous step)*
    - In your command line/terminal, navigate to your SC directory and run 
      - `vendor/bin/phinx seed:run`
    - This will populate your database with all the relevant tables and sample data
13. Run it
     - First, make sure Apache and MySQL are both started in XAMPP
     - Then navigate to http://localhost/ and you should see the game come up
    

### Installing PHP Manually

- Ubuntu
  - https://www.linode.com/docs/guides/install-php-8-for-apache-and-nginx-on-ubuntu/
- Windows
  - Go to https://www.php.net/downloads.php and then look for "Windows Downloads"

### Composer

We use composer for managing some dependencies and info about PHP version. 
- Windows:
    - Download and run https://getcomposer.org/Composer-Setup.exe
- Mac:
    - See directions here https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos

Once installed, navigate to your SC directory in your CLI and run `composer install`.

### Database Migrations

We use [Phinx](https://phinx.org/) for simple migration management. The documentation on writing migrations is here:
https://book.cakephp.org/phinx/0/en/migrations.html

**Initial Setup**
- Make sure you have installed Composer and run `composer install` to install Phinx.
- Run `vendor/bin/phinx seed:run` to populate your database with tables/data.
    - If you have existing database structure, this will not run. You need to drop all tables and re-run the command.

**Quick Cheatsheet**  
- Run all migrations
  - `vendor/bin/phinx migrate`
  - Useful if you've just pulled some changes from GitHub that include DB migrations
- Create a migration
  - `vendor/bin/phinx create MyCoolMigration`
  - If your DB is up to date except for this new migration, you can run the `vendor/bin/phinx migrate` command to execute it
- Run all migrations up to a specific version
  - `vendor/bin/phinx migrate -t <timestamp>`
  - The timestamp is the filename, in the `db/migrations` folder. The filename should look like `20110103081132_my_cool_migration.php`, in this case `20110103081132` is the timestamp.
- Rollback the most recent migration
  - `vendor/bin/phinx rollback`
- Rollback all migrations to a specific version
  - `vendor/bin/phinx rollback -t <timestamp>`
  - Optionally you can specify a date instead of the full timestamp:
  - `vendor/bin/phinx rollback -d 20230101`

See `/db/SampleSQLMigration.php` for a simple example of creating a migration that uses raw SQL queries.

### Docker compose
Before starting docker, it's recommended to install dependencies with `composer install` (step 9 at the beginning of this readme).
Otherwise, they'll be re-installed every time you destroy the docker image or upgrade a dependency version.

If you want to use docker before starting php set up your database with 
```
docker compose up -d
```

This will set up mysql and perform database migration in order to initialize database with needed data.

Note that if you have some code that relies on Composer's `vendor/autoload.php` and you're getting
`Class or interface "MyNewClass" does not exist` errors, you may need to run 
`composer dump-autoload`. If that does not resolve it, you may need to add its directory to
the `classmap` in `composer.json`.

### Installing Required PHP Extensions
This varies by system, but generally:

**Windows**  
- Find your php.ini file (php --ini from command line may help)
- search for the extension name without prefix (e.g. if composer tells you to install `ext-mbstring`)
- You should see a line like `;extension=mbstring.dll` - Remove the semicolon and save the file

**Linux**  
These extensions are usually managed by your package manager, try 
installing, prefixed with `php<version>-`. Examples

Ubuntu: `sudo apt-get install php8.0-mbstring`

CentOS / RedHat: `sudo yum install php8.0-mbstring`

**Note about ext-dom**
  - This extension is included in the `xml` extension, install that


### Node.js and NPM

We use Node.js and NPM to install Javascript packages for some advanced 
UI interfaces using [ReactJS](https://reactjs.org/) and other libraries, such as the Battle page. 
(see the `ui_components` directory for the full list)

If you are not making changes to any of these pages, you do not need to install Node.js and NPM.

- Recommendation is to use a Node version manager so you can easily change version. See instructions here: 
  - [Using a Node version manager to install Node.js and npm](https://docs.npmjs.com/downloading-and-installing-node-js-and-npm#using-a-node-version-manager-to-install-node-js-and-npm)
- Once installed, navigate to the root SC directory and run `npm install`

### Advanced UI Components
Some of the syntax used in these components (e.g. JSX) is not natively available in browsers, 
so we have to compile the source code into plain JavaScript for it to run in browsers.
- The editable source code lives in `ui_components/src`.
- The compiled output lives in `ui_components/build`
    - Do not make changes to code in the `build` folder, as it will be overwritten when the components are compiled.

- When developing advanced UI components, navigate to the root SC directory in a terminal window
and run `npm run watch-ui` to automatically compile source files from the `src` directory to 
  compiled version in the `build` directory.

#### PhpStorm file watcher setup
- Program: `$ProjectFileDir$\node_modules\.bin\babel`  
- Arguments: `$FilePathRelativeToProjectRoot$ --out-dir ui_components/build/$FileDirRelativeToSourcepath$`  
- Output paths to refresh: `$FileDirRelativeToSourcepath$/ui_components/build/$FileNameWithoutExtension$.js`  
- Working Directory: `$ContentRoot$`  

Scope
- Set this string in pattern: `file[shinobi-chronicles]:ui_components/src//*`
- The key point is just "files inside `ui_components/src`


## Testing
You will need to install Composer and run `composer install` to install the PHPUnit
testing framework. 

Run `composer test` to run all tests.

For more on writing tests with PHPUnit, see the docs for PHPUnit here:
https://phpunit.readthedocs.io/en/9.5/
  
## Remote Server Setup reference (CentOS 7)

#### Install PHP 8 CLI 
- `yum install -y https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm`
- `yum install -y https://rpms.remirepo.net/enterprise/remi-release-7.rpm`
- `yum install -y --enablerepo=remi-php80 php php-cli php-mysqlnd`

#### Install dependencies and setup database
- `composer install`
- `vendor/bin/phinx seed:run`
- `vendor/bin/phinx migrate`

