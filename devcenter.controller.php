<?php

use Monoless\Xe\OAuth2\Server\Entities\ClientEntity;
use Monoless\Xe\OAuth2\Server\Entities\UserEntity;
use Monoless\Xe\OAuth2\Server\Services\AuthorizationService;
use Monoless\Xe\OAuth2\Server\Utils\ResponseUtil;
use Monoless\Xe\OAuth2\Server\Utils\XpressUtil;
use Monoless\Xe\OAuth2\Server\Utils\ScopeUtil;
use League\OAuth2\Server\Exception\OAuthServerException;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\UploadedFile;
use Zend\Diactoros\Response\JsonResponse;

class devcenterController extends devcenter
{
    const MODULE_NAME = 'devcenter';
    const SALT = 'DEVCENTER-';
    const ACTION_REGISTERED = 1;

    const THUMBNAIL_SIZE = 1048576;
    const THUMBNAIL_WIDTH = 72;
    const THUMBNAIL_HEIGHT = 72;

	/**
	 * Initialization
	 * @return void
	 */
	public function init()
	{
	}

    public function triggerAddMemberMenu()
    {
        $oMemberController = getController('member');
        $oMemberController->addMemberMenu('dispDevcenterGrantApp', 'devcenter_grant');
        return new BaseObject();
    }

    public function procDevcenterRegisterApp()
    {
        $request = ServerRequest::fromGlobals();

        /**
         * @var \devcenterModel $devcenterModel
         */
        $devcenterModel = getModel(self::MODULE_NAME);

        /**
         * @var \stdClass $loggedInfo
         */
        $loggedInfo = \Context::get('logged_info');

        $params = $request->getParsedBody();
        $appName = array_key_exists('app_name', $params) ? $params['app_name'] : '';
        $appDescription = array_key_exists('app_description', $params) ? $params['app_description'] : '';
        $appCallback = array_key_exists('app_callback', $params) ? $params['app_callback'] : '';

        if ($devcenterModel->isAppExistByName($appName)) {
            return new BaseObject(-1,'devcenter_app_name_exists');
        }

        $uniqueAppSrl = XpressUtil::getUniqueSrl($loggedInfo->member_srl);

        $clientEntity = new ClientEntity();
        $clientEntity->setIdentifier($uniqueAppSrl);
        $clientEntity->setMemberSrl($loggedInfo->member_srl);
        $clientEntity->setName($appName);
        $clientEntity->setThumbnail('');
        $clientEntity->setDescription($appDescription);
        $clientEntity->setWebsiteUrl('');
        $clientEntity->setRedirectUri($appCallback ? $appCallback : '');
        $clientEntity->setScope(ScopeUtil::permissionToScope('w'));
        $clientEntity->setClientSecret(XpressUtil::getSecretKey($loggedInfo->member_srl));
        $clientEntity->setCreatedAt(date('YmdHis'));
        $clientEntity->setUpdatedAt(date('YmdHis'));

        if (!$devcenterModel->createApp($clientEntity)) {
            return new BaseObject(-1,'msg_invalid_request');
        }

        $redirectUrl = Context::get('success_return_url') ?
            Context::get('success_return_url') :
            getNotEncodedUrl('', 'mid', Context::get('mid'), 'act', 'dispDevcenterConfigApp');

        $this->setRedirectUrl($redirectUrl);
    }

    public function procDevcenterUploadThumbnail()
    {
        $request = ServerRequest::fromGlobals();

        /**
         * @var \stdClass $loggedInfo
         */
        $loggedInfo = \Context::get('logged_info');

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
        } elseif ($entry->getMemberSrl() != $loggedInfo->member_srl) {
            ResponseUtil::finalizeResponse(new JsonResponse([
                'status' => false,
                'error' => 'permission_denied',
            ], 401));
        }

        /**
         * @var UploadedFile[] $files
         */
        $files = $request->getUploadedFiles();
        $mimes = ['image/gif', 'image/jpeg', 'image/png'];
        if (!array_key_exists('file', $files) || !in_array($files['file']->getClientMediaType(), $mimes)) {
            ResponseUtil::finalizeResponse(new JsonResponse([
                'status' => false,
                'error' => 'file_invalid',
            ], 406));
        } elseif (self::THUMBNAIL_SIZE <= $files['file']->getSize()) {
            ResponseUtil::finalizeResponse(new JsonResponse([
                'status' => false,
                'error' => 'file_too_big',
            ], 406));
        }

        try {
            $path = XpressUtil::getThumbnailFolderPath($uniqueAppSrl, $loggedInfo->member_srl);
            $filePath = XpressUtil::getThumbnailFilePath($path);
            \FileHandler::makeDir($path);
            $files['file']->moveTo($filePath);
            \FileHandler::createImageFile(
                $filePath,
                $filePath,
                self::THUMBNAIL_WIDTH,
                self::THUMBNAIL_HEIGHT
            );
        } catch (\Exception $e) {
            $filePath = '';

            ResponseUtil::finalizeResponse(new JsonResponse([
                'status' => false,
                'error' => 'something_wrong',
            ], 500));
        }

