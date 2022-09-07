<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Description of MY_Router
 *
 * @author hoksi
 * @property Nuna_Router $router
 * @property CI_Config $config
 * @property CI_URI $uri
 * @property CI_Log $log
 * @property CI_Benchmark $benchmark
 * @property CI_Utf8 utf8
 * @property CI_Output $output
 * @property CI_Security $security
 * @property CI_Input $input
 * @property CI_Lang $lang
 * @property CI_Tpl $tpl
 * @property Nuna_Loader $load
 * @property CI_Qb $qb
 * @property CI_Event $event
 * @property CI_Email $email
 * @property CI_Parser $parser
 */
class Forbiz extends CI_Controller
{
    public $router;
    public $config;
    public $uri;
    public $benchmark;
    public $log;
    public $utf8;
    public $output;
    public $security;
    public $input;
    public $lang;
    public $tpl;
    public $load;
    public $qb;
    public $class_name;
    public $event;
    protected $runMethod;
    protected $runClass;
    private $flashData;

    public function __construct()
    {
        parent::__construct();

        if (is_cli()) {
            throw new Exception('This is a WEB only!');
        } else {
            $this->class_name = get_class($this);
            $this->router->set_class_name($this->class_name);
            $this->runClass   = $this->router->fetch_class();
            $this->runMethod  = $this->router->fetch_method();

            if (isset($_SESSION['ForbizFlashData']) && !empty($_SESSION['ForbizFlashData'])) {
                $now = time();
                foreach ($_SESSION['ForbizFlashData'] as $key => $flashData) {
                    if (($now - $flashData['created']) <= $flashData['ttl']) {
                        $this->flashData[$key] = $_SESSION['ForbizFlashData'][$key]['val'];
                    } else {
                        unset($_SESSION['ForbizFlashData'][$key]);
                    }
                }
            }
        }
    }

    public function import($resource, $params = null, $return = false)
    {
        return $this->load->import($resource, $params, $return);
    }

    public function setRunMethod($runMethod)
    {
        $this->runMethod = $runMethod;

        return $this;
    }

    public function setFlashData($key, $value, $ttl = 300)
    {
        $_SESSION['ForbizFlashData'][$key] = ['val' => $value, 'created' => time(), 'ttl' => $ttl];
    }

    public function getFlashData($key)
    {
        if (isset($this->flashData[$key])) {
            $ret = $this->flashData[$key];
            unset($_SESSION['ForbizFlashData'][$key]);
        } else {
            $ret = '';
        }

        return $ret;
    }

    public function run($profiler = false)
    {
        if (method_exists($this, '_remap')) {
            $this->_remap($this->runMethod, $this->router->fetch_params());
        } elseif (method_exists($this, $this->runMethod)) {
            call_user_func_array(array($this, $this->runMethod), $this->router->fetch_params());
        } elseif ($this->class_name != 'Fobiz') {
            show_error("The page you requested was not found. ({$this->runClass}/{$this->runMethod})");
        }

        if ($profiler) {
            $this->output->enable_profiler();
        }

        $this->benchmark->mark('total_execution_time_end');
        $this->output->_display();
    }

    public function __destruct()
    {
        if (class_exists('CI_DB') AND isset($this->db)) {
            $this->db->close();
        }

        $this->load->closeAllDB();
    }
}
