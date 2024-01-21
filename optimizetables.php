<?php
/**
 * OptimizeTables - System plugin for daily database table optimizing
 * @version 1.0
 * @copyright (C) 2018 Palpalych.ru - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL.
 * OptimizeTables is free software.
 */
defined('_JEXEC') or die('Restricted access');

class plgSystemOptimizeTables extends JPlugin
{
	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array   $config    An optional associative array of configuration settings.
	 *
	 * @since   1.5
	 */
    function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);
    }

    function onAfterInitialise()
    {
        $app = JFactory::getApplication();;

        $currentTime = time();
        $tomorrowDate = date('Y-m-d', time());

        $time = $this->params->get('time', '00:00:00');
        $nextOptimization = $this->params->get('nextOptimization', $tomorrowDate . ' ' . $time);

        $nextOptimizationTime = strtotime($nextOptimization);

        if ($nextOptimizationTime < $currentTime) {
            $dbo = JFactory::getDBO();
            $dbo->setQuery("SHOW TABLES FROM `" . $app->getCfg('db') . "`");
            $tables = $dbo->loadColumn();

            $reapirStatus = $this->params->get('on_or_off');

            if ($reapirStatus == 1) {
                $dbo->setQuery("REPAIR TABLE `" . implode("` , `", $tables) . "`;");
                $dbo->execute();
            }
			
			$dbo->setQuery("OPTIMIZE TABLE `" . implode("` , `", $tables) . "`;");
            $dbo->execute();
            
			$nextOptimization = date('Y-m-d H:i:s', $nextOptimizationTime + 86400);

            $query = "UPDATE #__extensions"
                . " SET params = 'time=" . $time . "\nnextOptimization=" . $nextOptimization . "\n'"
                . " WHERE folder = 'system'"
                . "   AND element = 'optimizetables'";
            $dbo->setQuery($query);
            $dbo->execute();

        }
    }
}
