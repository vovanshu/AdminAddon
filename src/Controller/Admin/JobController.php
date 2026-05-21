<?php
namespace AdminAddon\Controller\Admin;

use Laminas\Mvc\Controller\AbstractActionController;
// use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Interop\Container\ContainerInterface;
use Omeka\Entity\Job;
use AdminAddon\TraitGeneral;

class JobController extends \Omeka\Controller\Admin\JobController
{

    use TraitGeneral;

    public function clearnAction()
    {

        $connect = $this->getConnection();
        $jobs = $connect->executeQuery("SELECT * FROM `job` WHERE `status` IN ('completed', 'stopped');")->fetchAllAssociative();
        if(!empty($jobs)){
            $jobsCount = count($jobs);
            $connect->executeStatement('SET FOREIGN_KEY_CHECKS=0;');
            $logsCount = 0;
            foreach($jobs as $job){
                $logsCount = $logsCount + $connect->executeQuery("DELETE FROM `log` WHERE `job_id` = {$job['id']};")->rowCount();
                $this->cleanerdb($job['id']);
            }
            $connect->executeStatement("DELETE FROM `job` WHERE `status` IN ('completed', 'stopped');");
            $connect->executeStatement('SET FOREIGN_KEY_CHECKS=1;');
            $this->messenger()->addSuccess('Deleted succes finished & stoped jobs'); // @translate
        }
        return $this->redirect()->toRoute('admin/default', ['controller' => 'job', 'action' => 'browse']);
        // $view = new ViewModel();
        // return $view->setTemplate('omeka/admin/job/terminal')->setTerminal(true);
    }

    public function deleteErrorAction()
    {

        $connect = $this->getConnection();
        $jobs = $connect->executeQuery("SELECT * FROM `job` WHERE `status` IN ('error');")->fetchAllAssociative();
        if(!empty($jobs)){
            $jobsCount = count($jobs);
            $connect->executeStatement('SET FOREIGN_KEY_CHECKS=0;');
            $logsCount = 0;
            foreach($jobs as $job){
                $logsCount = $logsCount + $connect->executeQuery("DELETE FROM `log` WHERE `job_id` = {$job['id']};")->rowCount();
                $this->cleanerdb($job['id']);
            }
            $connect->executeStatement("DELETE FROM `job` WHERE `status` IN ('error');");
            $connect->executeStatement('SET FOREIGN_KEY_CHECKS=1;');
            $this->messenger()->addSuccess('Deleted succes jobs with error'); // @translate
        }
        return $this->redirect()->toRoute('admin/default', ['controller' => 'job', 'action' => 'browse']);

    }
   
    public function fixJobAction()
    {

        $connect = $this->getConnection();
        $sql = 'SELECT id, pid, status FROM job WHERE status IN ("starting", "stopping", "in_progress") ORDER BY id ASC;';
        // Fetch all: jobs are few, except if admin never checks result of jobs.
        $result = $connect->executeQuery($sql)->fetchAllAssociative();

        $ids = [];
        // Unselect processes with an existing pid.
        foreach ($result as $id => $row) {
            // TODO The check of the pid works only with Linux.
            // if ($row['pid'] && file_exists('/proc/' . $row['pid'])) {
            if($this->isProcessRunning(intval($row['pid']))){
                unset($result[$id]);
            }else{
                $ids[] = $row['id'];
            }
        }

        if (!empty($result)) {

            $sql = 'SELECT COUNT(id) FROM job';
            $countJobs = $connect->executeQuery($sql)->fetchOne();

            $sql = 'UPDATE job SET status = "stopped" WHERE id IN ('.join(', ', $ids).') AND status IN ("starting", "stopping");';
            $stopped = $connect->executeQuery($sql)->rowCount();
            $sql = 'UPDATE job SET status = "error" WHERE id IN ('.join(', ', $ids).') AND status IN ("in_progress");';
            $error = $connect->executeQuery($sql)->rowCount();
            $this->getLogger()->notice(
                'Dead jobs were cleaned: {count_stopped} marked "stopped" and {count_error} marked "error" on a total of {count_jobs}.', // @translate
                [
                    'count_stopped' => $stopped,
                    'count_error' => $error,
                    'count_jobs' => $countJobs,
                ]
            );
            

            $this->getLogger()->notice(
                'The following {count} jobs are dead: {job_ids}.', // @translate
                [
                    'count' => count($result),
                    'job_ids' => implode(', ', array_map(fn ($v) => '#' . $v['id'], $result)),
                ]
            );

            $stopped = [];
            $errored = [];
            foreach ($result as $value) {
                if ($value['status'] === 'in_progress') {
                    $errored[] = (int) $value['id'];
                } else {
                    $stopped[] = (int) $value['id'];
                }
            }

            if ($stopped) {
                $sql = 'UPDATE job SET status = "stopped" WHERE id IN (' . implode(',', $stopped) . ')';
                $connect->executeStatement($sql);
            }

            if ($errored) {
                $sql = 'UPDATE job SET status = "error" WHERE id IN (' . implode(',', $errored) . ')';
                $connect->executeStatement($sql);
            }

            $this->getLogger()->notice(
                'A total of {count} dead jobs have been cleaned.', // @translate
                ['count' => count($result)]
            );

        }else{
            $this->getLogger()->notice(
                'There is no dead job.' // @translate
            );
        }

        $this->messenger()->addSuccess('Fix jobs succes'); // @translate
        return $this->redirect()->toRoute('admin/default', ['controller' => 'job', 'action' => 'browse']);

    }

