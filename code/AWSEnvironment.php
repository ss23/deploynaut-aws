<?php
/**
 * AWSEnvironment
 *
 * Example of using another DeploymentBackend for https://github.com/silverstripe/deploynaut
 *
 */

class AWSEnvironment extends DNEnvironment {

	/**
	 * @var array
	 */
	public static $db = array(
		'SnowcakeName' => 'Varchar(255)'

	);

	public function Backend() {
		return Object::create_from_string("SnowcakeDeploymentBackend");
	}

	/**
	 * @return FieldList
	 */
	public function getCMSFields() {
		$project = $this->Project();
		if($project && $project->exists()) {
			$viewerGroups = $project->Viewers();
			$groups = $viewerGroups->sort('Title')->map()->toArray();
			$members = array();
			foreach($viewerGroups as $group) {
				foreach($group->Members()->map() as $k => $v) {
					$members[$k] = $v;
				}
			}
			asort($members);
		} else {
			$groups = array();
			$members = array();
		}

		$fields = new FieldList(new TabSet('Root'));

		$fields->addFieldsToTab('Root.Main', array(
			// The Main.ProjectID
			TextField::create('ProjectName', 'Project')
				->setValue(($project = $this->Project()) ? $project->Name : null)
				->performReadonlyTransformation(),

			// The Main.Name
			TextField::create('Name', 'Environment name')
				->setDescription('A descriptive name for this environment, e.g. staging, uat, production'),

			// The Main.URL field
			TextField::create('URL', 'Server URL')
				->setDescription('This url will be used to provide the front-end with a link to this environment'),

			TextField::create('SnowcakeName', 'Snowcake deploy name')
				->setDescription('This must be the same as the snowcake folder name in the projects/ folder'),
		));

		$fields->addFieldsToTab('Root.UserPermissions', array(
			// The viewers of the environment
			$this
				->buildPermissionField('ViewerGroups', 'Viewers', $groups, $members)
				->setTitle('Who can view this environment?')
				->setDescription('Groups or Users who can view this environment'),

			// The Main.Deployers
			$this
				->buildPermissionField('DeployerGroups', 'Deployers', $groups, $members)
				->setTitle('Who can deploy?')
				->setDescription('Groups or Users who can deploy to this environment'),
		));

		return $fields;
	}
}
