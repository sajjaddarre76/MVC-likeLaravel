<?php

namespace thecodeholic\phpmvc;


use thecodeholic\phpmvc\db\Database;
use thecodeholic\phpmvc\db\DbModel;

class Application
{
    public static string $ROOT_DIR;

    public string $userClass;

    public string $layout = 'main';
    public Session $session;
    public Router $router;
    public Request $request;
    public Response $response;
    public Database $db;
    public ?UserModel $user;
    public View $view;

    public static Application $app;
    public ?Controller $controller = null;
    public function __construct($rootPath, array $config){
        $this->userClass = $config['userClass'];
        self::$ROOT_DIR = $rootPath;
        self::$app= $this;
       $this->session = new Session();
       $this->request = new Request();
       $this->response = new Response();
       $this->router = new Router($this->request,$this->response);
       $this->view = new View();

       $this->db = new Database($config['db']);

        $primaryValue = $this->session->get('user');
        if($primaryValue){

            $primaryKey = $this->userClass::primaryKey();
            $this->user = $this->userClass::findOne([$primaryKey => $primaryValue]);

        }else{
            $this->user = null;
        }

    }

    public function run(){
        try{

        echo $this->router->resolve();
        }catch (\Exception $e){
            $this->response->setStatusCode($e->getCode());
            echo $this->view->renderView('_error', [
                'exception' => $e
            ]);
        }
    }

    public function getController(): Controller{
        return $this->controller;
    }

    public function setController(Controller $controller){
         $this->controller = $controller;
    }

    public function login(UserModel $user){
        $this->user = $user;
        $primaryKey = $user->primaryKey();
        $primaryValue = $user->{$primaryKey};
        $this->session->set('user', $primaryValue);
        return true;
    }

    public function logout(){
        $this->user = null;
        $this->session->remove('user');
    }

    public static function isGuest(){
        return !self::$app->user;
    }

}