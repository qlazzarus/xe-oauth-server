<?php
define('_XE_DEVCENTER_AUTOLOAD', _XE_PATH_ . 'modules/devcenter/vendor/autoload.php');
if (file_exists(_XE_DEVCENTER_AUTOLOAD)) {
    require_once(_XE_DEVCENTER_AUTOLOAD);
}

if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

class devcenter extends ModuleObject
{
    const MODULE_NAME = 'devcenter';

    private $triggers = [
        // 회원 메뉴 등록
        ['moduleHandler.init', self::MODULE_NAME, 'controller', 'triggerAddMemberMenu', 'after']
    ];

    private function composerInstall()
    {
        chmod(_XE_PATH_ . 'modules/devcenter', 0775);
        chmod(_XE_PATH_ . 'modules/devcenter/composer.phar', 0775);
        exec(_XE_PATH_ . 'modules/devcenter/composer.phar install --working-dir=' . _XE_PATH_ . 'modules/devcenter', $output);

        file_put_contents('install.log', implode("\n", $output));
    }

    private function generateCertificate()
    {
        // generate 2048-bit RSA key
        $generate = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA
        ]);

        // get the private key
        openssl_pkey_export($generate,$private); // NOTE: second argument is passed by reference

        // get the public key
        $details = openssl_pkey_get_details($generate);
        $public = $details['key'];

        // free resources
        openssl_pkey_free($generate);

        $keys = [
            'public' => $public,
            'private' => $private,
            'encryption' => base64_encode(random_bytes(32))
        ];

        $path = './files/devcenter';

        if (!\FileHandler::exists($path)) {
            \FileHandler::makeDir($path);
        }

        $raw = implode("\n", [
            "<?php exit(); ?>",
            serialize($keys)
        ]);

        file_put_contents($path . '/keys.php', $raw);
    }

    private function generateConfig()
    {
        /**
         * @var \moduleController $controller
         */
        $controller = getController('module');

        /**
         * @var \moduleModel $model
         */
        $model = getModel('module');

        $config = $model->getModuleConfig(self::MODULE_NAME);

        if (empty($config)) {
            $config = new \stdClass();
            $config->use_rate_limiter = false;
            $config->rate_limit_capacity = 45;
            $config->redis_host = '127.0.0.1';
            $config->redis_port = 6379;
            $config->use_app_thumbnail = false;
        }

        $controller->insertModuleConfig(self::MODULE_NAME, $config);
    }

	/**
	 * Install Devcenter module
	 * @return BaseObject
	 */
	public function moduleInstall()
	{
        ini_set('memory_limit', '1G');
        set_time_limit(300);

	    $this->composerInstall();
	    $this->generateCertificate();
	    $this->generateConfig();
		return new BaseObject();
	}

	/**
	 * If update is necessary it returns true
	 * @return bool
	 */
	public function checkUpdate()
	{
        /**
         * @var \moduleModel $moduleModel
         */
        $moduleModel = getModel('module');

        foreach($this->triggers as $trigger) {
            if (!$moduleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4])) {
                return true;
            }
        }

		return false;
	}

	/**
	 * Update module
	 * @return BaseObject
	 */
	public function moduleUpdate()
	{
        ini_set('memory_limit', '1G');
        set_time_limit(300);

        $this->composerInstall();

        /**
         * @var \moduleModel $moduleModel
         */
        $moduleModel = getModel('module');

        /**
         * @var \moduleController $moduleController
         */
        $moduleController = getController('module');

        foreach($this->triggers as $trigger) {
            if (!$moduleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4])) {
                $moduleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
            }
        }

        return new BaseObject(0, 'success_updated');
	}

    /**
     * @return BaseObject
     */
	public function recompileCache()
	{
		return new BaseObject();
	}

    /**
     * @return BaseObject
     */
    public function moduleUninstall()
    {
        /**
         * @var \moduleController $moduleController
         */
        $moduleController = getController('module');

        foreach($this->triggers as $trigger) {
            $moduleController->deleteTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
        }

        return new BaseObject();
    }
}