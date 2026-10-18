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
 |    panel (Produkty -> praca dyplomowa). The biggest square of the "sample" region is cut
 |    out of the photo as it is (only the colours in "white_reference" are brightened), and
 |    its average colour becomes the colour of that cover.
 |    After changing "sample", "white_reference" or "texture" run:
 |    php artisan db:seed --class=ThesisCoverColorSeeder --force
 |  - university logos: public/images/uczelnie/<university key>.svg (also .png or .webp),
 |    keys as in the "Uczelnie" list, e.g. us.svg, ue.svg, polsl.svg
 |  - imprint font: public/fonts/okladka.woff2 (also .woff, .ttf or .otf)
 */
return [
    /** Proportions of the cover; only the ratio matters (210x297 = A4 portrait). */
    'cover' => ['width' => 210, 'height' => 297],

    /*
     | The front of the cover in the photo, in % of the photo: on the flat cover only, clear of
     | the spine (on the right), the edges and the shadow. The biggest square that fits in it
     | becomes the tile. The photos are taken in the same frame, so one region fits all of them.
     */
    'sample' => ['left' => 28, 'top' => 22, 'width' => 42, 'height' => 49],

    /*
     | The white paper the covers lie on, in % of the photo: bands above and below the cover.
     | The photos of the cover colours listed in "colors" (keys as in "Kolory okładki") are
     | brightened towards this paper reaching "level" (0-255) in each channel, which undoes the
     | exposure the phone picked: without it a white cover comes out grey. The number is how far:
     | 1 = the paper becomes fully white, 0.5 = half of that brightening (on a log scale).
     | A photo whose background is not light grey-white is left as it is.
     */
    'white_reference' => [
        'colors' => ['bialy' => 1, 'limonkowy' => 0.5],
        'regions' => [
            ['left' => 2, 'top' => 2, 'width' => 96, 'height' => 10],
            ['left' => 2, 'top' => 88, 'width' => 96, 'height' => 10],
        ],
        'level' => 250,
    ],

    'texture' => [
        /** Side of the cut square in the tile, in pixels (the tile is twice as tall). */
        'tile' => 768,
        /** Width of one tile as % of the cover width. */
        'size' => 100,
        /** Side in pixels of the small picture of the cloth in the colour swatches. */
        'swatch' => 96,
    ],

    'font' => [
        'family' => 'CoverImprint',
        'path' => 'fonts/okladka',
    ],

    'logos_path' => 'images/uczelnie',

    /*
     | Standard cover titles drawn as artwork: public/images/tytuly/<title key>.svg, keys as in
     | the "Tytuły prac" list, e.g. master_thesis.svg. A title without a file is typeset as text
     | in the imprint font. The artwork is shown at the size of the matrix: "title_art_scale" is
     | the % of the cover width one SVG unit takes (100 / 595.28 = 1 unit is 1 pt on an A4 cover).
     */
    'titles_path' => 'images/tytuly',
    'title_art_scale' => 0.168,

    /*
     | Metallic foils: an imprint colour key (as in "Kolory nadruku") listed here is painted with
     | a sheen, a diagonal gradient through these stops, instead of its flat colour. The flat
     | hex in the admin panel stays the colour of the swatch and of the order.
     */
    'foils' => [
        'gold' => ['#8a6712', '#e6c35a', '#fff1ab', '#c79d2b', '#7a590d', '#dcb548', '#f8e28c', '#a67f1d'],
        'silver' => ['#7b7f85', '#e4e7ea', '#ffffff', '#a8acb2', '#6c7075', '#d2d5d9', '#f4f5f6', '#979ba1'],
    ],

    /*
     | left/top/width/height: the frame of the zone. font_size: % of the cover width.
     | align: left|center|right, valign: start|center|end. tint: paint the logo in the
     | selected imprint color (true) or show it as it is in the file (false).
     */
    'zones' => [
        // University logo at the bottom, shown for the "Standardowy napis" cover.
        'logo' => [
            'left' => 22, 'top' => 80, 'width' => 56, 'height' => 12,
            'tint' => true,
        ],
        // Cover title (e.g. "Praca Magisterska"), a little above the middle, shown for the
        // "Standardowy napis" cover. A title with artwork (see "titles_path") is a picture
        // tinted like the logo, at the same lettering size as the custom text.
        'title' => [
            'left' => 8, 'top' => 24, 'width' => 84, 'height' => 28,
            'font_size' => 7.5, 'line_height' => 1.2, 'letter_spacing' => 0.02,
            'align' => 'center', 'valign' => 'center', 'uppercase' => true,
        ],
        // Text typed by the customer, shown for the "Własny napis" cover.
        'custom' => [
            'left' => 8, 'top' => 24, 'width' => 84, 'height' => 28,
            'font_size' => 7.5, 'line_height' => 1.2, 'letter_spacing' => 0.02,
            'align' => 'center', 'valign' => 'center', 'uppercase' => true,
        ],
    ],
];
