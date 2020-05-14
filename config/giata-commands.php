<?php

return [
    'countries' => ['EG'],

    // DB Table Name
    'table' => '__hotels',

    // DB Hotels Table columns
    'columns' => ['giataId', 'name', 'propertyCodes', 'ghgml', 'city', 'cityId', 'address', 'phones', 'faxes', 'chains'],

    // Last update date Y-m-d
    'date' => '2020-04-15',

    'translation' => [
        // All Languages to be translated
        'languages' => ['ar'],

        // DB Table Name
        'table' => '__hotels_translations',

        // DB Hotels Translation Table columns
        'columns' => ['giataId', 'lang', 'lastUpdate', 'texts'],

        // Last update date Y-m-d
        'date' => '2020-04-15',
    ],

    'images' => [
        // Optional - Upload to drive => drive name
        'drive' => 'public',

        // DB Table Name
        'table' => '__hotels_images',

        // DB Hotels Images Table columns
        'columns' => ['giataId', 'images'],
    ],

    'providers' => [
        // DB Table Name
        'table' => '__hotels_providers',

        // Providers Names
        'names' => ['TravelBoutiqueOnline'],

        // DB Hotels Table columns
        'columns' => ['giataId', 'providerId', 'provider'],
    ],

];
