<?php

namespace ProgressBar\Test;

use ProgressBar\Manager;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * As Manager prints on stdout, we use output buffering for the tests
     */
	public function setUp()
    {
    	ob_start();
    }
    
    /**
     * Clean output buffer after each test
     */
    public function tearDown()
    {
    	ob_clean();
    }
    
    /**
     * Test format getter and setter
     */
	public function testManagerFormat()
    {
    	$manager = new Manager(0, 10);
    	$this->assertEquals("%current%/%max% [%bar%] %percent%% %eta%", $manager->getFormat());
    	$manager->setFormat('%current%/%max% [%bar%] %percent%%');
    	$this->assertEquals("%current%/%max% [%bar%] %percent%%", $manager->getFormat());
    }
    
    /**
     * Tests update
     * When there is still work to do : print the bar with \r
     * When it s finished : print the bar with \n
     */
    public function testUpdate()
    {
    	$manager = new Manager(0, 10);
	    $manager->update(3);
	    $this->assertEquals("3/10 [===============>------------------------------------] 30.00% 00:00:00    \r", ob_get_contents());
	    ob_clean();
	    $manager->update(10);
	    $this->assertEquals("10/10 [==================================================>] 100.00% 00:00:00   \n", ob_get_contents());
    }

    /**
     * Tests the situation when the value given to progressbar is greater than the manager size.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testProgressOverflow()
    {
        $manager = new Manager(0, 10);
        $manager->update(11);
    }

    /**
     * Tests the advance method.
     * Advancing the progress bar should make one step further.
     */
    public function testAdvance()
    {
        $manager = new Manager(0, 10);
        $manager->advance();
        $this->assertEquals("1/10 [=====>----------------------------------------------] 10.00% 00:00:00    \r", ob_get_contents());
        $manager->update(3);
        ob_clean();
        $manager->advance();
        $this->assertEquals("4/10 [====================>-------------------------------] 40.00% 00:00:00    \r", ob_get_contents());
    }

    /**
     * Tests that a lower increment throws an InvalidArgumentException
     * 
     * @expectedException \InvalidArgumentException
     */
    public function testLowerIncrementThrowsException()
    {
    	$manager = new Manager(0, 10);
    	$manager->update(3);
    	$manager->update(2);
    }

    /**
     * Tests that a non integer increment throws an InvalidArgumentException
     * 
     * @expectedException \InvalidArgumentException
     */
    public function testNonIntegerIncrementThrowsException()
    {
    	$manager = new Manager(0, 10);
    	$manager->update(3.1415926);
    }

    /**
     * Tests the use of custom replacements rules
     */
    public function testCustomReplacementRule()
    {
    	$manager = new Manager(0, 10);
    	$manager->addReplacementRule('%foo%', 100, function ($buffer, $registry){return 'FOO!';});
    	$manager->setFormat('%foo%');
    	$manager->update(1);
    	$this->assertRegexp("/FOO!\s+\\r/", ob_get_contents());
    }

    /**
     * Tests that the bar width does not exceed the max width specified
     * in constructor.
     */
    public function testMaxWidth()
    {
    	$manager = new Manager(0, 10, 120);
    	$manager->update(1);
    	$this->assertTrue(120 >= strlen(ob_get_contents()));
    }

    /**
     * Tests ETA
     */
    public function testEta()
    {
    	$manager = new Manager(0, 10, 120);
    	$manager->setFormat('%eta%');
    	$advancement = array(0 => time() - 2);
    	$manager->getRegistry()->setValue('advancement', $advancement);
    	$manager->update(1);
    	$this->assertRegExp("/00:00:18\s+\\r/", ob_get_contents());
    }
}