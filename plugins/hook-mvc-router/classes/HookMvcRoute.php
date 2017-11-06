<?php
/**
 * Created by PhpStorm.
 * User: kuni
 * Date: 2017/11/04
 * Time: 10:33
 */

/**
 * Class HookMvcRoute
 */
class HookMvcRoute
{
    /**
     * @var string
     * Example GET|POST
     */
    public $method    = '';
    /**
     * @var string
     * Example user/detail/:user_id
     */
    public $path      = '';
    /**
     * Controller/Action
     * @var string
     * Example User::Detail
     */
    public $controller_action = '';

    /**
     * HookMvcRoute constructor.
     * @param $_method
     * @param $_path
     * @param $_controller_action
     */
    function __construct( $_method, $_path, $_controller_action ) {
        $this->method = $_method;
        $this->path = $_path;
        $this->controller_action = $_controller_action;
    }

    public function getActionType() {
        return
//            str_replace( array( '|' ), "_", $this->method)
            $this->method
            . '--' .
            str_replace( array( '::' ), "-", $this->controller_action);
    }

    /**
     * @return array
     */
    public function getPregPathObj() {
        $param_keys = array();

        $path_arr = explode( '/', $this->path);
        foreach ( $path_arr as &$p ){
            if( preg_match( '/^:.+$/', $p, $matches, PREG_OFFSET_CAPTURE) ) {
                $param_keys[] = preg_replace('/^:(.+)$/', '$1', $p);
                $p = preg_replace('/^:(.+)$/', '(?<$1>[^\/]+)', $p);
            }
        }
        return array(
//            'preg'  => '/^' . join( '\/', $path_arr) . '\/?$/',
            'preg'  => '^' . join( '\/', $path_arr),
            'param_keys' => $param_keys,
        );
    }
}