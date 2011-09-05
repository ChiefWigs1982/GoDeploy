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
class SettingsController extends Zend_Controller_Action
{
	public function indexAction()
	{
		$this->view->headLink()->appendStylesheet("/css/template/form.css");
		$this->view->headLink()->appendStylesheet("/css/template/table.css");
		$this->view->headLink()->appendStylesheet("/css/pages/project_settings.css");

		$projects = new GD_Model_ProjectsMapper();
		$project_slug = $this->_getParam("project");
		if ($project_slug != "new")
		{
			$project = $projects->getProjectBySlug($project_slug);
			$new_project = false;
		}
		else
		{
			$project = new GD_Model_Project();
			$project->setName("New Project");
			$project->setDeploymentBranch('master'); // Usually default for git
			$project->getSSHKey()->generateKeyPair($project);
			$new_project = true;
		}
		$this->view->project = $project;

		// Populate list of servers for this project
		if ($project->getId() > 0)
		{
			$servers = new GD_Model_ServersMapper();
			$this->view->servers = $servers->getServersByProject($project->getId());
		}

		$form = new GDApp_Form_ProjectSettings(null, $new_project);
		$this->view->form = $form;
		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($this->getRequest()->getParams()))
			{
				$result = $this->saveProject($projects, $project, $new_project);
				if($result !== true)
				{
					$form->repositoryUrl->addError("Could not clone the git repository [" . $result . "]. Please check the URL is correct and try again.");
				}
				else
				{
					$this->_redirect($this->getFrontController()->getBaseUrl() . "/home");
				}
			}
		}
		else
		{
			$data = array(
				'name' => $project->getName(),
				'repositoryUrl' => $project->getRepositoryUrl(),
				'deploymentBranch' => $project->getDeploymentBranch(),
				'publicKey' => $project->getSSHKey()->getPublicKey(),
			);

			$form->populate($data);
		}
	}

	public function confirmDeleteAction()
	{
		$projects = new GD_Model_ProjectsMapper();
		$project_slug = $this->_getParam("project");
		$project = $projects->getProjectBySlug($project_slug);

		$this->view->project = $project;

		$this->view->headLink()->appendStylesheet("/css/pages/confirm_delete.css");
	}

	public function deleteAction()
	{
		$projects = new GD_Model_ProjectsMapper();
		$project_slug = $this->_getParam("project");

		$project = $projects->getProjectBySlug($project_slug);

                // Delete the deployments associated with the project.
                $deployments = $project->getDeployments();
                $deploymentsMapper = new GD_Model_DeploymentsMapper(); 
                foreach($deployments as $deployment)
                {
                    // Delete the files associated with the project.
                    $deploymentFiles = $deployment->getDeploymentFiles();
                    $deploymentFilesMapper = new GD_Model_DeploymentFilesMapper();
                    foreach($deploymentFiles as $deploymentFile)
                        $deploymentFilesMapper->delete($deploymentFile);
                    // Delete deployment.
                    $deploymentsMapper->delete($deployment);                    
                }
                
		// Delete the servers associated with the project.
                $servers = $project->getServers();
                $serversMapper = new GD_Model_ServersMapper();
                foreach($servers as $server)
                    $serversMapper->delete($server);
                
                //Delete the project's git repo.
                $git = new GD_Git($project);
                $git->deleteRepository();
                                
		// Delete the ssh key
		$ssh_key = $project->getSSHKey();
		$ssh_keys = new GD_Model_SSHKeysMapper();
		$ssh_keys->delete($ssh_key);

		// Delete the project
		$projects->delete($project);
		$this->_redirect($this->getFrontController()->getBaseUrl() . "/home");
	}


	public function saveProject(GD_Model_ProjectsMapper $projects, GD_Model_Project $project, $new_project = false)
	{
		$repo_before = $project->getRepositoryUrl();
		$project->setName($this->_request->getParam('name', false));
		$project->setRepositoryUrl($this->_request->getParam('repositoryUrl', false));
		$project->setDeploymentBranch($this->_request->getParam('deploymentBranch', false));
		$project->setRepositoryTypesId(1);
		$repo_after = $project->getRepositoryUrl();

		// Save the new ssh key if a new project
		if($new_project)
		{
			$ssh_keys_map = new GD_Model_SSHKeysMapper();
			$key_id = $ssh_keys_map->save($project->getSSHKey());
			$project->setSSHKeysId($key_id);
		}

		// Save the project
		$projects->save($project);

		// If repo URL changed, delete and re-clone
		if($repo_before != $repo_after || $new_project)
		{
			$git = new GD_Git($project);

			$git->deleteRepository();

			$result = $git->gitClone();
			if($result !== true)
			{
				return $result;
			}
		}

		return true;
	}
}
