<?php 

declare(strict_types = 1);

namespace Aether;

use Config\Routes;
use Aether\Exception\PageNotFoundException;
use Aether\Exception\SystemException;
use Aether\Interface\RoutingInterface;

/** 
 * Routing Class
 * 
 * @class Aether\Route
**/

class Routing implements RoutingInterface
{
    /** 
     * List of allowed methods on the framework
     * 
     * @var array $this->allowedMethods
    **/
    protected array $allowedMethods = [
        'get', 'head', 'post', 'put', 'patch', 'options', 'delete'
    ];

    //==========================================================================================

    /** 
     * Any variable so it is consistent across then controller
     * 
     * @var string $this->anyVariable
    **/
    protected string $anyVariable = '(:any)';

    //==========================================================================================

    /** 
     * Group URI prefix
     * 
     * @var string $this->groupPrefix
    **/
    protected string $groupPrefix = '';

    //==========================================================================================
    
    protected array $groupRoutes = [];

    //==========================================================================================

    /** 
     * Segment variable so it is consistent across then controller
     * 
     * @var string $this->segmentVariable
    **/
    protected string $segmentVariable = '(:segment)';

    //==========================================================================================

    /** 
     * Route collection that will be used on routing
     * 
     * @var array $routeCollection
    **/
    protected static array $routeCollection = [
        'get'     => [],
        'head'    => [],
        'post'    => [],
        'put'     => [],
        'patch'   => [],
        'options' => [],
        'delete'  => [],
        'name'    => [],
    ];

    //==========================================================================================
    
    /** 
     * Variable that will be inserted into controllers method parameter
     * 
     * @var array $this->routeVariable
    **/
    protected array $routeVariables = [];

    //==========================================================================================

    /** 
     * Route storage before being pushed into $routeCollection
     * 
     * @var array $this->stashedRoute
    **/
    protected array $stashedRoute = [
        'name'        => '',
        'rule'        => '',
        'controller'  => '',
        'method'      => '',
        'middlewares' => [
            'before' => [],
            'after'  => [],
        ],
    ];

    //==========================================================================================

    /** 
     * Route method that is being used before being pushed into $routeCollection
     * 
     * @var array $this->stashedMethods
    **/
    protected array $stashedMethods = [];

    //==========================================================================================

    /** 
     * Save all stashed previous route
     * 
     * @return void
     * 
    **/
    protected function save(): void
    {
        // push all stashed items into route collection based on methods
        foreach ($this->stashedMethods as $method):

            $method = strtolower($method);

            if (isset(self::$routeCollection[$method]))
            {
                array_push(self::$routeCollection[$method], $this->stashedRoute);
            }

        endforeach;

        // push into route collection based by name
        if (!empty($this->stashedRoute['name']))
        {
            self::$routeCollection['name'][$this->stashedRoute['name']] = $this->stashedRoute;
        }

        // make default again
        $this->stashedRoute = [
            'name'        => '',
            'rule'        => '',
            'controller'  => '',
            'method'      => '',
            'middlewares' => [
                'before' => [],
                'after'  => [],
            ],
        ];

        $this->stashedMethods = [];
    }

    //==========================================================================================

    /** 
     * Save the inputted route into all allowed HTTP Methods
     * 
     * @param string $rule
     * @param string $controller
     * @param string $controllerMethod
     * 
     * @return RoutingInterface
     * 
    **/
    public function all(string $rule, string $controller, string $controllerMethod): RoutingInterface
    {
        return $this->match('all', $rule, $controller, $controllerMethod);
    }

    //==========================================================================================

    /** 
     * Save the inputted route as an alias
     * 
     * @param string $alias
     * 
     * @return RoutingInterface
     * 
    **/
    public function as(string $alias): RoutingInterface
    {
        $this->stashedRoute['name'] = $alias;

        // return instance
        return $this;
    }

    //==========================================================================================

    /** 
     * Save the inputted route into DELETE HTTP method
     * 
     * @param string $rule
     * @param string $controller
     * @param string $controllerMethod
     * 
     * @return RoutingInterface
     * 
    **/
    public function delete(string $rule, string $controller, string $controllerMethod): RoutingInterface
    {
        return $this->match('delete', $rule, $controller, $controllerMethod);
    }

    //==========================================================================================

