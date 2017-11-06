<?php
/**
 * Created by PhpStorm.
 * User: kuni
 * Date: 2017/11/04
 * Time: 10:33
 */

require_once __DIR__ . '/HookMvcRoute.php';

/**
 * Class HookMvcRouter
 */
class HookMvcRouter {
    const PLUGIN_NAME   = 'hmvcr';
    const ROUTER_ACTION = 'hmvcr_action';
    const ROUTER_PARAM  = 'hmvcr_param_';
    const PARAMS_COUNT  = 10;

    /**
     * @var HookMvcRoute[]
     */
    private $routes = array();

    /**
     * HookMvcRouter constructor.
     */
    function __construct() {
        add_action( 'init', function() {

            // ** Add Routes Filter
            apply_filters( self::PLUGIN_NAME . '/add_routes', $this );

            foreach ( $this->routes as $route ) {
                add_action( $route->getActionType(), function() {
                    global $wp_query;

                    $router_action = $wp_query->query_vars[ self::ROUTER_ACTION ];
                    $router_params = array();
                    for ($i = 1; $i <= self::PARAMS_COUNT;$i++ ) {
                        if( $wp_query->query_vars[ self::ROUTER_PARAM . $i ] ) {
                            $router_params[] = $wp_query->query_vars[ self::ROUTER_PARAM . $i ];
                        } else {
                            break;
                        }
                    }
                    $action = self::parseAction($router_action);

//                    // ** Check method
//                    if( $_SERVER["REQUEST_METHOD"] == "POST" && array_search('POST', $action['methods']) !== false ){
//                    } else if( array_search('GET', $action['methods']) !== false ) {
//                    } else {
//                    }

                    try {
                        $classPath = get_template_directory() . '/controller/' . $action['controller'] . '.php';
                        if( ! file_exists( $classPath ) ) {



                            // TODO:fileないっす


                        }
                        require_once $classPath;
                        $controller = new $action['controller'];
                        $action_name = $action['action'] . 'Action';

                        // ** Exec Controller/Action
                        $controller->$action_name($router_params);

                    } catch (Exception $ex) {
                       self::redirect404();
                    }
                    exit;
                });
            }

            // ** Add Rewrite Rules
            foreach( $this->routes as $route ) {
                $preg_obj = $route->getPregPathObj();
                $param_keys = $preg_obj['param_keys'];
                $param_keys_count = count($param_keys);

                $get_arr = array();
                $get_arr[] = self::ROUTER_ACTION . '=' . $route->getActionType();

                for( $i = 1; $i <= $param_keys_count;$i++ ) {
                    $get_arr[] = self::ROUTER_PARAM . $i . '=' . '$matches[' . $i . ']';
                }
                $get_arr[] = '$matches[' . ( $param_keys_count + 1 ) . ']';

                $regex = $preg_obj['preg'] . '\/?(\?=.*)?$';

                add_rewrite_rule( $regex, 'index.php?' . join( '&', $get_arr), 'top' );
            }
            flush_rewrite_rules();

            // **
            add_filter( 'query_vars', function( $query_vars ) {
                $query_vars[] = self::ROUTER_ACTION;
                for ($i = 1; $i <= self::PARAMS_COUNT;$i++ ) {
                    $query_vars[] = self::ROUTER_PARAM . $i;
                }
                return $query_vars;
            });

            // **
            add_action( 'template_redirect', function() {
                global $wp_query;
                $action_type = isset ( $wp_query->query_vars[ self::ROUTER_ACTION ] ) ? $wp_query->query_vars[ self::ROUTER_ACTION ] : '';
                do_action( $action_type );
            });

        });
    }

    /**
     * @param $path
     * @param $ca
     * @param string $method
     * @return $this
     */
    public function addRoute( $path, $ca, $method = 'GET' ) {
        $method = strtoupper($method);
        if( ! preg_match( '/^GET$|^POST$|^GET\|POST$|^POST\|GET$/', $method, $matches, PREG_OFFSET_CAPTURE ) ) {
            return $this;
        }
        $this->routes[] = new HookMvcRoute( $method, $path, $ca );

        return $this;
    }

    private static function parseAction($router_action) {
        $arr = explode( '--', $router_action);
        $ca = explode( '-', $arr[1] );
        $methods = explode( '|', $arr[0]);

        return array(
            'methods'        => $methods,
            'controller'    => $ca[0],
            'action'        => $ca[1],
        );
    }

    private static function redirect404() {
        wp_redirect( home_url( '/404' ) );
        ////                        wp_redirect( home_url( '/404' ) );
////            wp_redirect( home_url(), 404 );
    }

}