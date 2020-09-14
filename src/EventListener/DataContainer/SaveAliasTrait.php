<?php


namespace Agoat\PermalinkBundle\EventListener\DataContainer;


use Contao\Database;

trait SaveAliasTrait
{
    /**
     * Save the alias to the database
     *
     * @param string  $alias
     * @param integer $intId
     * @param string  $strTable
     */
    protected function saveAlias($alias, $intId, $strTable)
    {
        $db = Database::getInstance();

        if (empty($alias)) {
            $alias = 'index';
        }

        $db->execute("UPDATE $strTable SET alias='$alias' WHERE id='$intId'");
    }
}
