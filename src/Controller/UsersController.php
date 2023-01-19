<?php   
 namespace App\Controller;

    use App\Controller\AppController;
    use Cake\Routing\Router;
    use Cake\Mailer\Email;
    use Authentication\IdentityInterface;
    use Cake\Controller\Component\AuthComponent;
    use Cake\Event\Event;
    use Cake\Http\Response;
  




class UsersController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        // Add the 'add' action to the allowed actions list.
        $this->Auth->allow(['logout', 'register','login','verify','index']);
        $this->loadComponent('Auth', [
            'authenticate' => [
                'Form' => [
                    'fields' => ['username' => 'email'],
                    'userModel' => 'Users'
                ]
            ],
        'loginAction' => [
            'controller' => 'Users',
            'action' => 'login'
        ],
       
        'logoutRedirect' => [
            'controller' => 'Users',
            'action' => 'login'
        ],
       
        'authorize' => 'Controller',
        ]);
    }
    

    // public function initialize()
    // {
    //     parent::initialize();
    //     $this->loadComponent('Auth', [
    //         'authenticate' => [
    //             'Form' => [
    //                 'fields' => ['username' => 'email'],
    //                 'userModel' => 'Users'
    //             ]
    //         ],
    // 'loginAction' => [
    //     'controller' => 'Users',
    //     'action' => 'login'
    // ],
    // 'loginRedirect' => [
    //     'controller' => 'Pages',
    //     'action' => 'home'
    //  ],
    // 'logoutRedirect' => [
    //     'controller' => 'Users',
    //     'action' => 'login'
    // ],
    // 'unauthorizedRedirect' => [
    //     'controller' => 'Users',
    //     'action' => 'login',
    //     'prefix' => false
    // ],
    // 'authorize' => 'Controller',
    //     ]);
    // }




    public function register()
    {
        $user = $this->Users->newEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                // Generate a unique token for email verification
                $user->token = sha1(uniqid(mt_rand(), true));
                // Set the user's status to "inactive" until email is verified
                $user->status = 'inactive';
                if ($this->Users->save($user)) {
                    // Send email verification link to user
                    $email = new Email();
                    $email->to($user->email)
                        ->subject('Email verification')
                        ->send('Please click the following link to verify your email: ' . Router::url(['controller' => 'users', 'action' => 'verify', $user->token], true));
                    $this->Flash->success(__('Your account has been created. Please check your email for verification.'));
                    return $this->redirect(['action' => 'login']);
                }
            }
            $this->Flash->error(__('Unable to register.'));
        }
        $this->set(compact('user'));
    }

    public function verify($token)
    {
        $user = $this->Users->findByToken($token)->first();
        if ($user) {
            // Update user's status to "active"
            $user->status = 'active';
            $user->token = null;
            if ($this->Users->save($user)) {
                $this->Flash->success(__('Your email has been verified. Please login.'));
                return $this->redirect(['action' => 'login']);
            }
        } else {
            $this->Flash->error(__('Invalid token.'));
        }
        return $this->redirect(['action' => 'register']);
    }

    public function login()
{
    if ($this->request->is('post')) {
        $user = $this->Auth->identify();
        if ($user) {
            if ($user['status'] == 'active') {
                $this->Auth->setUser($user);
                return $this->redirect($this->Auth->redirectUrl());
            } else {
                $this->Flash->error(__('Your email is not verified yet.'));
            }
        } else {
            $this->Flash->error(__('Invalid username or password, try again'));
        }
    }
 
}
    

    

    public function logout()
    {
        $this->Flash->success('You are now logged out.');
        return $this->redirect($this->Auth->logout());

    }


    public function index()
    {

    }
}

