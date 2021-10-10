<?php

namespace Cita\Event\Page;

use SilverStripe\Forms\TextareaField;
use SilverStripe\Blog\Model\BlogPost;
use Ramsey\Uuid\Uuid;
use Cita\Event\Model\EventLocation;
use gorriecoe\LinkField\LinkField;
use gorriecoe\Link\Models\Link;
use SilverStripe\Forms\HTMLEditor\HtmlEditorField;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\TextField;
use SilverStripe\Security\Member;
use SilverStripe\Forms\DatetimeField;
use SilverStripe\Assets\Image;
use SilverShop\HasOneField\HasOneButtonField;
use Cita\Event\Model\RSVP;
use Bummzack\SortableFile\Forms\SortableUploadField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorConfig;

class Event extends BlogPost
{
    private static $table_name = 'Cita_Event';

    private static $can_be_root = false;

    private static $description = 'Like the name says: an event Page :)';

    private static $db = [
        'QRToken'       =>  'Varchar(40)',
        'EventVideo'    =>  'HTMLText',
        'EventStart'    =>  'Datetime',
        'EventEnd'      =>  'Datetime',
        'AttendeeLimit' =>  'Int',
        'AllowGuests'   =>  'Boolean',
        'MaxGuests'     =>  'Int'
    ];

    private static $has_one = [
        'Location' => EventLocation::class,
        'WebinarLink' => Link::class
    ];

    /**
     * Relationship version ownership
     * @var array
     */
    private static $owns = [
        'FeaturedImage',
        'EventPhotos',
    ];

    /**
     * Has_many relationship
     * @var array
     */
    private static $has_many = [
        'RSVPs'     =>  RSVP::class
    ];

    private static $cascade_deletes = [
        'RSVPs'
    ];

    /**
     * Many_many relationship
     * @var array
     */
    private static $many_many = [
        'EventPhotos'   =>  Image::class
    ];

    /**
     * Defines Database fields for the Many_many bridging table
     * @var array
     */
    private static $many_many_extraFields = [
        'EventPhotos' => [
            'SortOrder' => 'Int'
        ]
    ];

    public function populateDefaults()
    {
        parent::populateDefaults();

        $uuid = Uuid::uuid4();
        $this->QRToken = $uuid->toString();
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if (empty($this->QRToken)) {
            $uuid = Uuid::uuid4();
            $this->QRToken = $uuid->toString();
        }
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->insertBefore('Content', $fields->fieldByName('Root.Main.FeaturedImage'));

        $fields->removeByName([
            'CustomSummary',
        ]);

        $fields->insertBefore(
            'Content',
            TextareaField::create('Summary')
              ->setDescription('It will  be used in the iCal file attached to the RSVP confirmation email. If you don\'t know what that is, leave it blank.')
        );

        $fields->addFieldsToTab(
            'Root.EventDetails',
            [
                DatetimeField::create(
                    'EventStart',
                    'Start'
                ),
                DatetimeField::create(
                    'EventEnd',
                    'End'
                ),
                TextField::create(
                    'AttendeeLimit',
                    'Attendee Limit'
                )->setDescription('0 means no limit.'),
                CheckboxField::create(
                    'AllowGuests',
                    'Allow Guests'
                ),
                TextField::create(
                    'MaxGuests',
                    'Max. number of guests can a RSVP bring'
                )->displayIf('AllowGuests')
                  ->isChecked()
                  ->end(),
                HasOneButtonField::create($this, "Location"),
                LinkField::create(
                    'WebinarLink',
                    'External Link',
                    $this
                )->setDescription('e.g. a link going to the online webinar address')
            ]
        );

        $fields->addFieldsToTab(
            'Root.Gallery',
            [
                HtmlEditorField::create('EventVideo', 'Vidoe')
                    ->setEditorConfig(HTMLEditorConfig::get('video-only')),
                SortableUploadField::create('EventPhotos', 'EventPhotos')
                    ->setFolderName("event-{$this->QRToken}"),
            ]
        );

        return $fields;
    }

    public function hasEnoughSeats($n, $rsvp)
    {
        return $this->AttendeeLimit - $this->getTotalAttendeeCount($rsvp) - $n >= 0;
    }

    public function getTotalAttendeeCount($exclude = null)
    {
        $n      =   0;
        $rsvps  =   $this->RSVPs();

        if (!empty($exclude)) {
            $rsvps  =   $rsvps->exclude(['ID' => $exclude->ID]);
        }

        foreach ($rsvps as $rsvp) {
            $n += ($rsvp->NumGuests + 1);
        }

        return $n;
    }

    public function alreadySignedup()
    {
        if ($member = Member::currentUser()) {
            return $this->RSVPs()->filter(['MemberID' => $member->ID])->first();
        }

        return false;
    }
}
