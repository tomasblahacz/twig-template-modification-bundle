<?php

namespace Maba\Bundle\TwigTemplateModificationBundle\Twig;

use Twig_Token as Token;

class ExtendedToken extends Token
{
    /**
     * @var int
     */
    private $startCursor;

    /**
     * @var int
     */
    private $endCursor;

    /**
     * @param int $startCursor
     */
    public function setStartCursor($startCursor)
    {
        $this->startCursor = $startCursor;
    }

    /**
     * @param int $endCursor
     */
    public function setEndCursor($endCursor)
    {
        $this->endCursor = $endCursor;
    }

    /**
     * @return int
     */
    public function getStartCursor()
    {
        return $this->startCursor;
    }

    /**
     * @return int
     */
    public function getEndCursor()
    {
        return $this->endCursor;
    }
}
