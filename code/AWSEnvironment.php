<?php
/**
 * AWSEnvironment
 *
 * Example of using another DeploymentBackend for https://github.com/silverstripe/deploynaut
 *
 */

class AWSEnvironment extends DNEnvironment {

	/**
	 * Use our proprietary deployment backend
	 *
	 * @var string
	 */
	protected $deploymentBackend = "SnowcakeDeploymentBackend";

	/**
	 * @var array
	 */
	public static $db = array(
		'SnowcakeName' => 'Varchar(255)'

	);

	/**
	 * @return FieldList
	 */
	public function getCMSFields() {
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
		return $fields;
	}
}