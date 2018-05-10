<?php

use League\OAuth2\Server\Entities\ClientEntityInterface;
use Monoless\Xe\OAuth2\Server\Repositories\AccessTokenRepository;
use Monoless\Xe\OAuth2\Server\Repositories\ClientRepository;
use Monoless\Xe\OAuth2\Server\Repositories\GrantAppRepository;
use Monoless\Xe\OAuth2\Server\Repositories\RefreshTokenRepository;
use Monoless\Xe\OAuth2\Server\Entities\ClientEntity;
use Monoless\Xe\OAuth2\Server\Entities\PageContainerEntity;

class devcenterModel extends devcenter
{
    const MODULE_NAME = 'devcenter';

    /**
     * @var ClientRepository
     */
    private $clientRepository;

    /**
     * @var GrantAppRepository
     */
    private $grantAppRepository;

    /**
     * @var AccessTokenRepository
     */
    private $accessTokenRepository;

    /**
     * @var RefreshTokenRepository
     */
    private $refreshTokenRepository;

    private static $keys = null;

    private static $config = null;

    public function __construct($error = 0, $message = 'success')
    {
        parent::__construct($error, $message);

        $this->clientRepository = new ClientRepository();
        $this->grantAppRepository = new GrantAppRepository();
        $this->accessTokenRepository = new AccessTokenRepository();
        $this->refreshTokenRepository = new RefreshTokenRepository();
    }

    /**
     * @return stdClass|null
     */
    public function getConfig()
    {
        if (null == self::$config) {
            $oModuleModel = getModel('module');
            $config = $oModuleModel->getModuleConfig(self::MODULE_NAME);
            if (!$config) {
                $config = new stdClass();
            }

            self::$config = $config;
        }

        return self::$config;
    }

    public function saveConfig(\stdClass $config)
    {
        /**
         * @var \moduleController $controller
         */
        $controller = getController('module');
        $controller->insertModuleConfig(self::MODULE_NAME, $config);
    }

    /**
     * @return array|null
     */
    public function getKeys()
    {
        if (null == self::$keys) {
            $path = './files/devcenter/keys.php';
            if (\FileHandler::exists($path)) {
                $raw = explode("\n", file_get_contents($path));
                if (is_array($raw) && 2 <= count($raw)) {
                    array_shift($raw);
                    self::$keys = unserialize(implode("\n", $raw));
                }
            }
        }

        return self::$keys;
    }

    public function getSitemap()
    {
        $path = './files/devcenter/sitemap.json';
        if (\FileHandler::exists($path)) {
            if (time() > filemtime($path) + 60) {
                return null;
            }

            return file_get_contents($path);
        }

        return null;
    }

    public function setSitemap($json)
    {
        $path = './files/devcenter/sitemap.json';
        file_put_contents($path, $json);
    }

    /**
     * @param ClientEntity $clientEntity
     * @return boolean
     */
    public function createApp(ClientEntity $clientEntity)
    {
        return $this->clientRepository->persistNewClient($clientEntity);
    }

    /**
     * @param ClientEntity $clientEntity
     * @return boolean
     */
    public function modifyApp(ClientEntity $clientEntity)
    {
        return $this->clientRepository->persistClient($clientEntity);
    }

    /**
     * @param ClientEntity $clientEntity
     * @return boolean
     */
    public function deleteApp(ClientEntity $clientEntity)
    {
        return $this->clientRepository->removeClient($clientEntity);
    }

    /**
     * @param ClientEntity $clientEntity
     * @param integer $memberSrl
     * @return boolean
     */
    public function revokeApp(ClientEntity $clientEntity, $memberSrl)
    {
        try {
            $this->grantAppRepository->revokeGrantApp($clientEntity->getIdentifier(), $memberSrl);
            $this->refreshTokenRepository->revokeRefreshTokenByUniqueAppSrlAndMemberSrl(
                $clientEntity->getIdentifier(), $memberSrl);
            $this->accessTokenRepository->revokeAccessTokenByUniqueAppSrlAndMemberSrl(
                $clientEntity->getIdentifier(), $memberSrl);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function isAppExistByName($name)
    {
        return $this->clientRepository->isAppExistByName($name);
    }

    /**
     * @param string $clientIdentifier
     * @return ClientEntityInterface|ClientEntity
     */
    public function getAppByClientId($clientIdentifier)
    {
        return $this->clientRepository->getClientEntity(
            $clientIdentifier,
            null,
            null,
            false);
    }

    /**
     * @param integer $listCount
     * @param integer $pageCount
     * @param integer $page
     * @return PageContainerEntity
     */
    public function getApps($listCount = 20, $pageCount = 10, $page = 1)
    {
        return $this->clientRepository->getApps(
            $listCount,
            $pageCount,
            $page);
    }

    /**
     * @param integer $memberSrl
     * @param integer $listCount
     * @param integer $pageCount
     * @param integer $page
     * @return PageContainerEntity
     */
    public function getAppsByMemberSrl($memberSrl, $listCount = 20, $pageCount = 10, $page = 1)
    {
        return $this->clientRepository->getAppsByMemberSrl(
            $memberSrl,
            $listCount,
            $pageCount,
            $page);
    }

    /**
     * @param integer $memberSrl
     * @param integer $listCount
     * @param integer $pageCount
     * @param integer $page
     * @return PageContainerEntity
     */
    public function getGrantAppsByMemberSrl($memberSrl, $listCount = 20, $pageCount = 10, $page = 1)
    {
        return $this->grantAppRepository->getGrantAppsByMemberSrl(
            $memberSrl,
            $listCount,
            $pageCount,
            $page);
    }
}