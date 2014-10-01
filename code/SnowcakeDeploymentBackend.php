<?php
use \Symfony\Component\Process\Process;

class SnowcakeDeploymentBackend implements DeploymentBackend {

	/**
	 * This is a bit of a hack. To actually get the deployment, we should check one of the live
	 * servers configuration
	 */
	public function currentBuild($environment) {
		$file = DEPLOYNAUT_LOG_PATH . '/' . $environment . ".deploy-history.txt";

		if(file_exists($file)) {
			$lines = file($file);
			$lastLine = array_pop($lines);
			return $this->convertLine($lastLine);
		}
	}

	/**
	 * Use snowcake to do the deployment
	 */
	public function deploy(DNEnvironment $environment, $sha, DeploynautLogFile $log, DNProject $project, $leaveMaintenancePage = false) {
		$log->write('Deploying "'.$sha.'" to "'.$project->Name.':'.$environment->Name.'"');

		if (!defined('SNOWCAKE_PATH')) {
			$log->write('SNOWCAKE_PATH is not defined');
			throw new RuntimeException("SNOWCAKE_PATH is not defined");
		}

		// Construct our snowcake command
		// ./bin/snowcake-linux deploy $ENVIRONMENT $RECOGNIZABLESTRING $SHA
		$name = $environment->SnowcakeName . '-' . substr($sha, 0, 8) . '-' . mt_rand();
		$command = SNOWCAKE_PATH . ' deploy ' . $environment->SnowcakeName . ' ' . $name . ' ' . $sha;

		$log->write("Running command: $command");

		$process = new Process($command, dirname(dirname(SNOWCAKE_PATH)));
		$process->setTimeout(3600);

		$process->run(function ($type, $buffer) use($log) {
			$log->write($buffer);
		});

		// OH GOD, AN ERROR?
		if(!$process->isSuccessful()) {
			throw new RuntimeException($process->getErrorOutput());
		}

		$log->write('Deploy done "'.$sha.'" to "'.$project->Name.':'.$environment->Name.'"');
	}

	/**
	 * There is no such thing as setting the application in maintenance mode on AWS at the moment, so this doesn't do anything
	 */
	public function enableMaintenance(DNEnvironment $environment, DeploynautLogFile $log, DNProject $project) {
		// NOOP
	}

	/**
	 * There is no such thing as setting the application in maintenance mode on AWS at the moment, so this doesn't do anything
	 */
	public function disableMaintenance(DNEnvironment $environment, DeploynautLogFile $log, DNProject $project) {
		// NOOP
	}

	/**
	 * There is no such thing as transfering data on AWS at the moment, so this doesn't do anything
	 */
	public function dataTransfer(DNDataTransfer $dataTransfer, DeploynautLogFile $log) {
		throw new Exception("No dataTransfer implemented");
	}

	/**
	 * At the moment we can't ensure that the environment is up and running on AWS before doing a
	 * deploy, so this is a not allowed
	 */
	public function ping(DNEnvironment $environment, DeploynautLogFile $log, DNProject $project) {
		throw new Exception("Not implemented yet");
	}

}