    /** 
     * Find the matched route based on the supplied URI
     * 
     * @param string $uri
     * 
     * @return array
     * 
    **/
    public function find(string $uri): array
    {
        // load routes config and run
        $routeConfig = new Routes();
        $routeConfig->run($this);

        // save all config
        $this->save();

        // only parse uri before uri parameters
        $requestURI = str_contains($uri, '?') ? strstr($uri, '?', true) : $uri;
        $requestURI = explode('/', $requestURI);
        $requestURI = array_values(array_filter($requestURI, 'sanitizeURI'));

        // mutate ori and replace using config
        // disable this feature for now
        // $uri = preg_replace('/[^'. $appConfig->permittedURIChars .']+/iu', '-', $uri);

        // check routes
        $usedMethod      = strtolower($_SERVER['REQUEST_METHOD']);
        $routeMatchedKey = false;

        foreach (self::$routeCollection[$usedMethod] as $i => $route):

            // break into segments
            $ruleURI = explode('/', $route['rule']);
            $ruleURI = array_values(array_filter($ruleURI, 'sanitizeURI'));

            // count total segment
            $totalRuleURI = count($ruleURI);

            // count total request uri
            $totalRequestURI = count($requestURI);

            // if count request URI lower than
            // count total rule URI then continue as it won't match
            if ($totalRequestURI < $totalRuleURI)
            {
                continue;
            }

            // mutate rules (:segment) and (:any)
            foreach ($ruleURI as $n => $item):

                // mutate segment with the same $n from request URI
                if ($item === $this->segmentVariable)
                {
                    $ruleURI[$n] = $requestURI[$n];

                    // push into route variable
                    array_push($this->routeVariables, $requestURI[$n]);
                }

                // mutate any with all of the other $n from request URI
                if ($item === $this->anyVariable)
                {
                    $ruleURI[$n] = $requestURI[$n];
                    array_push($this->routeVariables, $requestURI[$n]);

                    if ($totalRequestURI > $totalRuleURI)
                    {
                        $keysLeft = range(($n + 1), ($totalRequestURI - 1));
                        
                        foreach ($keysLeft as $key):

                            array_push($ruleURI, $requestURI[$key]);
                            array_push($this->routeVariables, $requestURI[$key]);

                        endforeach;
                    }
                }

            endforeach;

            // build full URI
            $ruleURIFull    = implode('/', $ruleURI);
            $requestURIFull = implode('/', $requestURI);

            // check if matched
            if ($ruleURIFull === $requestURIFull)
            {
                
                $routeMatchedKey = $i;
                break;   
            }

        endforeach;

        // if there is no route matched
        if ($routeMatchedKey === false)
        {
            $message = (AETHER_ENV === 'development') ? 'Route is not found in route configuration.' : 'Page not found.';
            throw new PageNotFoundException($message);
        }
        
        // return with array
        return [
            'uri'   => ($requestURIFull === '') ? '/' : $requestURIFull,
            'data'  => self::$routeCollection[$usedMethod][$routeMatchedKey],
            'param' => $this->routeVariables,
        ];
    }

    //==========================================================================================

    /** 
     * Find the matched route based on the alias of supplied route
     * 
     * @param string $alias
     * 
     * @return array
     * 
    **/
    public function findByAlias(string $alias, array $params = []): array
    {
        // early return if not exist
        if (!isset(self::$routeCollection['name'][$alias]))
        {
            return [];
        }

        // create route variable
        $route = self::$routeCollection['name'][$alias];

        // create full URI using params
        // if not empty
        if (!empty($params))
        {
            $uriSegments = explode('/', $route['rule']);

            foreach ($uriSegments as $n => $segment):

                if ($segment === $this->segmentVariable)
                {
                    $uriSegments[$n] = $params[0];
                    array_shift($params);

                } elseif ($segment === $this->anyVariable) {

                    $uriSegments[$n] = implode('/', $params);

                }

            endforeach;

        } else {

            $uriSegments = explode('/', $route['rule']);

        }

        // return with array
        return [
            'uri'   => implode('/', $uriSegments),
            'data'  => $route,
            'param' => $params,
        ];
    }

    //==========================================================================================

    /** 
     * Save the inputted route into GET HTTP method
     * 
     * @param string $rule
     * @param string $controller
     * @param string $controllerMethod
     * 
     * @return RoutingInterface
     * 
    **/
    public function get(string $rule, string $controller, string $controllerMethod): RoutingInterface
    {
        return $this->match('get', $rule, $controller, $controllerMethod);
    }

    //==========================================================================================

