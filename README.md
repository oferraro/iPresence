
 Run the server
 - php -S localhost:8000 project/api.php 

 System requirements
 - This was created using PHP 7.4.4
 - It has Guzzle for requests (no needed if only mocked requests are use), only for url resources

 TODO
 - Implements the API for getting n quotes of a famous person
 - Return the quotes "shouted"
 - Use a cache from a client / time period

 Nice to have
 - Classes Autoloader
 - config.ini (for php)

 Details
 - Manage sources, the API consumes quotes from different sources
    - Url, Mock (files), cached requests
 - Since Laravel is not allowed for the test and the idea is have something simple to run from cmd, I have decided to use just PHP with no framework
 - There are some comments in the code, just to clarify the idea of some parts of the code
 - For the same reason of simplicity I have used sQlite for the cache, this avoid dependencies to install, my first choice was redis and everything in a Docker, but it should make more complicate run in it anywhere easily 

 Constrains
 - Allow only from 1 to 10 quotes
 - Require number of quotes and famous person name
 
 Api Return
 - A json with an array of quotes
 - The quotes should be return in uppercase and ending with an exclamation mark
 
 Resources
 - Example URL: http://awesomequotesapi.com/shout/steve-jobs?limit=2
 