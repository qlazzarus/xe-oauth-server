<?php

use GuzzleHttp\Psr7\ServerRequest;
use Monoless\Xe\OAuth2\Server\Utils\ResponseUtil;
use Zend\Diactoros\Response\JsonResponse;

class devcenterAdminController extends devcenter
{
    const MODULE_NAME = 'devcenter';

    public function procDevcenterAdminConfig()
    {
        $request = ServerRequest::fromGlobals();

        /**
         * @var \devcenterModel $model
         */
        $model = getModel(self::MODULE_NAME);
        $config = $model->getConfig();

        $params = $request->getParsedBody();

        if (array_key_exists('use_rate_limiter', $params)) $config->use_rate_limiter = (1 == $params['use_rate_limiter']);
        if (array_key_exists('use_app_thumbnail', $params)) $config->use_app_thumbnail = (1 == $params['use_app_thumbnail']);
        if (array_key_exists('rate_limit_capacity', $params)) $config->rate_limit_capacity = $params['rate_limit_capacity'];
        if (array_key_exists('redis_host', $params)) $config->redis_host = $params['redis_host'];
        if (array_key_exists('redis_port', $params)) $config->redis_port = $params['redis_port'];

        $model->saveConfig($config);
        $this->setRedirectUrl(Context::get('error_return_url'));
    }

    public function procDevcenterAdminRemoveApp()
    {
        $request = ServerRequest::fromGlobals();

        /**
         * @var \devcenterModel $devcenterModel
         */
        $devcenterModel = getModel(self::MODULE_NAME);

        $params = $request->getParsedBody();
        $uniqueAppSrl = array_key_exists('unique_app_srl', $params) ? $params['unique_app_srl'] : null;

        $entry = $devcenterModel->getAppByClientId($uniqueAppSrl);

        if (!$entry) {
            ResponseUtil::finalizeResponse(new JsonResponse([
                'status' => false,
                'error' => 'app_not_exist',
            ], 404));
        }

        $status = $devcenterModel->deleteApp($entry);
        if (!$status) {
            ResponseUtil::finalizeResponse(new JsonResponse([
                'status' => false,
                'error' => 'invalid_request',
            ], 500));
        }

        ResponseUtil::finalizeResponse(new JsonResponse([
            'status' => true,
            'error' => ''
        ]));
    }
}