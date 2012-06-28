<?php
/**
 * ProgressBarRegistry is a registry class that stores metrics for the progress bar
 */
class ProgressBarRegistry
{
    /**
	 * Registry
	 */
	public $registry = array();

    /**
	 * Sets the value for a key
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public function setValue($key, $value)
    {
      $this->registry[$key] = $value;
    }

    /**
	 * Returns the value associated to a key
	 * 
	 * @param string $key
	 * @return mixed
	 * @throws Exception
	 */
	public function getValue($key)
    {
      if (!isset($this->registry[$key]))
		throw new Exception('Invalid offset requested');

      return $this->registry[$key];
    }
}