    /** 
     * Get all registered route
     * 
     * @return array self::$routeCollection
     * 
    **/
    public function getAllRoutes(): array
    {
        return self::$routeCollection;
    }

    //==========================================================================================

    /** 
     * Grouping routes
     * 
     * @param string $prefix
     * @param callable $callback
     * @param array $middlewares ['before' => [], 'after' => []]
     * 
     * @return RoutingInterface $this
     * 
    **/
    public function group(string $prefix, callable $callback, array $middlewares = ['before' => [], 'after' => []]): RoutingInterface
    {
        // check if there are any stashed route
        // and store it
        if (!empty($this->stashedMethods))
        {
            $this->save();
        }

        // add slash or not
        $prefix = (empty($this->groupPrefix) || (substr($prefix, 0, 1) !== '/')) ? $prefix : "/{$prefix}";

        // add prefix
        $this->groupPrefix .= $prefix;

        // see collection
        $oldCollection = self::$routeCollection;
        $httpMethods   = array_keys($oldCollection);

        // call
        $callback($this);

        // save into collections
        if (!empty($this->stashedMethods))
        {
            $this->save();
        }

        // new collection
        $newCollection = self::$routeCollection;
        $ruleKeys      = [];
        $divider       = '##DIVIDER##';

        // get the diff
        foreach ($httpMethods as $method):

            $oldTotal = count($oldCollection[$method]);
            $newTotal = count($newCollection[$method]);

            if ($newTotal > $oldTotal)
            {
                if ($method === 'name')
                {
                    // get the keys
                    $oldKey = array_keys($oldCollection[$method]);
                    $newKey = array_keys($newCollection[$method]);
                    $diffs  = array_diff($newKey, $oldKey);
                    
                    foreach ($diffs as $key):

                        array_push($ruleKeys, "{$method}{$divider}{$key}");

                    endforeach;

                } else {

                    $startKey = $oldTotal;
                    $endKey   = $newTotal - 1;

                    foreach (range($startKey, $endKey) as $i)
                    {
                        array_push($ruleKeys, "{$method}{$divider}{$i}");
                    }
                }
            }

        endforeach;

        // set error message
        $message = (AETHER_ENV === 'development') ? "The supplied middlewares data type should be in array" : "Failed to fetch routes";

        // register middleware before
        if (isset($middlewares['before']) && !empty($middlewares['before']) && !empty($ruleKeys))
        { 
            if (!is_array($middlewares['before']))
            {
                throw new SystemException($message, 500);
            }

            foreach ($middlewares['before'] as $middleware):

                if (!empty($middleware))
                {
                    foreach ($ruleKeys as $key)
                    {
                        $keyArray = explode($divider, $key);
                        $method   = $keyArray[0];
                        $i        = $keyArray[1];

                        // push middleware
                        array_push(self::$routeCollection[$method][$i]['middlewares']['before'], $middleware);
                    }
                }

            endforeach;            
        }

        // register middleware after
        if (isset($middlewares['after']) && !empty($middlewares['after']) && !empty($ruleKeys))
        {      
            if (!is_array($middlewares['after']))
            {
                throw new SystemException($message, 500);
            }

            foreach ($middlewares['after'] as $middleware):

                if (!empty($middleware))
                {
                    foreach ($ruleKeys as $key)
                    {
                        $keyArray = explode($divider, $key);
                        $method   = $keyArray[0];
                        $i        = $keyArray[1];

                        // push middleware
                        array_push(self::$routeCollection[$method][$i]['middlewares']['after'], $middleware);
                    }
                }

            endforeach;
        }

        // remove only supplied prefix
        // so it support multiple nested group
        $storedPrefixCount   = strlen($this->groupPrefix);
        $suppliedPrefixCount = strlen($prefix);
        $this->groupPrefix   = substr($this->groupPrefix, 0, ($storedPrefixCount - $suppliedPrefixCount));

        // return
        return $this;
    }

    //==========================================================================================

    /** 
     * Save the inputted route into HEAD HTTP method
     * 
     * @param string $rule
     * @param string $controller
     * @param string $controllerMethod
     * 
     * @return RoutingInterface
     * 
    **/
    public function head(string $rule, string $controller, string $controllerMethod): RoutingInterface
    {
        return $this->match('head', $rule, $controller, $controllerMethod);
    }

    //==========================================================================================

