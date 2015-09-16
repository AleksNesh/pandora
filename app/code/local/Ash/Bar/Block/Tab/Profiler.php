<?php
/**
 * Magento Developer's Toolbar
 *
 * @category    Ash
 * @package     Ash_Bar
 * @copyright   Copyright (c) 2012 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Profiler Tab Block
 *
 * @category    Ash
 * @package     Ash_Bar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Bar_Block_Tab_Profiler extends Ash_Bar_Block_Tab
{
    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->setTemplate('tabs/profiler.phtml');
    }

    /**
     * Retrieve profiler results
     *
     * @return  string
     */
    public function getLabel()
    {
        return 'Profiler';
    }

    /**
     * Get profiler timers, sorted by slowest items at the top
     *
     * @return array
     */
    public function getTimers()
    {
        $timers = Varien_Profiler::getTimers();
        foreach ($timers as $key => $value) {
            $timers[$key]['sum'] = Varien_Profiler::fetch($key, 'sum');
        }
        uasort($timers, array('self', 'compareTimers'));

        return $timers;
    }

    /**
     * Compare two timer entries to determine slowest
     *
     * @param  array  $timerA
     * @param  array  $timerB
     * @return boolean
     */
    static public function compareTimers(array $timerA, array $timerB)
    {
       return $timerA['sum'] < $timerB['sum'];
    }
}