        ResponseUtil::finalizeResponse(new JsonResponse([
            'status' => true,
            'error' => '',
            'path' => $filePath
        ]));
    }

    public function procDevcenterModifyApp()
    {
        $request = ServerRequest::fromGlobals();

        /**
         * @var \stdClass $loggedInfo
         */
        $loggedInfo = \Context::get('logged_info');

        /**
         * @var \devcenterModel $devcenterModel
         */
        $devcenterModel = getModel(self::MODULE_NAME);

        $params = $request->getParsedBody();
        $uniqueAppSrl = array_key_exists('unique_app_srl', $params) ? $params['unique_app_srl'] : null;
        $appThumbnail = array_key_exists('app_thumbnail', $params) ? $params['app_thumbnail'] : '';
        $appDescription = array_key_exists('app_description', $params) ? $params['app_description'] : '';
        $appCallback = array_key_exists('app_callback', $params) ? $params['app_callback'] : '';
        $appWebsite = array_key_exists('app_website', $params) ? $params['app_website'] : '';
        $appScope = array_key_exists('app_scope', $params) ? $params['app_scope'] : '';

        $entry = $devcenterModel->getAppByClientId($uniqueAppSrl);
        if (!$entry) {
            return new BaseObject(-1,'devcenter_app_not_exists');
        } elseif ($entry->getMemberSrl() != $loggedInfo->member_srl) {
            return new BaseObject(-1,'devcenter_app_permission_denied');
        }

        $entry->setIdentifier($uniqueAppSrl);
        $entry->setThumbnail($appThumbnail);
        $entry->setDescription($appDescription);
        $entry->setWebsiteUrl($appWebsite);
        $entry->setRedirectUri($appCallback);
        $entry->setScope(ScopeUtil::permissionToScope($appScope));
        $entry->setUpdatedAt(date('YmdHis'));
        $status = $devcenterModel->modifyApp($entry);
        if (!$status) {
            return new BaseObject(-1,'msg_invalid_request');
        }

        $redirectUrl = Context::get('success_return_url') ?
            Context::get('success_return_url') :
            getNotEncodedUrl(
                '',
                'mid',
                Context::get('mid'),
                'act',
                'dispDevcenterConfigApp'
            );

        $this->setRedirectUrl($redirectUrl);
    }

    public function procDevcenterRemoveApp()
    {$request = ServerRequest::fromGlobals();

        /**
         * @var \stdClass $loggedInfo
         */
        $loggedInfo = \Context::get('logged_info');

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
        } elseif ($entry->getMemberSrl() != $loggedInfo->member_srl) {
            ResponseUtil::finalizeResponse(new JsonResponse([
                'status' => false,
                'error' => 'permission_denied',
            ], 401));
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

    public function procDevcenterRevokeApp()
    {
        $request = ServerRequest::fromGlobals();

        /**
         * @var \stdClass $loggedInfo
         */
        $loggedInfo = \Context::get('logged_info');

        /**
         * @var \devcenterModel $devcenterModel
         */
        $devcenterModel = getModel(self::MODULE_NAME);

        $params = $request->getParsedBody();
        $uniqueAppSrl = array_key_exists('unique_app_srl', $params) ? $params['unique_app_srl'] : null;

        $entry = $devcenterModel->getAppByClientId($uniqueAppSrl);

        if (!$entry) {
            return new BaseObject(-1,'devcenter_app_not_exists');
        }

        $status = $devcenterModel->revokeApp($entry, $loggedInfo->member_srl);
        if (!$status) {
            return new BaseObject(-1,'msg_invalid_request');
        }

        $redirectUrl = Context::get('success_return_url') ?
            Context::get('success_return_url') :
            getNotEncodedUrl(
                '',
                'mid',
                Context::get('mid'),
                'act',
                'dispDevcenterGrantApp'
            );

        $this->setRedirectUrl($redirectUrl);
    }

    public function procDevcenterChangeSecret()
    {
        $request = ServerRequest::fromGlobals();

        /**
         * @var \stdClass $loggedInfo
         */
        $loggedInfo = \Context::get('logged_info');

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
        } elseif ($entry->getMemberSrl() != $loggedInfo->member_srl) {
            ResponseUtil::finalizeResponse(new JsonResponse([
                'status' => false,
                'error' => 'permission_denied',
            ], 401));
        }

        $entry->setClientSecret(XpressUtil::getSecretKey($loggedInfo->member_srl));
        $status = $devcenterModel->modifyApp($entry);
        if (!$status) {
            ResponseUtil::finalizeResponse(new JsonResponse([
                'status' => false,
                'error' => 'invalid_request',
            ], 500));
        }

        ResponseUtil::finalizeResponse(new JsonResponse([
            'status' => true,
            'error' => '',
            'client_secret' => $entry->getClientSecret()
        ]));
    }

    public function procDevcenterAuthorize()
    {
        /**
         * @var \devcenterModel $model
         */
        $model = getModel(self::MODULE_NAME);
        $keys = $model->getKeys();

        if (null == $keys) {
            return new BaseObject(-1,'msg_invalid_request');
        }

        try {
            $server = AuthorizationService::getServer($keys['private'], $keys['encryption']);
            $memberSrl = Context::get('member_srl') ? Context::get('member_srl') : Context::get('logged_info')->member_srl;

            $serverRequest = ServerRequest::fromGlobals();
            $serverResponse = new Response();

            $userEntity = new UserEntity();
            $userEntity->setIdentifier($memberSrl);

            $response = AuthorizationService::approve($server, $serverRequest, $serverResponse, $userEntity);
        } catch (OAuthServerException $exception) {

            return new BaseObject(-1,'msg_invalid_request');
        } catch (\Exception $exception) {

            return new BaseObject(-1,'msg_invalid_request');
        }

        ResponseUtil::finalizeResponse($response);
    }
}
