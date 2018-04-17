<?php

use GuzzleHttp\Psr7\ServerRequest;

class devcenterAdminView extends devcenter
{
    const MODULE_NAME = 'devcenter';
    const LIST_COUNT = 20;
    const PAGE_COUNT = 10;

    public function init()
    {
        $this->setTemplatePath($this->module_path.'tpl');
        $this->setTemplateFile(strtolower(str_replace('dispDevcenterAdmin', '', $this->act)));
    }

    public function dispDevcenterAdminConfig()
    {
        /**
         * @var \devcenterModel $model
         */
        $model = getModel(self::MODULE_NAME);
        $entry = $model->getConfig();

        \Context::set('entry', $entry);
    }

    public function dispDevcenterAdminApps()
    {
        $request = ServerRequest::fromGlobals();

        $params = $request->getQueryParams();

        $page = array_key_exists('page', $params) ? (is_numeric($params['page']) && 1 >= $params['page']) ? $params['page'] : 1 : 1;

        /**
         * @var \devcenterModel $model
         */
        $model = getModel(self::MODULE_NAME);
        $config = $model->getConfig();
        $container = $model->getApps(self::LIST_COUNT, self::PAGE_COUNT, $page);

        \Context::set('page', $page);
        \Context::set('config', $config);
        \Context::set('container', $container);
    }
}