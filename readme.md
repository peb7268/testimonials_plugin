#Infosurv Testimonials plugin

##PSR-0 Autoloading
Autoloading has three main staple principles:  
- Each class must be namespaced with the project’s (or creator’s) name. 
- Underscores within the class’ name should be converted to directory separators. 
- Files must have the .php extension. 

For example: 
A class reference of: \Nettuts\Database\SQL_Postgres

would translate to a path of: 

./Nettuts/Database/SQL/Postgres.php 

//The composer.json file looks like: 

{
    "autoload": {
        "psr-0": {
            "Inf": "./",
            "Gmanricks": "vendor/"
        }
    }
}

Autoload all classes in the **Inf namespaced** files and use the current directory ( as the **base path** ).
The next line tells composer to autoload all of the files in the Gmanricks namespace in the vendor directory ( relative to the vendor folder so: ./vendor/Gmanricks/ClassName ).

####Require the autoloader 
Once your specify this setup you must require the autoloader 
require 'vendor/autoload.php'; 


##PSR-1 Coding Standard 

Naming conventions are: 
1. Class names use PascalCase 
2. method names use camelCase 
3. Constants are all caps deliniated by underscores.