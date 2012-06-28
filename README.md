PHP-CLI-Progress-Bar
====================

A PHP5 CLI Progress bar
Version 0.1a

Requirements
============

PHP > 5.3

How it works
============

There are 2 classes ProgressBar and ProgressBarRegistry.

ProgressBar is responsible to manage the output of the progress bar. Each instance of this class is associated with a 
ProgressBarRegistry. Each time the ProgressBar needs to keep a metric, 
it is stored in this object.

When the display is requested, the script uses the string format and iterates over 
all replacement rules. Replacement are handled by closures.

The progress bar has the following default output
%current%/%max% [%bar%] %percent%% %eta%

Feel free to change the template.
Buit-in variables are : 
* %current% : the current element
* %max% : the number of elements
* %bar% : the progress bar
* %percent% : the advancement in percent
* %eta% : estimation of the remaining

Constructor arguments :
* current : the initial step
* max : the amount of steps in your process
* width : the max width of the line (default : 80)
* doneBarElementCharacter : a character to identify done advancement in the progress bar (default : =)
* remainingBarElementCharacter : a character to identify remaining advancement in the progress bar (default : -)
* currentPositionCharacter : a character to identify the current position in the progress bar (default : >)


Examples
==========

Add include statements at the beginning of your script

<?php
require_once 'src/ProgressBar.php';
require_once 'src/ProgressBarRegistry.php';


Echo a sample progress bar

$progressBar = new ProgressBar(0, 10);

for ($i = 0; $i <= 10; $i++)
{
    $progressBar->update($i);	
    sleep(3);
}

Will output 
1/10 [>----------------------------------------------] 0.00% Calculating...

Changing the output : 

$progressBar->setFormat('%current% |%bar%| %max%');
$progressBar->update(1);

Will output : 

1|>-------------------------------------------------------------------| 10

 
Extending
=========

You may add your custom variables for replacement.
Keep in mind that the length of the progress bar is evaluated at the end so that the width will never exceed the limit.
So keeping the %bar% at the end is a good practice.

Here is an example of what you should do if you want to add a new replacement rule.

<?php

class MyProgressBar extends ProgressBar
{
	protected function getReplacementsRules()
    {
    	return array_merge(parent::getReplacementsRules(), array(
    		'%foo%' => function ($buffer, $registry) {return 'OK!';}
    		});
    }
}

$pb = new MyProgressBar(0, 213);
$pb->setFormat('Progress : %current%/%max% [%bar%] %foo%');
$pb->update(1);

Will echo 

1/10 [>----------------------------------------------] OK!

