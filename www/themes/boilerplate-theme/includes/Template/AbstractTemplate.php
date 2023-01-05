<?php

namespace App\Theme\Template;

use Timber\Timber;

/**
 * Abstract Template
 */
abstract class AbstractTemplate
{
    /** @var array */
    private $context;

    /** @var array */
    private $fields;

    /** @var Timber\Post */
    private $post;

    /**
     * @return void
     */
    public function __construct()
    {
        $context    = Timber::context();
        $this->post = Timber::get_post();

        $context['post'] = $this->post;
        $this->set_context($context);
    }

    /**
     * Set Context Data
     *
     * @param array $data Context data.
     * @return $this
     */
    public function set_context(array $data = [])
    {
        $context = $this->get_context();
        $this->context = array_merge($context, $data);
        return $this;
    }

    /**
     * Get Context Data
     *
     * @return array
     */
    public function get_context()
    {
        if (!isset($this->context)) {
            $this->context = [];
        }
        return $this->context;
    }

    /**
     * Get ACF Fields
     *
     * @return array
     */
    public function get_fields()
    {
        if (!isset($this->fields)) {
            $this->fields = [];

            if (!empty($this->post->ID)) {
                $this->fields = get_fields($this->post->ID);
            }
        }
        return $this->fields;
    }

    /**
     * Get Post
     *
     * @return Timber\Post|boolean
     */
    public function get_post()
    {
        if (!isset($this->post)) {
            return false;
        }
        return $this->post;
    }

    /**
     * Transform data.
     *
     * @param array $data
     * @param string $transformer
     * @return ?array
     */
    public function transform(array $data = [], string $transformer): ?array
    {
        $transformer = (new $transformer);
        return $transformer($data);
    }
}
