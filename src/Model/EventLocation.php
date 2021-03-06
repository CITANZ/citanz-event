<?php

namespace Cita\Event\Model;

use SilverStripe\ORM\DataObject;

/**
 * Description
 *
 * @package silverstripe
 * @subpackage mysite
 */
class EventLocation extends DataObject
{
    /**
     * Defines the database table name
     * @var string
     */
    private static $table_name = 'EventLocation';

    /**
     * Database fields
     * @var array
     */
    private static $db = [
        'Title'     =>  'Varchar(128)',
        'Address'   =>  'Text',
        'Lat'       =>  'Varchar(128)',
        'Lng'       =>  'Varchar(128)',
        'Zoom'      =>  'Int'
    ];

    /**
     * Add default values to database
     * @var array
     */
    private static $defaults = [
        'Zoom'      =>  12
    ];

    public function get_location_string()
    {
        return $this->Title . ' (' . $this->Address . ')';
    }
}
