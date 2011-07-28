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
 * @author James Titcumb, Simon Wade
 * @link http://www.godeploy.com/
 */
class ServersController extends Zend_Controller_Action
{
	private $_method;

	public function init()
	{
		if(!in_array($this->_request->getActionName() . "Action", get_class_methods($this)))
		{
			if($this->_request->getActionName() == "add")
			{
				$this->_method = "add";
			}
			else if($this->_request->getActionName() == "edit")
			{
				$this->_method = "edit";
			}

			$this->_forward("index");
		}
	}

	public function indexAction()
	{
		$project_slug = $this->_getParam("project");

    	$form = new GDApp_Form_ServerSettings();
    	$this->view->form = $form;

		$servers = new GD_Model_ServersMapper();
		$server = new GD_Model_Server();

		$server_id = $this->_request->getParam('id', 0);

		if($server_id > 0 && $this->_method == "edit")
		{
			$servers->find($server_id, $server);
		}
		else if($this->_method == "add")
		{
			$projects = new GD_Model_ProjectsMapper();
			$project = $projects->getProjectBySlug($project_slug);
			$server->setProjectsId($project->getId());
		}
		$this->view->server = $server;

		if($this->getRequest()->isPost())
		{
			$server->setName($this->_request->getParam('name', false));
			$server->setHostname($this->_request->getParam('hostname', false));
			$server->setConnectionTypesId($this->_request->getParam('connectionTypeId', false));
			$server->setPort($this->_request->getParam('port', false));
			$server->setUsername($this->_request->getParam('username', false));
			$server->setPassword($this->_request->getParam('password', false));
			$server->setRemotePath($this->_request->getParam('remotePath', false));

			$servers->save($server);

			$this->_redirect("/project/" . $this->_getParam("project") . "/settings");
		}
		else
		{
			$data = array(
				'name' => $server->getName(),
				'hostname' => $server->getHostname(),
				'connectionTypeId' => $server->getConnectionTypesId(),
				'port' => $server->getPort(),
				'username' => $server->getUsername(),
				'password' => $server->getPassword(),
				'remotePath' => $server->getRemotePath(),
			);

    		$form->populate($data);
		}
	}

	public function deleteAction()
	{
		$project_slug = $this->_getParam("project");

		$servers = new GD_Model_ServersMapper();
		$server = new GD_Model_Server();

		$server_id = $this->_request->getParam('id', 0);

		if($server_id > 0)
		{
			$servers->find($server_id, $server);
			$servers->delete($server);
			$this->_redirect("/project/" . $this->_getParam("project") . "/settings");
		}
		else
		{
			throw new Zend_Exception("Server id was not specified");
		}
	}
}