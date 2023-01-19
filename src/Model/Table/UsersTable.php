<?php

namespace App\Model\Table;

use App\Model\Entity\User;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Mailer\MailerAwareTrait;
use Cake\Core\Exception\Exception;

/**
 * Users Model
 *
 * @property \App\Model\Table\ArticlesTable&\Cake\ORM\Association\HasMany $Articles
 *
 * @method \App\Model\Entity\User get($primaryKey, $options = [])
 * @method \App\Model\Entity\User newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\User[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\User findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UsersTable extends Table
{

    use MailerAwareTrait;

    /**
     * Initialize method
     *
     * @param array <mixed> $config array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany(
            'Articles',
            [
                'foreignKey' => 'user_id',
            ]
        );
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('username')
            ->maxLength('username', 255)
            ->requirePresence('username', 'create')
            ->notEmptyString('username');


        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmptyString('email');

        $validator
            ->scalar('password')
            ->maxLength('password', 255)
            ->requirePresence('password', 'create')
            ->notEmptyString('password');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->isUnique(['email']));

        return $rules;
    }
    /***
     *  find user by first name
     *
     * @return \Cake\Datasource\EntityInterface|null
     */
    public function findUserByName(string $username): ?\Cake\Datasource\EntityInterface
    {
        return $this->find('all', [
            'conditions' => [
                'Users.first_name' => $username,
            ],
        ])->first();
    }

    public function findAuth(\Cake\ORM\Query $query, array $options): ?\Cake\ORM\Query
    {
        return $query->find('all');
    }

    /**
     * register user
     *
     * @param array<mixed> $data
     * @return \App\Model\Entity\User|false $user
     * */
    public function registerUser(array $data, bool $notify = false): ?User
    {

        //logic for token
        $user = $this->newEntity();
        $user = $this->patchEntity($user, $data);

        $token = sha1(rand(0, 100) . time());
        $user->token = $token;
        $user->token_expires = date("Y-m-d H:i:s", time() + 3600);



        $user = $this->save($user);

        if ($user) {
            //send email  to user

            $email = $user->email;
            $name = $user->first_name . ' ' . $user->last_name;

            $dataEmail = [
                'email' => $email,
                'name' => $name,
                'token' => $token,
                'expires' => date("Y-m-d H:i:s", time() + 3600),
            ];

            if (!$notify) {
                return $user;
            }
            try {
                $this->getMailer('Usersmailer')->send('welcome', [$dataEmail]);
            } catch (\Exception $th) {
                throw new Exception("Error Processing Request", 1);
            }
        }

        return $user;
    }
    }