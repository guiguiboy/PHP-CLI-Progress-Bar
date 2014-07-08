<?php
namespace ProgressBar;

/**
 *
 * ProgressBar
 * @author guiguiboy
 * @see README.md for more information
 */
class Manager
{
    /**
     * Default format for the progress bar
     */
    protected $format = <<<EOF
%current%/%max% [%bar%] %percent%% %eta%
EOF;

    /**
     * Instance of Registry. Used to store metrics.
     */
    protected $registry = null;

    /**
     * Stores replacement rules
     * 
     * @var array
     */
    protected $replacementRules = array();

    /**
     * Class constructor
     */
    public function __construct($current, $max, $width = 80, $doneBarElementCharacter = '=', $remainingBarElementCharacter = '-', $currentPositionCharacter = '>')
    {
        $advancement    = array($current => time());
        $this->registry = new Registry();
        $this->registry->setValue('current', $current);

        $this->registry->setValue('max', $max);
        $this->registry->setValue('advancement', $advancement);
        $this->registry->setValue('width', $width);
        $this->registry->setValue('doneBarElementCharacter', $doneBarElementCharacter);
        $this->registry->setValue('remainingBarElementCharacter', $remainingBarElementCharacter);
        $this->registry->setValue('currentPositionCharacter', $currentPositionCharacter);
        $this->registerDefaultReplacementRules();
    }

    /**
     * Sets a Registry
     * 
     * @param Registry $registry
     */
    public function setRegistry(Registry $registry)
    {
    	$this->registry = $registry;
    }
    
    /**
     * Returns the Registry
     * 
     * @return Registry
     */
    public function getRegistry()
    {
    	return $this->registry;
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
     * If you wish to add custom replacements rules, extend this class, and
     * add overload this method.
     * Each replacement has a priority and a closure
     *
     */
    protected function registerDefaultReplacementRules()
    {
        $this->addReplacementRule('%current%', 10, function ($buffer, $registry)  {return $registry->getValue('current');});
        $this->addReplacementRule('%max%', 20, function ($buffer, $registry)  {return $registry->getValue('max');});
        $this->addReplacementRule('%percent%', 30, function ($buffer, $registry)  {return number_format(($registry->getValue('current') * 100) / $registry->getValue('max'), 2);});
        $this->addReplacementRule('%eta%', 40, function ($buffer, $registry)
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

            $estimatedSecondsToEnd = $estimatedTotalSeconds - $seconds;
            $hoursCount            = intval($estimatedSecondsToEnd / 3600);
            $rest                  = ($estimatedSecondsToEnd % 3600);
            $minutesCount          = intval($rest / 60);
            $secondsCount          = ($rest % 60);
            
            return sprintf("%02d:%02d:%02d", $hoursCount, $minutesCount, $secondsCount);
        });
        $this->addReplacementRule('%bar%', 500, function ($buffer, $registry)  
        {
            $bar             = '';
            $lengthAvailable = $registry->getValue('width') - (int) strlen(str_replace('', '%bar%', $buffer));
            $barArray        = array_fill(0, $lengthAvailable, $registry->getValue('remainingBarElementCharacter'));
            $position        = intval(($registry->getValue('current') * $lengthAvailable) / $registry->getValue('max'));

            for ($i = $position; $i >= 0; $i--)
            $barArray[$i] = $registry->getValue('doneBarElementCharacter');
                
            $barArray[$position] = $registry->getValue('currentPositionCharacter');

            return implode('', $barArray);
        });
    }

    /**
     * Register a replacement rule
     * 
     * @param string   $tag
     * @param integer  $priority
     * @param callable $callable
     */
    public function addReplacementRule($tag, $priority, $callable)
    {
        $this->replacementRules[$priority][$tag] = $callable;
        ksort($this->replacementRules);
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

        foreach ($this->replacementRules as $priority => $rule)
        {
            foreach ($rule as $tag => $closure)
            {
                $buffer = str_replace($tag, $closure($buffer, $this->registry), $buffer);
            }
        }

        $buffer = $this->clearRightCharacters($buffer);

        $eolCharacter = ($lineReturn) ? "\n" : "\r";
        echo "$buffer$eolCharacter";
    }

    /**
     * Clears line remaining characters so that buffer length always equals
     * to max width.
     * registry->getValue('width') -1 stands for the control character \r or \n
     * that will be added to the buffer.
     * 
     * @param string $buffer
     * @return string
     */
    protected function clearRightCharacters($buffer)
    {
        $len = mb_strlen($buffer);
        while ($len < $this->registry->getValue('width') - 1)
        {
            $buffer .= ' ';
            $len = mb_strlen($buffer);
        }
        return $buffer;
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
            throw new \InvalidArgumentException('Integer as current counter was expected');

        if ($this->registry->getValue('current') > $current)
            throw new \InvalidArgumentException('Could not set lower current counter');

        if($this->registry->getValue('max') < $current)
            throw new \InvalidArgumentException('Could not set the progress value ' . $current .
                ' because the max is ' . $this->registry->getValue('max'));

        $advancement           = $this->registry->getValue('advancement');
        $advancement[$current] = time();
        $this->registry->setValue('current', $current);
        $this->registry->setValue('advancement', $advancement);
        $lineReturn = ($current == $this->registry->getValue('max'));

        $this->display($lineReturn);
    }

    /**
     * Advances the progress bar with one step.
     */
    public function advance()
    {
        $this->update($this->registry->getValue('current') + 1);
    }
}