    /** 
     * Save the inputted route into based on the selected HTTP method
     * 
     * @param 'get'|'head'|'post'|'put'|'patch'|'options'|'delete' $httpMethod
     * @param string $rule
     * @param string $controller
     * @param string $controllerMethod
     * 
     * @return RoutingInterface
     * 
    **/
    public function match(array|string $httpMethod, string $rule, string $controller, string $controllerMethod): RoutingInterface
    {
        // mutate rule if group is not empty
        if (!empty($this->groupPrefix))
        {
            $rule = (substr($rule, 0, 1) !== '/') ? "{$this->groupPrefix}/{$rule}" : $this->groupPrefix . $rule;
        }

        // check if there are any stashed route
        // and store it
        if (!empty($this->stashedMethods))
        {
            $this->save();
        }

        // sanitize http method
        if (is_array($httpMethod))
        {
            foreach ($httpMethod as $n => $method):

                if (!in_array(strtolower($method), $this->allowedMethods))
                {
                    unset($httpMethod[$n]);
                }

            endforeach;

        } else {

            if (strtolower($httpMethod) === 'all')
            {
                // input all available method
                $httpMethod = $this->allowedMethods;

            } else {

                if (!in_array($httpMethod, $this->allowedMethods))
                {
                    // don't process this routes
                    // as the http method is invalid or not allowed
                    return $this;
                }
            }

        }

        // replace (:any) with ?ANY?
        $posString = stripos($rule, $this->anyVariable);

        if ($posString !== false)
        {
            //$rule = strstr($rule, $anyString, true);
            $rule = substr($rule, 0, $posString);
            $rule = $rule . $this->anyVariable;
        }

        // replace (:segment) with ?SEGMENT?
        if (str_contains($rule, $this->segmentVariable) !== false)
        {
            $rule = str_ireplace($this->segmentVariable, $this->segmentVariable, $rule);
        }

        // move the stashed rule, controller, and controller method
        // into stashed route which then moved into route collection
        $this->stashedRoute['rule']       = $rule;
        $this->stashedRoute['controller'] = '\\' . $controller;
        $this->stashedRoute['method']     = $controllerMethod;

        // check methods and stash it
        $this->stashedMethods = is_array($httpMethod) ? $httpMethod : [ $httpMethod ];

        // return instance
        return $this;
    }

    //==========================================================================================

    /** 
     * Save the used middleware into the selected route
     * 
     * @param string $middlewares
     * @param string $executionTime 'before' | 'after'
     * 
     * @return RoutingInterface
     * 
    **/
    public function middlewares(string|array $middlewares, string $executionTime = 'before'): RoutingInterface
    {
        if (is_array($middlewares))
        {
            foreach ($middlewares as $middleware):

                array_push($this->stashedRoute['middlewares'][$executionTime], $middleware);

            endforeach;

        } else {

            array_push($this->stashedRoute['middlewares'][$executionTime], $middlewares);

        }

        // return instance
        return $this;
    }

    //==========================================================================================

    /** 
     * Save the inputted route into OPTIONS HTTP method
     * 
     * @param string $rule
     * @param string $controller
     * @param string $controllerMethod
     * 
     * @return RoutingInterface
     * 
    **/
    public function options(string $rule, string $controller, string $controllerMethod): RoutingInterface
    {
        return $this->match('options', $rule, $controller, $controllerMethod);
    }

    //==========================================================================================

    /** 
     * Save the inputted route into PATCH HTTP method
     * 
     * @param string $rule
     * @param string $controller
     * @param string $controllerMethod
     * 
     * @return RoutingInterface
     * 
    **/
    public function patch(string $rule, string $controller, string $controllerMethod): RoutingInterface
    {
        return $this->match('patch', $rule, $controller, $controllerMethod);
    }

    //==========================================================================================

    /** 
     * Save the inputted route into POST HTTP method
     * 
     * @param string $rule
     * @param string $controller
     * @param string $controllerMethod
     * 
     * @return RoutingInterface
     * 
    **/
    public function post(string $rule, string $controller, string $controllerMethod): RoutingInterface
    {
        return $this->match('post', $rule, $controller, $controllerMethod);
    }

    //==========================================================================================

    /** 
     * Save the inputted route into PUT HTTP method
     * 
     * @param string $rule
     * @param string $controller
     * @param string $controllerMethod
     * 
     * @return RoutingInterface
     * 
    **/
    public function put(string $rule, string $controller, string $controllerMethod): RoutingInterface
    {
        return $this->match('put', $rule, $controller, $controllerMethod);
    }

    //==========================================================================================
}