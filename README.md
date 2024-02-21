# yii2-connectife
 Modulo per integrare la  Yii con Connectife
 
 [Connectife Technical API Documentation](https://api-docs.connectif.cloud/connectif-api/guides/introduction)
 
 Installation
 ------------
 
 The preferred way to install this extension is through [composer](http://getcomposer.org/download/).
 
 Run
 
 ```
 composer require "magicalella/yii2-connectife" "*"
 ```
 
 or add
 
 ```
 "magicalella/yii2-connectife": "*"
 ```
 
 to the require section of your `composer.json` file.
 
 Usage
 -----
 
 1. Add component to your config file
 ```php
 'components' => [
     // ...
     'connectife' => [
         'class' => 'magicalella\connectife\Connectife',
         'apiKey' => 'API KEY Connectife',
         'endpoint' => 'ERL API Connectife',
         'method' => 'POST'
     ],
 ]
 ```
 
 2. Add new contact to Connectife . Patch a contact by email. If contact does not exists, it is created
 ```php
 $connectife = Yii::$app->connectife;
 $result = $connectife->call('purchases',[
     '_email'=> 'string',
     '_birthdate'=> 'date-time',
     '_emailStatus'=> 'string',
     '_mobilePhone'=> 'international',
     '_mobilePhoneStatus'=> 'string',
     '_name'=> 'string',
     '_newsletterSubscriptionStatus'=> 'string',
     '_points'=> 'integer',
     '_smsSubscriptionStatus'=> 'string',
     '_surname'=> 'string'
     ]
 );
 ```
 
 Check [Connectife Technical API Documentation](https://api-docs.connectif.cloud/connectif-api/guides/introduction) for all available options.
