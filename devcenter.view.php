<?php

use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Response;
use Monoless\Xe\OAuth2\Server\Services\AuthorizationService;
use Monoless\Xe\OAuth2\Server\Services\ResourceService;
use Monoless\Xe\OAuth2\Server\Services\XpressService;
use Monoless\Xe\OAuth2\Server\Entities\ClientEntity;
use Monoless\Xe\OAuth2\Server\Utils\ResponseUtil;
use Monoless\Xe\OAuth2\Supports\XpressSupport;
use Zend\Diactoros\Response\JsonResponse;

class devcenterView extends devcenter
{
    const MODULE_NAME = 'devcenter';
    const LIST_COUNT = 20;
    const PAGE_COUNT = 10;

    /**
     * @return null|stdClass
     */
    private function getConfig()
    {
        /**
         * @var \devcenterModel $model
         */
        $model = getModel(self::MODULE_NAME);
        return $model->getConfig();
    }

    /**
     * @return string
     */
    private function getPublicKeyPath()
    {
        /**
         * @var \devcenterModel $model
         */
        $model = getModel(self::MODULE_NAME);
        $keys = $model->getKeys();
        return $keys ? $keys['public'] : null;
    }

    /**
     * @return string
     */
    private function getPrivateKeyPath()
    {
        /**
         * @var \devcenterModel $model
         */
        $model = getModel(self::MODULE_NAME);
        $keys = $model->getKeys();
        return $keys ? $keys['private'] : null;
    }

    /**
     * @return string
     */
    private function getEncryptionKey()
    {
        /**
         * @var \devcenterModel $model
         */
        $model = getModel(self::MODULE_NAME);
        $keys = $model->getKeys();
        return $keys ? $keys['encryption'] : null;
    }

    public function init()
    {
        $this->setTemplatePath(sprintf("%sskins/%s/", $this->module_path, 'default'));

        /**
         * @var \layoutModel $layoutModel
         */
        $layoutModel = getModel('layout');

        /**
         * @var \devcenterModel $devcenterModel
         */
        $devcenterModel = getModel(self::MODULE_NAME);

        $config = $devcenterModel->getConfig();
        $layout_info = $layoutModel->getLayout($config->layout_srl);

        if ($layout_info) {
            $this->module_info->layout_srl = $config->layout_srl;
            $this->setLayoutPath($layout_info->path);
        }
    }

    public function dispDevcenterGrantApp()
    {
        $request = ServerRequest::fromGlobals();

        /**
         * @var \devcenterModel $devcenterModel
         */
        $devcenterModel = getModel(self::MODULE_NAME);

        $params = $request->getQueryParams();
        $page = array_key_exists('page', $params) ?
            (is_numeric($params['page']) ? $params['page'] : 1):
            1;

        /**
         * @var \stdClass $loggedInfo
         */
        $loggedInfo = \Context::get('logged_info');

        $container = $devcenterModel->getGrantAppsByMemberSrl(
            $loggedInfo->member_srl,
            self::LIST_COUNT,
            self::PAGE_COUNT,
            $page);

        \Context::set('config', $devcenterModel->getConfig());
        \Context::set('container', $container);
        $this->setTemplateFile('GrantList');
    }

    public function dispDevcenterRegisterApp()
    {
        $this->setTemplateFile('RegisterApp');
    }

    public function dispDevcenterConfigApp()
    {
        $request = ServerRequest::fromGlobals();

        /**
         * @var \devcenterModel $devcenterModel
         */
        $devcenterModel = getModel(self::MODULE_NAME);

        $params = $request->getQueryParams();
        $page = array_key_exists('page', $params) ?
            (is_numeric($params['page']) ? $params['page'] : 1):
            1;

        /**
         * @var \stdClass $loggedInfo
         */
        $loggedInfo = \Context::get('logged_info');

        $container = $devcenterModel->getAppsByMemberSrl(
            $loggedInfo->member_srl,
            self::LIST_COUNT,
            self::PAGE_COUNT,
            $page);

        \Context::set('config', $devcenterModel->getConfig());
        \Context::set('container', $container);
        $this->setTemplateFile('ConfigList');
    }

