# venue-bad
This is pre-composer, pre-framework ticketing system and website. The specific files are related to a sunset project for trustus theater in Columbia, SC.

## Setup
Its highly unlikely this application will ever work again. Its very tempermental and dependant on libraries that are no longer supported.
However, you're welcome to dig around for coding ideas in the pre-composer pre-framework landscape. 
* copy boffice_config.example.php to boffice_config.php and fill in the credentials
* to process credit card payments, fill in the `$merchant_authorize_dot_net_*` variables
* setup the db using both of the .sql files from `/setup/`
* (optional) setup the testing data by ingesting create_testing_data.php
* (optional) to generate barcode-printable tickets, you'll need http://www.barcodebakery.com/en/download/php 
  * I've removed it from this repo as its license is proprietary
  * It goes in `/shared_functions/barcodes/class` in its entirety (unzipped, of course)
  
## Why, oh why
This is a sunset project I am fairly proud of buidling. By contemporary standards, its pretty poor quality; but 
not all the application logic is brute-force... which makes for some interesting logic. Of particular interest is the 
CMS. 
