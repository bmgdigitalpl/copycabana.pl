<?php

/*
 | Live cover preview on /prace-dyplomowe (step 04).
 |
 | Every position below is a percentage of the cover photo (0-100), measured from its
 | top-left corner, so the layout stays correct at any preview size. Open
 | /prace-dyplomowe?mapa=1 to draw the frames of all zones on top of the photo and tune
 | the numbers against the real photos.
 |
 | Files that go with it (all optional, the preview falls back to a placeholder):
 |  - cover photos: uploaded per cover color in the admin panel (Produkty -> praca dyplomowa)
 |  - university logos: public/images/uczelnie/<university key>.svg (also .png or .webp),
 |    keys as in the "Uczelnie" list, e.g. us.svg, ue.svg, polsl.svg
 |  - imprint font: public/fonts/okladka.woff2 (also .woff, .ttf or .otf)
 */
return [
    /** Pixel size of one cover photo; only the proportion matters (600x800 = 3:4). */
    'image' => ['width' => 600, 'height' => 800],

    'font' => [
        'family' => 'CoverImprint',
        'path' => 'fonts/okladka',
    ],

    'logos_path' => 'images/uczelnie',

    /*
     | left/top/width/height: the frame of the zone. font_size: % of the photo width.
     | align: left|center|right, valign: start|center|end. tint: paint the logo in the
     | selected imprint color (true) or show it as it is in the file (false).
     */
    'zones' => [
        // University logo, shown for the "Standardowy napis" cover.
        'logo' => [
            'left' => 30, 'top' => 12, 'width' => 40, 'height' => 22,
            'tint' => true,
        ],
        // Cover title (e.g. "Praca Magisterska"), shown for the "Standardowy napis" cover.
        'title' => [
            'left' => 8, 'top' => 44, 'width' => 84, 'height' => 14,
            'font_size' => 6.2, 'line_height' => 1.2, 'letter_spacing' => 0.04,
            'align' => 'center', 'valign' => 'center', 'uppercase' => false,
        ],
        // Text typed by the customer, shown for the "Własny napis" cover.
        'custom' => [
            'left' => 8, 'top' => 36, 'width' => 84, 'height' => 28,
            'font_size' => 5.5, 'line_height' => 1.25, 'letter_spacing' => 0.02,
            'align' => 'center', 'valign' => 'center', 'uppercase' => false,
        ],
    ],
];
