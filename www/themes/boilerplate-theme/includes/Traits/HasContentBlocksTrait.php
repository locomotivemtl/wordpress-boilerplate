<?php

namespace App\Theme\Traits;

/**
 * Has Content Blocks
 */
trait HasContentBlocksTrait
{
    /**
     * @var ?array
     */
    protected $content_blocks;

    /**
     * @return ?array
     */
    public function get_content_blocks(): ?array
    {
        if (!isset($this->content_blocks)) {
            $blocks = $this->get_fields()['content_blocks'] ?? [];
            if (!empty($blocks) && have_rows('content_blocks')) {
                foreach ($blocks as $block_data) {
                    the_row();
                    $block = $this->transform_block($block_data);
                    if (!empty($block)) {
                        $this->content_blocks[] = $block;
                    }
                }
            }
        }
        return $this->content_blocks;
    }

    /**
     * @param array $block
     * @return array
     */
    public function transform_block(array $block = []): array
    {
        $transformer = $this->resolve_block_transformer($block);
        return $this->transform($block, $transformer);
    }

    /**
     * @param array $block
     * @return string
     */
    public function resolve_block_transformer(array $block): string
    {
        $layout = !empty($block['acf_fc_layout']) ? $block['acf_fc_layout'] : null;
        $namespace = 'App\Theme\Transformer\Content\\';

        if (!empty($layout)) {
            $layout = str_replace('_', '', ucwords($layout, '_'));
            $class = $namespace . $layout;

            if (class_exists($class)) {
                return $class;
            }
        }

        return $namespace . 'ContentBlock';
    }

    /**
     * @param array $data
     * @param string $transformer
     * @return ?array
     */
    abstract public function transform(array $data, string $transformer): ?array;
}
