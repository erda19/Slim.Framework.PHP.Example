<?php 

namespace ApiSlimFramework\Middleware;

class AuthorizationMiddleware
{
    /**
     * Example middleware invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */

    private $resourceServer;

    function __construct($resourceServer)
    {
        $this->resourceServer = $resourceServer;
    }
    public function __invoke($request, $response, $next)
    {
        //$response->getBody()->write('BEFORE');
        //return $response;
        //$response = $next($request, $response);
        //$response->getBody()->write('AFTER');
        //return $response;
        try {
            // Try to respond to the request
            $auth = $this->resourceServer->validateAuthenticatedRequest($request);
            //return $response->write($data);
            $response = $next($request, $response);
            return $response;
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
        } catch (\Exception $exception) {
            $response->write($exception->getCode());
            return $response;
        }

    }
}

?>