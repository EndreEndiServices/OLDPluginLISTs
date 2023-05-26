<?php

declare(strict_types=1);

namespace fcore\form;

/**
 * Class Button
 * @package fcore\form
 */
class Button {

    const IMAGE_TYPE_URL = "url";
    const IMAGE_TYPE_PATH = "path";

    /** @var array $data */
    public $data = [];

    /**
     * Button constructor.
     * @param string $text
     * @param null $imagePath
     * @param null $imageType
     */
    public function __construct(string $text, $imagePath = null, $imageType = null) {
        $content = ["text" => $text];
        if($imagePath !== null || $imageType !== null) {
            $content["image"]["type"] = $imageType;
            $content["image"]["data"] = $imagePath;
        }
        $this->data = $content;
    }

    /**
     * @return array
     */
    public function getData(): array {
        return $this->data;
    }
}