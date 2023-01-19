<h1>Email Verification</h1>
<p>Your email has been verified. Please click the following link to login:</p>
<p><?= $this->Html->link('Login', ['controller' => 'users', 'action' => 'login']) ?>
</p>
<?=$this->redirect(['controller' => 'users', 'action' => 'login']);