    public function dispDevcenterModifyApp()
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

        $params = $request->getQueryParams();
        $clientId = array_key_exists('client_id', $params) ? $params['client_id']: null;

        $entry = ($clientId) ? $devcenterModel->getAppByClientId($clientId) : null;

        if (!$entry || $loggedInfo->member_srl != $entry->getMemberSrl()) {
            return new \BaseObject(-1, 'msg_module_not_exists');
        }

        \Context::set('config', $devcenterModel->getConfig());
        \Context::set('entry', $entry);
        $this->setTemplateFile('ModifyApp');
    }

    public function dispDevcenterRevokeApp()
    {
        $request = ServerRequest::fromGlobals();

        /**
         * @var \devcenterModel $devcenterModel
         */
        $devcenterModel = getModel(self::MODULE_NAME);

        $params = $request->getQueryParams();
        $clientId = array_key_exists('client_id', $params) ? $params['client_id']: null;

        $entry = ($clientId) ? $devcenterModel->getAppByClientId($clientId) : null;

        if (!$entry) {
            return new \BaseObject(-1, 'msg_module_not_exists');
        }

        \Context::set('entry', $entry);
        \Context::set('config', $devcenterModel->getConfig());
        $this->setTemplateFile('RevokeApp');
    }

    public function dispDevcenterAuthorize()
    {
        $request = ServerRequest::fromGlobals();

        /**
         * @var \devcenterModel $devcenterModel
         */
        $devcenterModel = getModel(self::MODULE_NAME);

        /**
         * @var \memberModel $memberModel
         */
        $memberModel = getModel('member');

        \Context::addHtmlHeader('<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">');

        $params = $request->getQueryParams();
        $entry = array_key_exists('client_id', $params) ? $devcenterModel->getAppByClientId($params['client_id']) : null;
        if (true === $entry instanceof ClientEntity) {
            // Get member information
            $memberInfo = $memberModel->getMemberInfoByMemberSrl($entry->getMemberSrl());

            $state = array_key_exists('state', $params) ? $params['state'] : '';

            \Context::set('config', $devcenterModel->getConfig());
            \Context::set('self', ResponseUtil::authorizeRedirectUrl($request, $entry, $state));
            \Context::set('entry', $entry);
            \Context::set('memberInfo', $memberInfo);
            $this->setTemplateFile('Authorize');
        } else {
            $this->setTemplateFile('AuthorizeNotFound');
        }
    }

    public function token()
    {
        $serverRequest = ServerRequest::fromGlobals();
        $serverResponse = new Response();

        // prevent html response
        \Context::setResponseMethod('JSON');

        if ('POST' != $serverRequest->getMethod()) {
            return new BaseObject();
        }

        $privateKeyPath = $this->getPrivateKeyPath();
        $encryptionKey = $this->getEncryptionKey();

        try {
            $server = AuthorizationService::getServer($privateKeyPath, $encryptionKey);
            $serverResponse = AuthorizationService::respondToAccessTokenRequest($server, $serverRequest, $serverResponse, $encryptionKey);
            ResponseUtil::finalizeResponse($serverResponse);
        } catch (OAuthServerException $exception) {
            ResponseUtil::finalizeResponse($exception->generateHttpResponse($serverResponse));
        } catch (\Exception $exception) {
            ResponseUtil::finalizeExceptionResponse($serverResponse, $exception);
        }
    }

    public function profile()
    {
        $publicKeyPath = $this->getPublicKeyPath();

        \Context::setResponseMethod('JSON');
        ResourceService::processResource(
            $publicKeyPath,
            $this->getConfig(),
            function (RequestInterface $request, ResponseInterface $response) {
                $method = strtoupper($request->getMethod());
                if ('GET' == $method) {
                    return XpressService::getProfile($request, $response);
                } else {
                    return ResponseUtil::notSupportedMethod();
                }
            }
        );
    }

    public function friend()
    {
        $publicKeyPath = $this->getPublicKeyPath();

        \Context::setResponseMethod('JSON');
        ResourceService::processResource(
            $publicKeyPath,
            $this->getConfig(),
            function (RequestInterface $request, ResponseInterface $response) {
                $method = strtoupper($request->getMethod());
                if ('GET' == $method) {
                    return XpressService::getFriends($request, $response);
                } elseif ('POST' == $method) {
                    return XpressService::postFriend($request, $response);
                } elseif ('DELETE' == $method) {
                    return XpressService::deleteFriend($request, $response);
                } else {
                    return ResponseUtil::notSupportedMethod();
                }
            }
        );
    }

    public function message()
    {
        $publicKeyPath = $this->getPublicKeyPath();

        \Context::setResponseMethod('JSON');
        ResourceService::processResource(
            $publicKeyPath,
            $this->getConfig(),
            function (RequestInterface $request, ResponseInterface $response) {
                $method = strtoupper($request->getMethod());
                if ('GET' == $method) {
                    return XpressService::getMessages($request, $response);
                } elseif ('POST' == $method) {
                    return XpressService::postMessage($request, $response);
                } elseif ('DELETE' == $method) {
                    return XpressService::deleteMessage($request, $response);
                } else {
                    return ResponseUtil::notSupportedMethod();
                }
            }
        );
    }

    public function login_history()
    {
        $publicKeyPath = $this->getPublicKeyPath();

        \Context::setResponseMethod('JSON');
        ResourceService::processResource(
            $publicKeyPath,
            $this->getConfig(),
            function (RequestInterface $request, ResponseInterface $response) {
                $method = strtoupper($request->getMethod());
                if ('GET' == $method) {
                    return XpressService::getLoginHistories($request, $response);
                } else {
                    return ResponseUtil::notSupportedMethod();
                }
            }
        );
    }

    public function sitemap()
    {
        /**
         * @var \devcenterModel $model
         */
        $model = getModel(self::MODULE_NAME);

        $request = ServerRequest::fromGlobals();
        $response = new Response();

        $sitemap = $model->getSitemap();
        if (!$sitemap) {
            $sitemap = XpressService::getSitemap($request, $response);
            $model->setSitemap((string)$sitemap->getBody());
        } else {
            $sitemap = new JsonResponse(json_decode($sitemap));
        }

        \Context::setResponseMethod('JSON');
        ResponseUtil::finalizeResponse($sitemap);
    }

    public function article()
    {
        $publicKeyPath = $this->getPublicKeyPath();

        $request = ServerRequest::fromGlobals();
        $queries = $request->getQueryParams();
        $mId = array_key_exists('board', $queries) ? $queries['board'] : '';
        $articleSrl = array_key_exists('article_srl', $queries) ? $queries['article_srl'] : '';
        if ('GET' == strtoupper($request->getMethod()) && $mId) {
            $loggedInfo = \Context::get('logged_info');
            $moduleModel = \getModel('module');
            $moduleInfo = XpressSupport::getModuleInfoByMId($mId);
            $grant = $moduleModel->getGrant($moduleInfo, $loggedInfo);
        } elseif ('GET' == strtoupper($request->getMethod()) && $articleSrl) {
            $loggedInfo = \Context::get('logged_info');
            $moduleModel = \getModel('module');
            $moduleInfo = XpressSupport::getModuleInfoByArticleSrl($articleSrl);
            $grant = $moduleModel->getGrant($moduleInfo, $loggedInfo);
        } else {
            $grant = null;
        }

        if ($grant && $articleSrl) {
            $ignoreSession = $grant->view ? $grant->view : false;
        } elseif ($grant && $mId) {
            $ignoreSession = $grant->list ? $grant->list : false;
        } else {
            $ignoreSession = false;
        }

        \Context::setResponseMethod('JSON');
        ResourceService::processResource(
            $publicKeyPath,
            $this->getConfig(),
            function (RequestInterface $request, ResponseInterface $response) use ($ignoreSession) {
                $method = strtoupper($request->getMethod());
                if ('GET' == $method) {
                    return XpressService::getArticles($request, $response, $ignoreSession);
                } elseif ('POST' == $method) {
                    return XpressService::postArticle($request, $response);
                } elseif ('PUT' == $method) {
                    return XpressService::updateArticle($request, $response);
                } elseif ('DELETE' == $method) {
                    return XpressService::deleteArticle($request, $response);
                } else {
                    return ResponseUtil::notSupportedMethod();
                }
            },
            $ignoreSession
        );
    }

    public function comment()
    {
        $publicKeyPath = $this->getPublicKeyPath();

        $request = ServerRequest::fromGlobals();
        $queries = $request->getQueryParams();
        $articleSrl = array_key_exists('article_srl', $queries) ? $queries['article_srl'] : '';
        if ('GET' == strtoupper($request->getMethod()) && $articleSrl) {
            $loggedInfo = \Context::get('logged_info');
            $moduleModel = \getModel('module');
            $moduleInfo = XpressSupport::getModuleInfoByArticleSrl($articleSrl);
            $grant = $moduleModel->getGrant($moduleInfo, $loggedInfo);
        } else {
            $grant = null;
        }

        if ($grant && $articleSrl) {
            $ignoreSession = $grant->view ? $grant->view : false;
        } else {
            $ignoreSession = false;
        }

        \Context::setResponseMethod('JSON');
        ResourceService::processResource(
            $publicKeyPath,
            $this->getConfig(),
            function (RequestInterface $request, ResponseInterface $response) use ($ignoreSession) {
                $method = strtoupper($request->getMethod());
                if ('GET' == $method) {
                    return XpressService::getComments($request, $response, $ignoreSession);
                } elseif ('POST' == $method) {
                    return XpressService::postComment($request, $response);
                } elseif ('PUT' == $method) {
                    return XpressService::updateComment($request, $response);
                } elseif ('DELETE' == $method) {
                    return XpressService::deleteComment($request, $response);
                } else {
                    return ResponseUtil::notSupportedMethod();
                }
            },
            $ignoreSession
        );
    }

    public function scrap()
    {
        $publicKeyPath = $this->getPublicKeyPath();

        \Context::setResponseMethod('JSON');
        ResourceService::processResource(
            $publicKeyPath,
            $this->getConfig(),
            function (RequestInterface $request, ResponseInterface $response) {
                $method = strtoupper($request->getMethod());
                if ('GET' == $method) {
                    return XpressService::getScraps($request, $response);
                } elseif ('POST' == $method) {
                    return XpressService::postScrap($request, $response);
                } elseif ('DELETE' == $method) {
                    return XpressService::deleteScrap($request, $response);
                } else {
                    return ResponseUtil::notSupportedMethod();
                }
            }
        );
    }

    public function my_article()
    {
        $publicKeyPath = $this->getPublicKeyPath();

        \Context::setResponseMethod('JSON');
        ResourceService::processResource(
            $publicKeyPath,
            $this->getConfig(),
            function (RequestInterface $request, ResponseInterface $response) {
                $method = strtoupper($request->getMethod());
                if ('GET' == $method) {
                    return XpressService::getMyArticles($request, $response);
                } else {
                    return ResponseUtil::notSupportedMethod();
                }
            }
        );
    }

    public function my_comment()
    {
        $publicKeyPath = $this->getPublicKeyPath();

        \Context::setResponseMethod('JSON');
        ResourceService::processResource(
            $publicKeyPath,
            $this->getConfig(),
            function (RequestInterface $request, ResponseInterface $response) {
                $method = strtoupper($request->getMethod());
                if ('GET' == $method) {
                    return XpressService::getMyComments($request, $response);
                } else {
                    return ResponseUtil::notSupportedMethod();
                }
            }
        );
    }
}