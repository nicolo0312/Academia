<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

session_start();

if (PHP_SAPI == 'cli-server') {
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) return false;
}

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader, [
    'cache' => false,
]);

$mongoconn = new \MongoDB\Client("mongodb://localhost");
$userService = new \Tuiter\Services\UserService($mongoconn->tuiter->users);
$postService = new \Tuiter\Services\PostService($mongoconn->tuiter->posts);
$likeService = new \Tuiter\Services\LikeService($mongoconn->tuiter->likes);
$followService = new \Tuiter\Services\FollowService($mongoconn->tuiter->follows, $userService);
$loginService = new \Tuiter\Services\LoginService($userService);


$app = AppFactory::create();

$app->get('/', function (Request $request, Response $response, array $args) use ($twig) {
    
    $template = $twig->load('index.html');

    $response->getBody()->write(
        $template->render()
    );
    return $response;
});
// $app->get('/Registrarse', function (Request $request, Response $response, array $args) use ($twig) {
    
//     $template = $twig->load('index.html');

//     $response->getBody()->write(
//         $template->render()
//     );
//     return $response;
// });
$app->post('/Registrarse', function (Request $request, Response $response, array $args) use ($userService) {
    if ($userService->register($_POST["userId"], $_POST["name"], $_POST["password"])==True){
        $response=$response->withStatus(302);
        $response=$response->withHeader("location","/");
    }
     else{
        $response=$response->withStatus(302);
        $response=$response->withHeader("location","Registrarse");
     }

    return $response;
});

$app->get('/contacto', function (Request $request, Response $response, array $args) use ($twig) {
    
    $template = $twig->load('contacto.html');

    $response->getBody()->write(
        $template->render()
    );
    return $response;
});

$app->post('/Logearse', function (Request $request, Response $response, array $args) use ($loginService) {
    $user=$loginService->login($_POST["userId"], $_POST["password"]);

        if (!$user instanceof \Tuiter\Models\UserNull){
            $response=$response->withStatus(302);
            $response=$response->withHeader("location", "/user/me");
        }else{
            $response=$response->withStatus(302);
            $response=$response->withHeader("location", "/");
        }

    

    return $response;
});
$app->get('/user/me', function (Request $request, Response $response, array $args) use ($twig) {
    
    $template = $twig->load('index.html');

    $response->getBody()->write(
        $template->render()
    );
    return $response;
});


$app->run();