
 Run the server
 - php -S localhost:8000 project/api.php 

 TODO
 - Implements the API for getting quotes
 - Use a cache from a client /  time period

 Nice to have
 - Classes Autoloader
 - config.ini (for php)

 Details
- Manage sources, the API consumes quotes from different sources
    - Url, Mock (files), cached requests

 Constrains
 - Allow only from 1 to 10 quotes
 - Require number of quotes and famous person name
 
 Api Return
 - A json with an array of quotes
 - The quotes should be return in uppercase and ending with an exclamation mark
 
 Resources
 - Example URL: http://awesomequotesapi.com/shout/steve-jobs?limit=2