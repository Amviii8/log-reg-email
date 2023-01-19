<?= $this->Form->create($user) ?>
<fieldset>
    <legend><?= __('Register') ?></legend>
    <?= $this->Form->control('username', ['required' => true]) ?>
    <?= $this->Form->control('email', ['required' => true]) ?>
    <?= $this->Form->control('password', ['required' => true]) ?>
</fieldset>
<?= $this->Form->button(__('Register')) ?>
<?= $this->Form->end() ?>