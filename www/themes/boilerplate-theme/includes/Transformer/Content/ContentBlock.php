<?php

namespace App\Theme\Transformer\Content;

use App\Theme\Transformer\AbstractTransformer;

/**
 * Transformer: Content Block
 */
class ContentBlock extends AbstractTransformer
{
    /**
     * @param  array $data Raw ACF content block data.
     * @return array
     */
    public function __invoke(array $data): array
    {
        $layout = !empty($data['acf_fc_layout']) ?
            str_replace('_', '-', $data['acf_fc_layout']) :
            null;
        return [
            'layout'   => $layout,
            'template' => $this->getTemplatePath($layout),
            'data'     => $data,
        ];
    }

    /**
     * @param string|null $layout
     * @return string
     */
    protected function getTemplatePath(?string $layout = null): string
    {
        $path = get_template_directory() . '/views/blocks/block';

        if (!empty($layout)) {
            $path .= '-' . $layout;
        }

        return $path . '.twig';
    }
}
