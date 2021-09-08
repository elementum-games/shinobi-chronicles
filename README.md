## Contributing

Recommended workflow

1. Fork repo on GitHub (top right)  
  ![image](https://i.imgur.com/0tiyX6N.png) 
2. Clone your fork to your local machine (you can find the URL to clone under "Code")  
  ![image](https://i.imgur.com/VCUgDnG.png)
3. Make changes to your local and test, push to your forked repo on GH
4. Open a pull request from fork => original repo
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
     - You should see a prompt on the main page of your fork repository when it's behind, with a button to click (Image TBD)



### Local Setup

WIP rough version of one way:
- Fork repo
- Download PhpStorm (https://www.jetbrains.com/phpstorm/)
- Open from VCS -> GitHub -> login to your account -> choose shinobi-chronicles repository from the list
- Download XAMPP (https://www.apachefriends.org/index.html)
- Go to apache > config > httpd.conf
- Find DocumentRoot > change the path "C:\xampp\htdocs" to your shinobi-chronicles directory (you can right-click the top folder in PhpStorm after opening it > copy path > absolute path)
- change the default path below in <Directory "C:\xampp\htdocs"> to your shinobi-chronicles directory
- Go to MySQL > Admin/PhpMyAdmin > user accounts > add user "shinobi_chronicles" > give it a password (doesn't matter what it is, can just be "password") > check "create database with same name and grant all permissions"
- Go to PhpStorm, find secure/vars.sample.php and change the values to the ones from your database: user = shinobi_chronicles, database = shinobi_chronicles, password = (whatever you set)

