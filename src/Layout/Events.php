<?php

namespace Cita\Event\Page;

use SilverStripe\Blog\Model\Blog;

class Events extends Blog
{
    private static $table_name = 'Cita_Events';

    private static $allowed_children = [
        Event::class,
    ];

    private static $description = 'A list page that contains multiple events';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName([
            'ChildPages'
        ]);

        return $fields;
    }
}
