<?php

namespace Maba\Bundle\TwigTemplateModificationBundle\Entity;

class TemplateContext
{

    protected $templateName;

    protected $attributes = array();
    protected $notices = array();

    /**
     * @return string
     */
    public function getTemplateName()
    {
        return $this->templateName;
    }

    /**
     * @param string $templateName
     */
    public function setTemplateName($templateName)
    {
        $this->templateName = $templateName;
    }

    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function getAttribute($name)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    public function addNotice($notice)
    {
        $this->notices[] = $notice;
    }

    public function getNotices()
    {
        return $this->notices;
    }
}
