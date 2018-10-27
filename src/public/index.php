<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//oauth2
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Middleware\AuthorizationServerMiddleware;
use League\OAuth2\Server\Middleware\ResourceServerMiddleware;
use League\OAuth2\Server\ResourceServer;
use ApiSlimFramework\Repositories\AccessTokenRepository;
use ApiSlimFramework\Repositories\AuthCodeRepository;
use ApiSlimFramework\Repositories\ClientRepository;
use ApiSlimFramework\Repositories\RefreshTokenRepository;
use ApiSlimFramework\Repositories\ScopeRepository;

use ApiSlimFramework\Utility;
use ApiSlimFramework\Utility\Helper;

require '../vendor/autoload.php';

$app = new \Slim\App([
    'settings'                 => [
        'displayErrorDetails' => true,
    ]
]);

$container = $app->getContainer();

//Add Dependency Injection
$container['AuthorizationServer'] = function($c)
{
    // Init our repositories
    $clientRepository = new ClientRepository();
    $accessTokenRepository = new AccessTokenRepository();
    $scopeRepository = new ScopeRepository();
    $authCodeRepository = new AuthCodeRepository();
    $refreshTokenRepository = new RefreshTokenRepository();
    $privateKeyPath = 'file://' . __DIR__ . '/../private.key';

    // Setup the authorization server
    $AuthorizationServer = new AuthorizationServer(
        $clientRepository,
        $accessTokenRepository,
        $scopeRepository,
        $privateKeyPath,
        'lxZFUEsBCJ2Yb14IF2ygAHI5N4+ZAUXXaSeeJm6+twsUmIen'
    );
    $AuthorizationServer->enableGrantType(
        new \League\OAuth2\Server\Grant\ClientCredentialsGrant(),
        new \DateInterval('PT1H') // access tokens will expire after 1 hour
    );
    return $AuthorizationServer;
};

$container['ResourceServer'] = function($c)
{
    $publicKeyPath = 'file://' . __DIR__ . '/../public.key';
    $ResourceServer = new ResourceServer(
        new AccessTokenRepository(),
        $publicKeyPath
    );
    return $ResourceServer;
};

$container['Helper'] = function($c) {
    return new Helper();
};


//API GET AUTHENTICATION AND AUTHORIZATION
$app->group('/api/auth', function () {

    //API Get Token
    $this->post('/access_token', function (Request $request, Response $response) {
        $data = $request->getParsedBody();
        $data['grant_type']='client_credentials';
        $request = $request->withParsedBody($data);
        $data = $request->getParsedBody();
        $server = $this->AuthorizationServer;
        try {
            // Try to respond to the request
            return $server->respondToAccessTokenRequest($request, $response);
        } catch (OAuthServerException $exception) {
            // All instances of OAuthServerException can be formatted into a HTTP response
            return $exception->generateHttpResponse($response);
        } catch (\Exception $exception) {
            // Unknown exception
            //$body = new Stream('php://temp', 'r+');
            $response->write($exception->getMessage());
            return $response;
        }
    });
    
});


//API RESOURCE Need Authorization
$app->group('/api', function () {

    $this->get('/hello/{name}', function (Request $request, Response $response, array $args) {
        $name = $args['name'];   
        $data = array('name' => $this->Helper->Hello());
        $response->withJson($data);
        return $response;
    });

    $this->get('/view_data', function (Request $request, Response $response, array $args) {
        //$name = $args['name'];   
        $data = array('name' => $this->Helper->Hello());
        $response->withJson($data);
        return $response;
    });
    
})->add( new ApiSlimFramework\Middleware\AuthorizationMiddleware($app->getContainer()->get('ResourceServer')));

$app->run();

?>