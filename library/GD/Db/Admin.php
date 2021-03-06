<?php

/**
 * GoDeploy deployment application
 * Copyright (C) 2011 James Titcumb, Simon Wade
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright 2011 GoDeploy
 * @author James Titcumb, Jon Wigham, Simon Wade
 * @link http://www.godeploy.com/
 */
class GD_Db_Admin extends GD_Shell
{
	protected $_hostname;
	protected $_username;
	protected $_password;
	protected $_database;

	public function __construct($hostname, $username, $password, $database)
	{
		$this->_hostname = $hostname;
		$this->_username = $username;
		$this->_password = $password;
		$this->_database = $database;
	}

    public function installDatabase($version = null)
    {
    	if(is_null($version))
    	{
			$version_conf = new Zend_Config_Ini(APPLICATION_PATH . '/configs/version.ini', 'version');
			$version = (int)$version_conf->gd->expect_db_version;
    	}
    	else
    	{
    		$version = (int)$version;
    	}

    	if($version <= 0)
    	{
    		throw new GD_Exception("Version '{$version}' was not a valid version");
    	}

    	$script = APPLICATION_PATH . "/../db/db_create_v{$version}.sql";

    	$this->Exec("mysql -u{$this->_username} -p{$this->_password} --database={$this->_database} < \"{$script}\"");
    }

    public function upgradeDatabase($from_version, $to_version)
    {
		$v = $from_version;

		while($v < $to_version)
		{
			// do upgrade
			$script = APPLICATION_PATH . "/../db/db_alter_v" . ($v + 1) . ".sql";
			$this->Exec("mysql -u{$this->_username} -p{$this->_password} --database={$this->_database} < \"{$script}\"");

			$v++;
		}
    }
}