<?php

namespace Cita\Event\Model;

use SilverStripe\Dev\Debug;
use SilverStripe\Forms\TextareaField;
use SilverStripe\ORM\DataObject;
use BenManu\LeafletField\LeafletField;


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
        'Zoom'      =>  'Int',
        'Geometry'  =>  'Text',
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

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName([
            'Geometry',
            'Lng',
            'Lat',
            'Zoom',
        ]);

        $fields->addFieldsToTab(
            'Root.Main',
            [
                $leaflet = LeafletField::create('Geometry', 'Geometry', $this),
            ]
        );

        $leaflet->setLimit(1);
        $leaflet->setDrawOptions([
            'polygon' => false,
            'polyline' => false,
            'rectangle' => false,
            'circle' => false,
        ]);

        if (!empty($this->Lng) && !empty($this->Lat) && !empty($this->Zoom)) {
            $leaflet->setDescription("Latitude: {$this->Lat}, Longitude: {$this->Lng}, Zoom: {$this->Zoom}");
        }

        return $fields;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        $geo = json_decode($this->Geometry);

        if (!empty($geo)) {
            if (!empty($geo->layers) &&
                !empty($geo->layers[0]->geometry) &&
                !empty($geo->layers[0]->geometry->coordinates)
            ) {
                $this->Lng = $geo->layers[0]->geometry->coordinates[0];
                $this->Lat = $geo->layers[0]->geometry->coordinates[1];
            }

            $this->Zoom = $geo->zoom;
        }
    }
}
