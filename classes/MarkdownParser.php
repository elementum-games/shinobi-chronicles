<?php

require __DIR__ . '/vendor/Parsedown.php';

/*

WIP FORMAT GUIDE FOR USERS
**bold**
*italic*
~~strikethrough~~

> quoted text

`code`

```
block of code
```

[Link Title](https://mycoolwebsite.com)

![Image alt](https://mycoolwebsite.com/my_cool_image.png)


- list item
- another list item
- yet another list item

1. Ordered list item
2. Another ordered list item
3. Yet another ordered list item

 */

class MarkdownParser extends Parsedown {
    public bool $disable_images = true;

    public static function instance(string $name = 'default') {
        $instance = parent::instance($name);
        $instance->setSafeMode(true);
        $instance->setMarkupEscaped(true);
        return $instance;
    }

    public function setImagesDisabled(bool $disable_images): static {
        $this->disable_images = $disable_images;
        return $this;
    }

    protected function inlineImage($Excerpt) {
        if($this->disable_images) {
            return null;
        }
        else {
            return parent::inlineImage($Excerpt);
        }
    }

    // Disable things we never want to allow

    protected function blockTable($Line, array $Block = null) {
        return null;
    }

    protected function blockTableContinue($Line, array $Block) {
        return null;
    }

    protected function blockHeader($Line) {
        return null;
    }

    protected function blockSetextHeader($Line, array $Block = null) {
        return null;
    }
}