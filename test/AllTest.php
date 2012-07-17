<?php

class AllTest
{
    public static $testClasses = array(
        '\ProgressBar\Test\RegistryTest',
        '\ProgressBar\Test\ManagerTest',
    );
    
    /**
     * Inits tests suite
     * 
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
    	$loader = require __DIR__.'/../vendor/autoload.php';
		$loader->add('ProgressBar', __DIR__);

    	$suite = new PHPUnit_Framework_TestSuite("PHP-CLI-Progress-Bar");
    	foreach (self::$testClasses as $testClass)
    	{
    		$suite->addTestSuite($testClass);
    	}
    	return $suite;
    }
}