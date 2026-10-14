<?php

/*
 | Live cover preview on /prace-dyplomowe (step 04).
 |
 | The preview paints a cover in the chosen colour and texture and puts the university logo
 | and the napis on it. Every position below is a percentage of the cover (0-100), measured
 | from its top-left corner, so the layout stays correct at any preview size. Open
 | /prace-dyplomowe?mapa=1 to draw the frames of all zones on the cover and tune the numbers.
 |
 | Where the pieces come from (all optional, the preview falls back to a plain colour):
 |  - colour and texture: the photo of the blank cover uploaded per cover colour in the admin
 |    panel (Produkty -> praca dyplomowa). A tile of the cloth is cut out of the "sample"
 |    region of the photo and its average colour becomes the colour of that cover.
 |    After changing "sample" or "texture" run: php artisan covers:rebuild-textures
 |  - university logos: public/images/uczelnie/<university key>.svg (also .png or .webp),
 |    keys as in the "Uczelnie" list, e.g. us.svg, ue.svg, polsl.svg
 |  - imprint font: public/fonts/okladka.woff2 (also .woff, .ttf or .otf)
 */
return [
    /** Proportions of the cover; only the ratio matters (210x297 = A4 portrait). */
    'cover' => ['width' => 210, 'height' => 297],

    /*
     | The part of the cover photo the cloth is sampled from, in % of the photo. It has to lie
     | on the flat cover only, clear of the spine, the edges and the shadow. The photos are
     | taken in the same frame, so one region fits all of them.
     */
    'sample' => ['left' => 30, 'top' => 25, 'width' => 37, 'height' => 45],

    'texture' => [
        /** Side of the generated tile in pixels. */
        'tile' => 384,
        /** Width of one tile as % of the cover width. A bigger tile shows the weave more clearly. */
        'size' => 100,
        /** 1 = the texture as photographed, more = a stronger weave. */
        'contrast' => 1.5,
    ],

    'font' => [
        'family' => 'CoverImprint',
        'path' => 'fonts/okladka',
    ],

    'logos_path' => 'images/uczelnie',

    /*
     | left/top/width/height: the frame of the zone. font_size: % of the cover width.
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
