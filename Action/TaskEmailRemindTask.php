<?php

namespace Kanboard\Plugin\EmailRemindTask\Action;

use Kanboard\Model\Task;
use Kanboard\Action\Base;


/**
 * Email remind x day before due date
 *
 * @package action
 * @author  Arien Nayrat
 */
class TaskEmailRemindTask extends Base
{
    /**
     * Get automatic action description
     *
     * @access public
     * @return string
     */
    public function getDescription()
    {
        return t('Send email reminder to owner x day before due date');
    }

    /**
     * Get the list of compatible events
     *
     * @access public
     * @return array
     */
    public function getCompatibleEvents()
    {
        return array(
            Task::EVENT_DAILY_CRONJOB,
        );
    }

    /**
     * Get the required parameter for the action (defined by the user)
     *
     * @access public
     * @return array
     */
    public function getActionRequiredParameters()
    {
        return array(
	    'category_id' => t('Category'),
            'subject' => t('Email subject'),
            'days' => t('Days before due date'),
        );
    }

    /**
     * Get the required parameter for the event
     *
     * @access public
     * @return string[]
     */
    public function getEventRequiredParameters()
    {
        return array('tasks');
    }

    /**
     * Check if the event data meet the action condition
     *
     * @access public
     * @param  array   $data   Event data dictionary
     * @return bool
     */
    public function hasRequiredCondition(array $data)
    {
        return count($data['tasks']) > 0;
    }

    /**
     * Execute the action (Send email reminder to owner x day before due date)
     *
     * @access public
     * @param  array   $data   Event data dictionary
     * @return bool            True if the action was executed or false when not executed
     */
    public function doAction(array $data)
    {
        $results = array();
	// $day contain day before notification
	$day = $this->getParam('days')-1;

	// Today in format Y-m-d
	$dt = new \DateTime();
	$dt = $dt->format('Y-m-d');

	foreach ($data['tasks'] as $task) {

            if (! empty($task['date_due'])) {
                $user = $this->user->getById($task['owner_id']);

		// Convert date_due in format Y-m-d for comparison with today    
		$epoch = $task['date_due'];
	        $task_due = new \DateTime("@$epoch");
	        $task_due = $task_due->modify("- $day days");
		$task_due = $task_due->format('Y-m-d');

		$cat= $this->getParam('category_id');
		if ( ($dt == $task_due) && (!empty($task['date_due'])) && (!empty($task['owner_id'])) )   {
			
		    //send mail for specified category. If no category specified, send mail for all category.	    
                    if (! empty($cat) && $cat == $task['category_id']) {
                        $results[] = $this->sendEmail($task['id'], $user);
                    }
                    elseif ( empty($cat) ) {
                        $results[] = $this->sendEmail($task['id'], $user);
                    } 

                }
            }
        }

        return in_array(true, $results, true);
    }

    /**
     * Send email
     *
     * @access private
     * @param  integer $task_id
     * @param  array   $user
     * @return boolean
     */
    private function sendEmail($task_id, array $user)
    {
        $task = $this->taskFinder->getDetails($task_id);

        $this->emailClient->send(
            $user['email'],
            $user['name'] ?: $user['username'],
            $this->getParam('subject'),
            $this->template->render('notification/task_create', array('task' => $task, 'application_url' => $this->config->get('application_url')))
        );

        return true;
    }
}
