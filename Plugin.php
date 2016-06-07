<?php

namespace Kanboard\Plugin\EmailRemindTask;

use Kanboard\Core\Plugin\Base;
use Kanboard\Plugin\EmailRemindTask\Action\TaskEmailRemindTask;

class Plugin extends Base
{
    public function initialize()
    {
        $this->actionManager->register(new TaskEmailRemindTask($this->container));
    }
    public function getPluginName()
    {
        return 'Email reminder';
    }
    public function getPluginDescription()
    {
        return t('This plugin send email to task owner before due date.');
    }
    public function getPluginAuthor()
    {
        return 'Adrien Nayrat';
    }
    public function getPluginVersion()
    {
        return '1.0.0';
    }
    public function getPluginHomepage()
    {
        return 'https://github.com/';
    }
}
