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
 * @author James Titcumb, Simon Wade, Jon Wigham
 * @link http://www.godeploy.com/
 */

/**
 * Map the deployment statuses table
 * @author james
 *
 */
class GD_Model_DeploymentStatusesMapper extends MAL_Model_MapperAbstract
{

	/**
	 * Should return the name of the table being used e.g. "Application_Model_DbTable_BlogPost"
	 * @return string
	 */
	protected function getDbTableName()
	{
		return "GD_Model_DbTable_DeploymentStatuses";
	}

	/**
	 * Should return the name of the object being used e.g. "Application_Model_BlogPost"
	 * @return string
	 */
	protected function getObjectName()
	{
		return "GD_Model_DeploymentStatus";
	}

	/**
	 * Should return an array of mapped fields to use in the MAL_Model_MapperAbstract::Save function
	 * @param GD_Model_DeploymentStatus $obj
	 */
	protected function getSaveData($obj)
	{
		$data = array(
			'code' => $obj->getCode(),
			'name' => $obj->getName(),
		);
		return $data;
	}

	/**
	 * Implement this by setting $obj values (e.g. $obj->setId($row->Id) from a DB row
	 * @param GD_Model_DeploymentStatus $obj
	 * @param Zend_Db_Table_Row_Abstract $row
	 */
	protected function populateObjectFromRow(&$obj, Zend_Db_Table_Row_Abstract $row)
	{
		$obj->setId($row->id)
			->setCode($row->code)
			->setName($row->name);
	}

	/**
	 * Search for a status by it's name
	 * @param string $name status name to find
	 * @return GD_Model_DeploymentStatus
	 */
	public function getDeploymentStatusByName($name)
	{
		$obj = new GD_Model_DeploymentStatus();

		$select = $this->getDbTable()
			->select()
			->where("name = ?", $name);

		$row = $this->getDbTable()->fetchRow($select);

		if(is_null($row))
		{
			return null;
		}
		$this->populateObjectFromRow($obj, $row);
		return $obj;
	}

	/**
	 * Search for a status by it's code
	 * @param string $name status code to find
	 * @return GD_Model_DeploymentStatus
	 */
	public function getDeploymentStatusByCode($code)
	{
		$obj = new GD_Model_DeploymentStatus();

		$select = $this->getDbTable()
			->select()
			->where("code = ?", $code);

		$row = $this->getDbTable()->fetchRow($select);

		if(is_null($row))
		{
			return null;
		}
		$this->populateObjectFromRow($obj, $row);
		return $obj;
	}
}
