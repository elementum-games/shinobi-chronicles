<?php

require_once __DIR__ . '/../../classes/System.php';

$system = System::initialize();

echo $system->parseMarkdown(
    text: "
**bold**
*italic*
~~strikethrough~~

> quoted text

`code`

```
block of code
```

[Link Title](https://mycoolwebsite.com)

![Image alt](/images/default_avatar_v2_blue.png)


- list item
   - nested list item
      - more nested list item
- another list item
- yet another list item

1. Ordered list item
2. Another ordered list item
3. Yet another ordered list item
",
allow_images: true
);