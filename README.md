PHP-CLI-Progress-Bar
====================

A PHP5 CLI Progress bar
Version 0.0.4

Requirements
============

PHP >= 5.3

How it works
============

There is one namespace ProgressBar that contains 2 classes Manager and Registry.

Manager is responsible to manage the progress bar. Each instance of this class is associated with a 
Registry object. Each time the Manager needs to keep a metric, it is stored in this object.

When the display is requested, the script uses the string format and iterates over 
all replacement rules. Replacements are handled by closures.

The progress bar has the following default output : 
%current%/%max% [%bar%] %percent%% %eta%

It is configurable. You can also change it while processing your batch script.

Buit-in variable replacement are : 
* %current% : the current element
* %max% : the number of elements
* %bar% : the progress bar
* %percent% : the advancement in percent
* %eta% : estimation of the remaining

Manager constructor arguments :
* current : the initial step
* max : the amount of steps in your process
* width : the max width of the line (default : 80)
* doneBarElementCharacter : a character to identify done advancement in the progress bar (default : =)
* remainingBarElementCharacter : a character to identify remaining advancement in the progress bar (default : -)
* currentPositionCharacter : a character to identify the current position in the progress bar (default : >)


How to use
==========

Quick start
-----------

Add include statements at the beginning of your script (if you don't have autoloaders)

```php
<?php
require_once 'ProgressBar/Manager.php';
require_once 'ProgressBar/Registry.php';

$progressBar = new \ProgressBar\Manager(0, 10);

for ($i = 0; $i <= 10; $i++)
{
    $progressBar->update($i);
    sleep(1);
}
```

Will output : 

1/10 [===>----------------------------------------------] 10.00% 00:00:09

When you want to iterate over a collection, you don't event need to track the counter:

```php
foreach ($array as $element)
{
    // process element
    $progressBar->advance();
}
```

Configuration
-------------

### Changing the output ###

Use the setFormat() method : 

```php
$progressBar->setFormat('%current% |%bar%| %max%');
$progressBar->update(1);
```

Will output : 

1|>-------------------------------------------------------------------| 10


### Changing the max length ###

The max length is specified in the constructor :  
 
```php
$pb = new \ProgressBar\Manager(0, 20, 120);
$progressBar->update(1);
```


Will output :

1/20 [====>----------------------------------------------------------------------------------------] 5.00% 00:00:00


### Changing the progress bar style ###

Use the parameters specified in the constructor : 

```php
$pb = new \ProgressBar\Manager(0, 20, 120, '-', ' ', ')');
$pb->update(5);
```

Will output :

5/20 [-----------------------)                                                                    ] 25.00% 00:00:00

 
Extending
=========

Adding custom replacement rules
-------------------------------

You may add your custom variables for replacement. You have to use the method addReplacementRule and specify a priority, a tag and a closure.
Keep in mind that the length of the progress bar is evaluated at the end so that the output width will scale up to the width you specified.
So keeping the %bar% with a high priority is a good practice.

Here is an example of what you should do if you want to add a new replacement rule.

```php
<?php

use ProgresBar;

$pb = new Manager(0, 213);
$pb->setFormat('Progress : %current%/%max% [%bar%] %foo%');
$pb->addReplacementRule('%foo%', 70, function ($buffer, $registry) {return 'OK!';});
$pb->update(1);
```

Will echo : 

Progress : 1/213 [>---------------------------------------------------] OK!


ChangeLog
=========

0.0.3 -> 0.0.4
--------------
* Changed RuntimeExceptions to InvalidArgumentExceptions
* Added advance() method for incrementing the progress bar
* Forbid to set the progress value greater than expected

0.0.2 -> 0.0.3
--------------
* Added composer support
* Added unit tests for the Manager and the registry

0.0.1 -> 0.0.2
--------------
* Changed directory structure to add namespace
* Changed priority behavior
* Added new public method addReplacementRule so that you don't need to extend the manager to add custom replacement rules

TODO
====
* ask developpers for feedback
