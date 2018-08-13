<p align="center"><img src="https://laravel.com/assets/img/components/logo-laravel.svg"></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
</p>

# Project NiBS(New IBS)

This is the new i-Elken Business Suites backend API system that covers all the existing i-Elken features with added capabilities.
This system is written using Laravel 5.x.

### Production
- Run <br>
php artisan db:seed --class=ProductionSeeder <br>

### Deployment
i-Elken backend system, so after cloning this repo, please follow these step to create your dev environment

- Clone and checkout 0.1/dev branch.
- Create Virtual Host something like http://ielken.dev
- Create your own branch.
- Cd your project folder.
- create .env file from .env.example
- Run => composer update.
- Update your .env file APP_URL=http://ielken.lan
- Run => php artisan migrate --step
- If this is the first time you migrate, then run => php artisan db:seed
- Run => php artisan passport:install 
- Run => php artisan storage:link
to generate the keys that passport needs to issue the tokens
- If this is not the first time you migrate, then run <br>
 php artisan db:seed --class=StagingSeeder <br>

or if you want to get the seeder file individually, then run =><br>
php artisan db:seed --class= <br> 
UserTypesSeeder <br>
OperationSeeder<br>
MasterSeeder<br>
CWScheduleSeeder<br>
TeamBonusRankSeeder<br>
EnrollmentRankSeeder<br>
CurrencySeeder<br>
LocationTypesSeeder<br>
CountryAndEntitySeeder<br>
StateAndCitySeeder<br>
BankSeeder<br>
SettingSeeder<br>
RunningNumberSeeder<br>
SalePaymentModeSeeder<br>
ProductCategorySeeder<br>
ProductSeeder<br>
ProductPriceSeeder<br>
MemberTreeSeeder<br>
MemberTreePyramidAlgoritmSeeder<br>
MemberSeeder<br>
MemberAddressSeeder<br>
TeamBonusSeeder<br>
SaleSeeder<br>
SaleAccumulationSeeder<br>
MemberRankTransactionSeeder<br>
WorkflowMasterSeeder<br>

- Run composer dump-autoload if there is an error seeder class not found

### Upload API Reference
* Make sure that these vars exists and configured correctly in your .env file <br>
UPLOADER_WORK_ENABLE <br>
UPLOADER_WORK_FILESYSTEM_DISK <br>
UPLOADER_WORK_ROOT <br>
UPLOADER_WORK_PATH <br>
UPLOADER_TEMP_FILESYSTEM_DISK <br>
UPLOADER_TEMP_ROOT <br>
UPLOADER_TEMP_PATH <br>
UPLOADER_TEMP_LINK <br>
UPLOADER_FILE_FILESYSTEM_DISK <br>
UPLOADER_FILE_ROOT <br>
UPLOADER_FILE_PATH <br>
UPLOADER_FILE_LINK <br>

* if s3 is used, ensure these vars exists and configured correctly in your .env file <br> 
AWS_KEY <br>
AWS_SECRET <br>
AWS_REGION <br>
AWS_BUCKET <br>

- run
php artisan storage:link <br>

* check config/setting.php and config/uploader.php for any required file like watermark etc exists


### API Reference
* [API for dev/0.1](https://docs.google.com/document/d/1geFn1OAvHVQsnJP_4HYsUUbxJZEpPHIzj_qLt7Np094/edit)
* on Google Drive after you open the api docs, for ease navigation go to Tools menu > Document Outline

### Coding Standard
* We will be using [PHP-Fig](http://www.php-fig.org)
* [psr-1](http://www.php-fig.org/psr/psr-1/),
[psr-2](http://www.php-fig.org/psr/psr-2/),
[psr-4](http://www.php-fig.org/psr/psr-4/)
* Variable name will be in camelCase.
* We only use underscore naming for array naming.

### Versioning
We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/your/project/tags). 


