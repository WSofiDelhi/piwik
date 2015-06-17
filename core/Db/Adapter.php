<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Db;

use Zend_Db_Table;

/**
 */
class Adapter
{
    /**
     * Create adapter
     *
     * @param string $adapterName database adapter name
     * @param array $dbInfos database connection info
     * @return AdapterInterface
     */
    public static function factory($adapterName, & $dbInfos)
    {
        if ($dbInfos['port'][0] == '/') {
            $dbInfos['unix_socket'] = $dbInfos['port'];
        }

        $className = self::getAdapterClassName($adapterName);

        $adapter   = new $className($dbInfos);
        Zend_Db_Table::setDefaultAdapter($adapter);

        return $adapter;
    }

    /**
     * Get adapter class name
     *
     * @param string $adapterName
     * @return string
     * @throws \Exception
     */
    private static function getAdapterClassName($adapterName)
    {
        $className = 'Piwik\Db\Adapter\\' . str_replace(' ', '\\', ucwords(str_replace(array('_', '\\'), ' ', strtolower($adapterName))));
        if (!class_exists($className)) {
            throw new \Exception("Adapter $adapterName is not valid.");
        }
        return $className;
    }

    /**
     * Get default port for named adapter
     *
     * @param string $adapterName
     * @return int
     */
    public static function getDefaultPortForAdapter($adapterName)
    {
        $className = self::getAdapterClassName($adapterName);
        return call_user_func(array($className, 'getDefaultPort'));
    }

    /**
     * Get list of adapters
     *
     * @return array
     */
    public static function getAdapters()
    {
        static $adapterNames = array(
            // currently supported by Piwik
            'Pdo\Mysql',
            'Mysqli',

            // other adapters supported by Zend_Db
//			'Pdo_Pgsql',
//			'Pdo_Mssql',
//			'Sqlsrv',
//			'Pdo_Ibm',
//			'Db2',
//			'Pdo_Oci',
//			'Oracle',
        );

        $adapters = array();

        foreach ($adapterNames as $adapterName) {
            $className = '\Piwik\Db\Adapter\\' . $adapterName;
            if (call_user_func(array($className, 'isEnabled'))) {
                $adapters[strtoupper($adapterName)] = call_user_func(array($className, 'getDefaultPort'));
            }
        }

        return $adapters;
    }
}
