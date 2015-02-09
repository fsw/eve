<?php

abstract class Action_EntityForm extends Action_Form
{

    protected static $entityClass = 'None';

    /** @Field_Honeypot() */
    public $honeypot;

    public function run()
    {
        if ($this->id != 0) {
            $this->entity = call_user_func([static::$entityClass, 'getById'], $this->id);
        } else {
            $this->entity = new static::$entityClass();
        }
        parent::run();
    }

    protected function getFields()
    {
        $method = new ReflectionMethod(static::$entityClass, 'getFields');
        $method->setAccessible(true);
        $this->entityFields = $method->invoke(null);
        return parent::getFields();
    }

    protected function postSave()
    {
        // post successful save of entity
    }

    protected function renderField($key)
    {
        if (isset($this->entityFields[$key])) {
            return $this->renderFieldClass($this->entityFields[$key], $this->entity->$key);
        } else {
            return parent::renderField($key);
        }
    }

    protected function success()
    {
        try {
            $this->id = $this->entity->save();
            $this->postSave();
            parent::success();
        } catch (Entity_Exception $e) {
            $this->errors = $e->errors;
        }
    }

    protected function post($post)
    {
        foreach ($this->entityFields as $key => $field) {
            $this->entity->$key = $field->updateWithPost($this->entity->$key, $post);
            $ret = $field->validate($this->entity->$key);
            if (! empty($ret)) {
                $this->errors[$key] = $ret;
            }
        }
        parent::post($post);
    }
}
