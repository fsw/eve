<?php

/* Frontend_Index */
class __TwigTemplate_b612fa5e0a2072f35224f37777d2b462f6d441da8d9e493b0453cf0ff60e11be extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->blocks = array(
            'content' => array($this, 'block_content'),
        );
    }

    protected function doGetParent(array $context)
    {
        // line 1
        return $this->env->resolveTemplate((isset($context["Frontend"]) ? $context["Frontend"] : null));
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->getParent($context)->display($context, array_merge($this->blocks, $blocks));
    }

    // line 2
    public function block_content($context, array $blocks = array())
    {
        // line 3
        echo "    <h3>Table Of Contents</h3>
    BODY BODY BODY
";
    }

    public function getTemplateName()
    {
        return "Frontend_Index";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  30 => 3,  27 => 2,  18 => 1,);
    }
}
