<?php
namespace AuthModule\Adapter;

use Zend\Authentication\Result;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Session\Container;
use Zend\Session\SessionManager;

class Facebook extends \BaseFacebook implements AdapterInterface
{

    /**
     * @var SessionManager
     */
    protected $sessionManager;

    /**
     * @var Container
     */
    protected $session;

    protected $scope;

    protected $identityField = 'email';

    protected $authenticatedUserData;


    /**
     * Similar to the parent constructor, except that
     * we SessionManager to store the user ID and
     * access token if during the course of execution
     * we discover them.
     *
     * The configuration:
     * - appId: the application ID
     * - secret: the application secret
     * - fileUpload: (optional) boolean indicating if file uploads are enabled
     * - scope: (optional) default "scope"
     *
     *
     * @param array $config
     * @param SessionManager $sessionManager
     */
    public function __construct($config, SessionManager $sessionManager, $namespace = __NAMESPACE__)
    {
        $this->sessionManager = $sessionManager;
        $this->session = new Container($namespace . $this->getAppId(), $sessionManager);
        parent::__construct($config);
        if (isset($config['scope'])) {
            $this->scope = $config['scope'];
        }
    }

    protected static $kSupportedKeys = array('state','code','access_token','user_id');

    /**
     * Provides the implementations of the inherited abstract
     * methods.
     * The implementation uses PHP sessions to maintain
     * a store for authorization codes, user ids, CSRF states, and
     * access tokens.
     */
    protected function setPersistentData($key, $value)
    {
        if (!in_array($key, self::$kSupportedKeys)) {
            self::errorLog('Unsupported key passed to setPersistentData.');
            return;
        }
        $this->session[$key] = $value;
    }

    protected function getPersistentData($key, $default = false)
    {
        if (!in_array($key, self::$kSupportedKeys)) {
            self::errorLog('Unsupported key passed to getPersistentData.');
            return $default;
        }
        return isset($this->session[$key]) ? $this->session[$key] : $default;
    }

    protected function clearPersistentData($key)
    {
        if (!in_array($key, self::$kSupportedKeys)) {
            self::errorLog('Unsupported key passed to clearPersistentData.');
            return;
        }
        unset($this->session[$key]);
    }

    protected function clearAllPersistentData()
    {
        foreach (self::$kSupportedKeys as $key) {
            unset($this->session[$key]);
        }
    }

    /**
    * Get a Login URL for use with redirects. By default, full page redirect is
    * assumed. If you are using the generated URL with a window.open() call in
    * JavaScript, you can pass in display=popup as part of the $params.
    *
    * The parameters:
    * - redirect_uri: the url to go to after a successful login
    * - scope: comma separated list of requested extended perms
    *
    * @param array $params Provide custom parameters
    * @return string The URL for the login flow
    */
    public function getLoginUrl($params = array())
    {
        if (!isset($params['scope'])) {
            $params['scope'] = $this->scope;
        }

        return parent::getLoginUrl($params);
    }


    public function authenticatedUserData()
    {
        return $this->authenticatedUserData;
    }

    /**
     * @return Result
     */
    public function authenticate()
    {
        $token  = $this->getAccessToken();

        if (empty($token)) {
            return new Result(Result::FAILURE, null);
        }

        $data   = $this->api('/me');
        $fbId   = $data['id'];

        if (empty($fbId)) {
            return new Result(Result::FAILURE, null);
        }


        if (!empty($data[$this->identityField])) {
            $this->authenticatedUserData = $data;
            return new Result(Result::SUCCESS, $data[$this->identityField]);
        }

        return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, null);
    }

}
