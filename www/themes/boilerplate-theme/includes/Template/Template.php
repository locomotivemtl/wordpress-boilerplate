<?php

namespace App\Theme\Template;

use App\Theme\Traits\HasContentBlocksTrait;

/**
 * Base Template
 */
class Template extends AbstractTemplate
{
    use HasContentBlocksTrait;

    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->set_context([
            'content_blocks' => $this->get_content_blocks(),
        ]);
    }
}
