<?php

/**
 *
 * ProgressBar
 * @author guiguiboy
 * @see README.md for more information
 */
class ProgressBar
{
	/**
	 * Default format for the progress bar
	 */
	protected $format = <<<EOF
%current%/%max% [%bar%] %percent%% %eta%
EOF;

	/**
	 * Instance of ProgressBarRegistry. Used to store metrics.
	 */
	protected $registry = null;

	/**
	 * Class constructor
	 *
	 */
	public function __construct($current, $max, $width = 80, $doneBarElementCharacter = '=', $remainingBarElementCharacter = '-', $currentPositionCharacter = '>')
	{
		$advancement    = array($current => time());
		$this->registry = new ProgressBarRegistry();
		$this->registry->setValue('current', $current);

		$this->registry->setValue('max', $max);
		$this->registry->setValue('advancement', $advancement);
		$this->registry->setValue('width', $width);
		$this->registry->setValue('doneBarElementCharacter', $doneBarElementCharacter);
		$this->registry->setValue('remainingBarElementCharacter', $remainingBarElementCharacter);
		$this->registry->setValue('currentPositionCharacter', $currentPositionCharacter);
	}

	/**
	 * Returns the current output format
	 *
	 * @return string
	 */
	public function getFormat()
	{
		return $this->format;
	}

	/**
	 * Sette le format d'affichage
	 *
	 * @param string $string
	 */
	public function setFormat($string)
	{
		$this->format = $string;
	}

	/**
	 * Allows to define replacements functions for the format string
	 * If you wish to add custom replacements rules, feel free to add your own closure
	 * in the array.
	 *
	 * @return array
	 */
	protected function getReplacementsRules()
	{
		return array(
		'%current%' => function ($buffer, $registry)  {return $registry->getValue('current');},
		'%max%'     => function ($buffer, $registry)  {return $registry->getValue('max');},
		'%percent%' => function ($buffer, $registry)  {return number_format(($registry->getValue('current') * 100) / $registry->getValue('max'), 2);},
		'%eta%'     => function ($buffer, $registry)
		{
			$advancement    = $registry->getValue('advancement');
			if (count($advancement) == 1)
			return 'Calculating...';

			$current               = $registry->getValue('current');
			$timeForCurrent        = $advancement[$current];
			$initialTime           = $advancement[0];
			$seconds               = ($timeForCurrent - $initialTime);
			$percent               = ($registry->getValue('current') * 100) / $registry->getValue('max');
			$estimatedTotalSeconds = intval($seconds * 100 / $percent);
			$dateInterval          = new DateInterval(sprintf('PT%sS', $estimatedTotalSeconds - $seconds));
			return $dateInterval->format('%H:%I:%s');
		},
		'%bar%'     => function ($buffer, $registry)  
		{
			$bar             = '';
			$lengthAvailable = $registry->getValue('width') - (int) strlen(str_replace('', '%bar%', $buffer));
			$barArray        = array_fill(0, $lengthAvailable, $registry->getValue('remainingBarElementCharacter'));
			$position        = intval(($registry->getValue('current') * $lengthAvailable) / $registry->getValue('max'));

			for ($i = $position; $i >= 0; $i--)
			$barArray[$i] = $registry->getValue('doneBarElementCharacter');
				
			$barArray[$position] = $registry->getValue('currentPositionCharacter');

			return implode('', $barArray);
		},
		);
	}

	/**
	 * Prints the progress bar
	 *
	 * @param boolean $lineReturn
	 */
	protected function display($lineReturn)
	{
		$buffer = '';
		$buffer = $this->format;

		foreach ($this->getReplacementsRules() as $token => $closure)
		$buffer = str_replace($token, $closure($buffer, $this->registry), $buffer);

		$eolCharacter = ($lineReturn) ? "\n" : "\r";
		echo "$buffer$eolCharacter";
	}

	/**
	 * Updates current progress
	 * Saves new metrics in the registry
	 *
	 * @param integer $current
	 */
	public function update($current)
	{
		if (!is_int($current))
			throw new Exception('Integer as current counter was expected');

		if ($this->registry->getValue('current') > $current)
			throw new Exception('Could not set lower current counter');

		$advancement            = $this->registry->getValue('advancement');
		$advancement[$current] = time();
		$this->registry->setValue('current', $current);
		$this->registry->setValue('advancement', $advancement);
		$lineReturn = ($current == $this->registry->getValue('max'));

		$this->display($lineReturn);
	}
}
