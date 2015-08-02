<?php
namespace Mu\Kernel\Helper;

use Mu\Kernel;
use Mu\Kernel\Model\Entity;
use Mu\Kernel\Model\Manager;

/**
 * Helper Holidays
 *
 * Service that allow to generate list of holidays
 *
 * @author Olivier Stahl
 */
class Holidays extends Kernel\Service\Core
{
    /**
     * @param $year
     * @param string $country
     * @return array
     */
    public function getAll($year = null, $country = 'fr')
    {
        if (empty($year)) {
            $year = date('Y');
        }
        $year = intval($year);

        $method = 'getAll'.ucfirst($country);
        return (method_exists($this, $method)) ? call_user_func(array($this, $method), $year) : array();
    }

    /**
     * @param \DateTime $date
     * @param string $country
     * @return bool
     */
    public function isHoliday(\DateTime $date = null, $country = 'fr')
    {
        $formated = $date->format('Y-m-d');
        $holidays = $this->getAll($date->format('Y'), $country);

        return in_array($formated, $holidays);
    }

    /**
     * @param $year
     * @return array() Formated array (Y-m-d)
     */
    private function getAllFr($year)
    {
        // Get easter dates:
        $easterDate  = easter_date($year);
        $easterDay   = date('j', $easterDate);
        $easterMonth = date('n', $easterDate);
        $easterYear  = date('Y', $easterDate);

        // Build holidays array:
        $holidays = array(
            // Fixed dates
            "$year-01-01",   // 1er janvier
            "$year-05-01",   // Fête du travail
            "$year-05-08",   // Victoire des alliés
            "$year-07-14",   // Fête nationale
            "$year-08-15",   // Assomption
            "$year-11-01",   // Toussaint
            "$year-11-11",   // Armistice
            "$year-12-25",   // Noel

            // Variables dates
            date('Y-m-d', mktime(0, 0, 0, $easterMonth, $easterDay + 1,  $easterYear)),
            date('Y-m-d', mktime(0, 0, 0, $easterMonth, $easterDay + 39, $easterYear)),
            date('Y-m-d', mktime(0, 0, 0, $easterMonth, $easterDay + 50, $easterYear)),
        );

        // Sort dates:
        sort($holidays);

        // Return list:
        return $holidays;
    }
}