    public function runAction()
    {

        // $job = $this->api()->read('jobs', $this->params('id'))->getContent();
        $entityManager = $this->getEntityManager();
        $job = $entityManager->find(Job::class, $this->params('id'));
        if(!empty($job)){
            if (in_array($job->getStatus(), ['starting', 'completed', 'stopped', 'error'])) {
                $strategy = $this->services->get('Omeka\Job\DispatchStrategy\Synchronous');
                $this->jobDispatcher()->send($job, $strategy);
                $this->messenger()->addSuccess('The job was started.'); // @translate
            }else{
                $this->messenger()->addError('The job could not be starting.'); // @translate
            }
            return $this->redirect()->toRoute('admin/default', ['controller' => 'job', 'action' => $this->params('id')]);
        }else{
            $this->messenger()->addError('The job could not be founded.'); // @translate
            return $this->redirect()->toRoute('admin/default', ['controller' => 'job', 'action' => 'browse']);
        }
        
    }

    public function terminateAction()
    {

        $jobID = $this->params('id');
        $job = $this->api()->read('jobs', $jobID)->getContent();
        if(!empty($job)){
            if (in_array($job->status(), ['starting', 'stopping', 'in_progress'])) {
                $this->jobDispatcher()->stop($job->id());
                $this->messenger()->addSuccess('Attempting to stop the job.'); // @translate
                if($this->isProcessRunning(intval($jobID))){
                    $this->killProcessRunning(intval($jobID));
                }
                $sql = 'UPDATE job SET status = "stopped" WHERE id = '.$jobID.' AND status IN ("starting", "stopping");';
                $sql .= 'UPDATE job SET status = "error" WHERE id = '.$jobID.' AND status IN ("in_progress");';
                $this->getConnection()->executeQuery($sql);
            }else{
                $this->messenger()->addError('The job could not be stopped.'); // @translate
            }
            return $this->redirect()->toRoute('admin/default', ['controller' => 'job', 'action' => $job->id()]);
        }else{
            $this->messenger()->addError('The job could not be founded.'); // @translate
            return $this->redirect()->toRoute('admin/default', ['controller' => 'job', 'action' => 'browse']);
        }
        
    }

    public function deleteAction()
    {

        $id = $this->params('id');
        $connect = $this->getConnection();
        $connect->executeStatement("DELETE FROM `job` WHERE `id` = '$id';");
        $log = $connect->executeQuery("SELECT * FROM `log` WHERE 'job_id' = '$id';")->rowCount();
        if(!empty($log)){
            $connect->executeStatement("DELETE FROM `log` WHERE `job_id` = '$id';");
        }
        $this->messenger()->addSuccess('The job was deleted.'); // @translate
        return $this->redirect()->toRoute('admin/default', ['controller' => 'job', 'action' => 'browse']);

    }

    private function cleanerdb($id)
    {

        if(!empty($this->getConnection()->executeQuery("SELECT * FROM `log` WHERE 'job_id' = '$id';")->rowCount())){
            $this->getConnection()->executeStatement("DELETE FROM `log` WHERE `job_id` = '$id';");
        }
        if(!empty($this->getConnection()->executeQuery("SELECT * FROM `bulk_import` WHERE 'job_id' = '$id';")->rowCount())){
            $this->getConnection()->executeStatement("DELETE FROM `bulk_import` WHERE `job_id` = '$id';");
        }
        if(!empty($this->getConnection()->executeQuery("SELECT * FROM `bulk_imported` WHERE 'job_id' = '$id';")->rowCount())){
            $this->getConnection()->executeStatement("DELETE FROM `bulk_imported` WHERE `job_id` = '$id';");
        }
         

    }

    private function isProcessRunning(int $pid): bool
    {
        // Check if the PID is active
        return is_dir("/proc/$pid");

    }

    private function killProcessRunning(int $pid)
    {

        // Kill running process PID
        posix_kill($pid, SIGTERM);
    }

}
