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
            'left' => 8, 'top' => 30, 'width' => 84, 'height' => 28,
            'font_size' => 8.5, 'line_height' => 1.2, 'letter_spacing' => 0.02,
            'align' => 'center', 'valign' => 'center', 'uppercase' => false,
        ],
        // Text typed by the customer, shown for the "Własny napis" cover.
        'custom' => [
            'left' => 8, 'top' => 30, 'width' => 84, 'height' => 28,
            'font_size' => 8.5, 'line_height' => 1.2, 'letter_spacing' => 0.02,
            'align' => 'center', 'valign' => 'center', 'uppercase' => false,
        ],
    ],
];
