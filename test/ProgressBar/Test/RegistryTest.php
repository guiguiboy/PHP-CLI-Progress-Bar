<?php
namespace ProgressBar\Test;

use ProgressBar\Registry;

class RegistryTest extends \PHPUnit_Framework_TestCase
{
    public function testSetAndGetValue()
    {
    	$registry = new Registry();
    	$registry->setValue('foo', 'bar');
    	$this->assertEquals('bar', $registry->getValue('foo'));
    	
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetNonExistingKeyThrowsRuntimeException()
    {
    	$registry = new Registry();
    	$registry->getValue('baz');
    }
}