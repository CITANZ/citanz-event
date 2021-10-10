<?php

use SilverStripe\Forms\HTMLEditor\TinyMCEConfig;

$editorVideoOnly = clone TinyMCEConfig::get('cms');

$editorVideoOnly->setButtonsForLine(1, 'ssembed');
$editorVideoOnly->setButtonsForLine(2, []);
$editorVideoOnly->disablePlugins('contextmenu');
TinyMCEConfig::set_config('video-only', $editorVideoOnly);
