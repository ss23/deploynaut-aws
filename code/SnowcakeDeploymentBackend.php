<?php
use \Symfony\Component\Process\Process;

class SnowcakeDeploymentBackend implements DeploymentBackend
{

    /**
     * Use snowcake to do the deployment
     */
    public function deploy(DNEnvironment $environment, $sha, DeploynautLogFile $log, DNProject $project, $leaveMaintenancePage = false)
    {
        $log->write(sprintf('Deploying "%s" to "%s"', $sha, $environment->getFullName()));

        if (!defined('SNOWCAKE_PATH')) {
            $log->write('SNOWCAKE_PATH is not defined');
            throw new RuntimeException('SNOWCAKE_PATH is not defined');
        }

        // Construct our snowcake command
        $name = $environment->SnowcakeName . '-' . substr($sha, 0, 8) . '-' . mt_rand();
        // Filter invalid characters out of $name (Value 'ssorg_uat-fdceda2e-1400725889-bake' at 'stackName' failed to satisfy constraint:
        // "Member must satisfy regular expression pattern: [a-zA-Z][-a-zA-Z0-9]*)"
        $name = str_replace('_', '-', $name);
        $command = sprintf('%s deploy %s %s %s', SNOWCAKE_PATH, $environment->SnowcakeName, $name, $sha);
        $log->write(sprintf('Running command: %s', $command));

        $process = new Process($command, dirname(dirname(SNOWCAKE_PATH)));
        $process->setTimeout(3600);

        $process->run(function ($type, $buffer) use ($log) {
            $log->write($buffer);
        });

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput());
        }

        $log->write(sprintf('Deploy of "%s" to "%s" finished', $sha, $environment->getFullName()));
    }

    /**
     * There is no such thing as setting the application in maintenance mode on AWS at the moment, so this doesn't do anything
     */
    public function enableMaintenance(DNEnvironment $environment, DeploynautLogFile $log, DNProject $project)
    {
        // NOOP
    }

    /**
     * There is no such thing as setting the application in maintenance mode on AWS at the moment, so this doesn't do anything
     */
    public function disableMaintenance(DNEnvironment $environment, DeploynautLogFile $log, DNProject $project)
    {
        // NOOP
    }

    /**
     * There is no such thing as transfering data on AWS at the moment, so this doesn't do anything
     */
    public function dataTransfer(DNDataTransfer $dataTransfer, DeploynautLogFile $log)
    {
        throw new Exception("No dataTransfer implemented");
    }

    /**
     * At the moment we can't ensure that the environment is up and running on AWS before doing a
     * deploy, so this is a not allowed
     */
    public function ping(DNEnvironment $environment, DeploynautLogFile $log, DNProject $project)
    {
        throw new Exception("Not implemented yet");
    }
}
