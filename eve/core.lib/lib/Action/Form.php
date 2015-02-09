<?php

abstract class Action_Form extends Frontend
{

    protected $success = false;

    protected $errors = [];

    protected $fields = [];

    public function run()
    {
        $this->success = (! empty($_GET['success'])) && ($_GET['success'] == 'true');
        $this->fields = $this->getFields();
        parent::run();
    }

    protected function getScriptsUrls()
    {
        $ret = parent::getScriptsUrls();
        $ret[] = '/static/js/fields.js';
        return $ret;
    }

    protected function getFields()
    {
        $ret = array();
        foreach (Eve::getFieldsAnnotations($this) as $field => $annotations) {
            foreach ($annotations as $annotation) {
                if ($annotation instanceof Field) {
                    $annotation->name = $field;
                    $annotation->entity = get_called_class();
                    $ret[$field] = $annotation;
                }
            }
        }
        return $ret;
    }

    protected function renderFieldClass($field, $value)
    {
        if ($field->hasFormInput()) :
            ?>
<div
	class="form-group<?php if($field->isRequired()) echo ' required'; ?><?php if(!empty($this->errors[$field->name])) echo ' has-error'; ?>">
	<label class="col-md-3 control-label" for="textinput"><?php echo $field->getVerboseName(); ?></label>
	<div class="col-md-9">
                    <?php echo $field->getFormInput($value)?>
                    <span class="help-block"><?php echo $field->getHelpText(); ?></span>
                    <?php if(!empty($this->errors[$field->name])): ?>
                        <?php foreach ($this->errors[$field->name] as $error): ?>
                            <div class="alert alert-danger" role="alert">
			<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
			<span class="sr-only">Error:</span>
                                <?php echo $error; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
</div>



        <?php endif;
    }

    protected function renderField($key)
    {
        $this->renderFieldClass($this->fields[$key], $this->$key);
    }

    protected function post($post)
    {
        foreach ($this->fields as $key => $field) {
            $this->$key = $field->updateWithPost($this->$key, $post);
            $ret = $field->validate($this->$key);
            if (! empty($ret)) {
                $this->errors[$key] = $ret;
            }
        }
        if (empty($this->errors)) {
            $this->success();
        }
    }

    protected function success()
    {
        $this->redirectTo('?success=true&id=' . $newId);
    }

    protected function sectionContent()
    {
        
        // TODO default form body?
    }
}
