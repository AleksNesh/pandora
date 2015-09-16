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
 * Database Tab Block
 *
 * @category    Ash
 * @package     Ash_Bar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Bar_Block_Tab_Database extends Ash_Bar_Block_Tab
{
    /**
     * Number of seconds for longest query
     *
     * @var float
     */
    protected $_longestQueryTime = 0.0;

    /**
     * Longest executing query string
     *
     * @var string
     */
    protected $_longestQuery;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->setTemplate('tabs/database.phtml');
    }

    /**
     * Retrieve details SQL queries run during request
     *
     * @return  string
     */
    public function getLabel()
    {
        return $this->getProfiler()->getTotalNumQueries();
    }

    /**
     * Get database profiler object
     *
     * @return Zend_Db_Profiler
     */
    public function getProfiler()
    {
        return Mage::getSingleton('core/resource')
            ->getConnection('core_write')
            ->getProfiler();
    }

    /**
     * Number in seconds that activates a query highlight
     *
     * @return float
     */
    public function getQueryThreshold()
    {
        return 1.0;
    }

    /**
     * Get the average number of seconds per query
     *
     * @return float
     */
    public function getAverageQueryTime()
    {
        return $this->_calculateQueryStats('avg');
    }

    /**
     * Get the queries per second speed
     *
     * @return float
     */
    public function getQueriesPerSecond()
    {
        return $this->_calculateQueryStats('qps');
    }

    /**
     * Get the longest executing query time
     *
     * @return float
     */
    public function getLongestQueryTime()
    {
        if (!$this->_longestQueryTime) {
           $this->_calculateLongestQuery();
        }

        return $this->_longestQueryTime;
    }

    /**
     * Get the longest executing query string
     *
     * @return string
     */
    public function getLongestQuery()
    {
        if (!$this->_longestQuery) {
            $this->_calculateLongestQuery();
        }

        return $this->_longestQuery;
    }

    /**
     * Calculate query statistics
     *
     * @param  string $type
     * @return float
     */
    protected function _calculateQueryStats($type)
    {
        $numQueries = $this->getProfiler()->getTotalNumQueries();
        $totalTime  = $this->getProfiler()->getTotalElapsedSecs();

        if ($numQueries && $totalTime) {
            switch ($type) {
                case 'qps':
                    return ($numQueries / $totalTime);
                    break;

                case 'avg':
                    return ($totalTime / $numQueries);
                    break;
            }
        }

        return 0.0;
    }

    /**
     * Scan through all queries to find longest executing one
     *
     * @return void
     */
    protected function _calculateLongestQuery()
    {
        foreach ($this->getProfiler()->getQueryProfiles() as $query) {
            if ($query->getElapsedSecs() > $this->_longestQueryTime) {
                $this->_longestQueryTime = $query->getElapsedSecs();
                $this->_longestQuery = $query->getQuery();
            }
        }
    }
}
