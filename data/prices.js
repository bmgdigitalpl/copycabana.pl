/* ===========================
   CopyCabana — Price Data
   All product pricing configurations
   Admin can modify these via admin panel
   =========================== */

const DEFAULT_PRICES = {
  wizytowki: {
    name: "Wizytówki",
    slug: "wizytowki",
    image: "images/produkty/product-02.png",
    category: "druk",
    description: "Profesjonalne wizytówki w różnych formatach i wykończeniach.",
    calculatorType: "options",
    options: {
      typ: {
        label: "Typ wizytówek",
        type: "select",
        values: [
          { value: "klasyczne", label: "Klasyczne (kreda mat 350g)", price: 0 },
          { value: "folia_blyszczaca", label: "Z folią błyszczącą", price: 15 },
          { value: "folia_matowa", label: "Z folią matową", price: 15 },
          { value: "soft_touch", label: "Soft Touch", price: 25 },
          { value: "papier_ozdobny", label: "Na papierze ozdobnym", price: 20 },
          { value: "zlocenie", label: "Ze złoceniem", price: 40 },
          { value: "lakier_wyborczy", label: "Z lakierem wybiórczym", price: 35 },
          { value: "multiloft", label: "Multiloft (wielowarstwowe)", price: 60 }
        ],
        default: "klasyczne"
      },
      naklad: {
        label: "Nakład (sztuk)",
        type: "select",
        values: [
          { value: "50", label: "50", price: 25 },
          { value: "100", label: "100", price: 40 },
          { value: "200", label: "200", price: 65 },
          { value: "500", label: "500", price: 120 },
          { value: "1000", label: "1000", price: 180 },
          { value: "2000", label: "2000", price: 280 },
          { value: "5000", label: "5000", price: 500 }
        ],
        default: "100"
      },
      projekt: {
        label: "Projekt graficzny",
        type: "select",
        values: [
          { value: "nie", label: "Nie mam projektu", price: 0 },
          { value: "tak", label: "Tak, potrzebuję projektu", price: 80 }
        ],
        default: "nie"
      }
    }
  },

  ulotki: {
    name: "Ulotki",
    slug: "ulotki",
    image: "images/produkty/product-03.png",
    category: "druk",
    description: "Ulotki reklamowe w różnych formatach i gramaturach papieru.",
    calculatorType: "options",
    options: {
      format: {
        label: "Format",
        type: "select",
        values: [
          { value: "A6", label: "A6 (148 × 105 mm)", price: 0 },
          { value: "A5", label: "A5 (210 × 148 mm)", price: 5 },
          { value: "A4", label: "A4 (297 × 210 mm)", price: 10 },
          { value: "DL", label: "DL (210 × 99 mm)", price: 3 },
          { value: "A3", label: "A3 (420 × 297 mm)", price: 18 }
        ],
        default: "A5"
      },
      papier: {
        label: "Papier",
        type: "select",
        values: [
          { value: "kreda_130", label: "Kreda mat 130g", price: 0 },
          { value: "kreda_170", label: "Kreda mat 170g", price: 3 },
          { value: "kreda_250", label: "Kreda mat 250g", price: 6 },
          { value: "kreda_350", label: "Kreda mat 350g", price: 10 }
        ],
        default: "kreda_130"
      },
      zadruk: {
        label: "Zadruk",
        type: "select",
        values: [
          { value: "jednostronny", label: "Jednostronny (4/0)", price: 0 },
          { value: "dwustronny", label: "Dwustronny (4/4)", price: 5 }
        ],
        default: "jednostronny"
      },
      naklad: {
        label: "Nakład (sztuk)",
        type: "select",
        values: [
          { value: "50", label: "50", price: 30 },
          { value: "100", label: "100", price: 45 },
          { value: "250", label: "250", price: 70 },
          { value: "500", label: "500", price: 100 },
          { value: "1000", label: "1000", price: 160 },
          { value: "2000", label: "2000", price: 260 },
          { value: "5000", label: "5000", price: 450 }
        ],
        default: "250"
      }
    }
  },

  plakaty: {
    name: "Plakaty",
    slug: "plakaty",
    image: "images/produkty/product-04.png",
    category: "reklama",
    description: "Plakaty w formatach od A3 do B1 na papierze błysk i mat.",
    calculatorType: "options",
    options: {
      format: {
        label: "Format",
        type: "select",
        values: [
          { value: "A3", label: "A3 (420 × 297 mm)", price: 8 },
          { value: "A2", label: "A2 (594 × 420 mm)", price: 14 },
          { value: "A1", label: "A1 (841 × 594 mm)", price: 22 },
          { value: "B1", label: "B1 (1000 × 707 mm)", price: 32 },
          { value: "B2", label: "B2 (707 × 500 mm)", price: 25 }
        ],
        default: "A3"
      },
      papier: {
        label: "Papier",
        type: "select",
        values: [
          { value: "kreda_blyszcz", label: "Kreda błyszcząca 150g", price: 0 },
          { value: "kreda_mat", label: "Kreda matowa 150g", price: 0 },
          { value: "kreda_blyszcz_200", label: "Kreda błyszcząca 200g", price: 3 },
          { value: "kreda_mat_200", label: "Kreda matowa 200g", price: 3 },
          { value: "syneps", label: "Syneps 200g (wodoodporny)", price: 8 }
        ],
        default: "kreda_blyszcz"
      },
      kolor: {
        label: "Kolor",
        type: "select",
        values: [
          { value: "kolor", label: "Kolorowy (CMYK)", price: 0 },
          { value: "czarnobialy", label: "Czarno-biały", price: -3 }
        ],
        default: "kolor"
      },
      naklad: {
        label: "Nakład (sztuk)",
        type: "select",
        values: [
          { value: "1", label: "1", price: 1 },
          { value: "2", label: "2", price: 2 },
          { value: "5", label: "5", price: 5 },
          { value: "10", label: "10", price: 10 },
          { value: "20", label: "20", price: 18 },
          { value: "50", label: "50", price: 40 }
        ],
        default: "1"
      }
    }
  },

  rollupy: {
    name: "Rollupy",
    slug: "rollupy",
    image: "images/produkty/product-05.png",
    category: "reklama",
    description: "Systemy wystawiennicze z grafiką na wymiar.",
    calculatorType: "options",
    options: {
      szerokosc: {
        label: "Szerokość",
        type: "select",
        values: [
          { value: "85", label: "85 cm", price: 0 },
          { value: "100", label: "100 cm", price: 15 },
          { value: "120", label: "120 cm", price: 30 },
          { value: "150", label: "150 cm", price: 50 }
        ],
        default: "85"
      },
      wysokosc: {
        label: "Wysokość",
        type: "select",
        values: [
          { value: "200", label: "200 cm", price: 0 },
          { value: "220", label: "220 cm", price: 10 },
          { value: "250", label: "250 cm", price: 20 }
        ],
        default: "200"
      },
      material: {
        label: "Materiał",
        type: "select",
        values: [
          { value: "banner_440", label: "Banner 440g (standard)", price: 0 },
          { value: "banner_510", label: "Banner 510g (premium)", price: 15 },
          { value: "blockout", label: "Blockout (blokujący światło)", price: 25 }
        ],
        default: "banner_440"
      }
    }
  },

  banery: {
    name: "Banery",
    slug: "banery",
    image: "images/produkty/product-06.png",
    category: "reklama",
    description: "Banery wielkoformatowe z oczkami i wykończeniem.",
    calculatorType: "area",
    pricePerM2: 35,
    minPrice: 30,
    options: {
      material: {
        label: "Materiał",
        type: "select",
        values: [
          { value: "banner_440", label: "Banner 440g", price: 0 },
          { value: "banner_510", label: "Banner 510g", price: 10 },
          { value: "siatka_mesh", label: "Siatka mesh (wiatroodporna)", price: 5 }
        ],
        default: "banner_440"
      },
      wykończenie: {
        label: "Wykończenie",
        type: "select",
        values: [
          { value: "oczka", label: "Oczka co 50cm", price: 0 },
          { value: "tunel", label: "Tunel górny i dolny", price: 5 },
          { value: "kieszenie", label: "Kieszenie na pręty", price: 8 }
        ],
        default: "oczka"
      }
    }
  },

  billboardy: {
    name: "Billboardy",
    slug: "billboardy",
    image: "images/produkty/product-07.png",
    category: "reklama",
    description: "Reklama zewnętrzna w dużym formacie na Śląsku.",
    calculatorType: "area",
    pricePerM2: 28,
    minPrice: 50,
    options: {
      material: {
        label: "Materiał",
        type: "select",
        values: [
          { value: "banner_440", label: "Banner PVC 440g", price: 0 },
          { value: "banner_510", label: "Banner PVC 510g", price: 12 },
          { value: "blockout", label: "Blockout", price: 20 }
        ],
        default: "banner_440"
      }
    }
  },

  fotoobrazy: {
    name: "Fotoobrazy",
    slug: "fotoobrazy",
    image: "images/produkty/product-08.png",
    category: "foto",
    description: "Fotoobrazy na płótnie i w ramach.",
    calculatorType: "options",
    options: {
      rozmiar: {
        label: "Rozmiar",
        type: "select",
        values: [
          { value: "30x40", label: "30 × 40 cm", price: 89 },
          { value: "40x60", label: "40 × 60 cm", price: 129 },
          { value: "50x70", label: "50 × 70 cm", price: 169 },
          { value: "60x80", label: "60 × 80 cm", price: 219 },
          { value: "80x120", label: "80 × 120 cm", price: 319 },
          { value: "100x150", label: "100 × 150 cm", price: 449 }
        ],
        default: "40x60"
      },
      typ: {
        label: "Typ",
        type: "select",
        values: [
          { value: "plotno", label: "Na płótnie (bez ramy)", price: 0 },
          { value: "rama_cienka", label: "W ramie cienkiej (1.5cm)", price: 40 },
          { value: "rama_gruba", label: "W ramie grubej (3cm)", price: 70 }
        ],
        default: "plotno"
      }
    }
  },

  fototapety: {
    name: "Fototapety",
    slug: "fototapety",
    image: "images/produkty/product-09.png",
    category: "foto",
    description: "Fototapety na wymiar do wnętrz i biur.",
    calculatorType: "area",
    pricePerM2: 45,
    minPrice: 40,
    options: {
      material: {
        label: "Materiał",
        type: "select",
        values: [
          { value: "vinyl_mat", label: "Winyl matowy", price: 0 },
          { value: "vinyl_blyszcz", label: "Winyl błyszczący", price: 3 },
          { value: "samoprzylepny", label: "Folia samoprzylepna", price: 5 },
          { value: "zmywalny", label: "Winyl zmywalny (do kuchni)", price: 8 }
        ],
        default: "vinyl_mat"
      }
    }
  },

  kalendarze: {
    name: "Kalendarze spiralowane",
    slug: "kalendarze",
    image: "images/produkty/product-10.png",
    category: "druk",
    description: "Kalendarze ścienne i biurkowe z indywidualnym projektem.",
    calculatorType: "options",
    options: {
      typ: {
        label: "Typ kalendarza",
        type: "select",
        values: [
          { value: "scienny_A3", label: "Ścienny A3 (12 kartek)", price: 35 },
          { value: "scienny_A4", label: "Ścienny A4 (12 kartek)", price: 25 },
          { value: "biurkowy", label: "Biurkowy (13 kartek)", price: 20 },
          { value: "scianka", label: "Ścianka jednodzielna", price: 15 }
        ],
        default: "scienny_A3"
      },
      papier: {
        label: "Papier",
        type: "select",
        values: [
          { value: "kreda_170", label: "Kreda mat 170g", price: 0 },
          { value: "kreda_200", label: "Kreda mat 200g", price: 3 },
          { value: "kreda_250", label: "Kreda mat 250g", price: 5 }
        ],
        default: "kreda_170"
      },
      naklad: {
        label: "Nakład (sztuk)",
        type: "select",
        values: [
          { value: "10", label: "10", price: 1 },
          { value: "25", label: "25", price: 2 },
          { value: "50", label: "50", price: 3 },
          { value: "100", label: "100", price: 5 },
          { value: "250", label: "250", price: 8 }
        ],
        default: "10"
      }
    }
  },

  naklejki: {
    name: "Naklejki",
    slug: "naklejki",
    image: "images/produkty/product-11.png",
    category: "druk",
    description: "Naklejki w dowolnych kształtach i rozmiarach.",
    calculatorType: "options",
    options: {
      rozmiar: {
        label: "Rozmiar (szer. × wys.)",
        type: "select",
        values: [
          { value: "5x5", label: "5 × 5 cm", price: 25 },
          { value: "7x7", label: "7 × 7 cm", price: 30 },
          { value: "10x10", label: "10 × 10 cm", price: 40 },
          { value: "15x15", label: "15 × 15 cm", price: 55 },
          { value: "20x20", label: "20 × 20 cm", price: 70 },
          { value: "niestandardowy", label: "Niestandardowy", price: 0 }
        ],
        default: "10x10"
      },
      ksztalt: {
        label: "Kształt",
        type: "select",
        values: [
          { value: "prostokat", label: "Prostokątny", price: 0 },
          { value: "obrys", label: "Wycinany po obrysie", price: 10 }
        ],
        default: "prostokat"
      },
      material: {
        label: "Materiał",
        type: "select",
        values: [
          { value: "bialy", label: "Biały samoprzylepny", price: 0 },
          { value: "przezroczysty", label: "Przezroczysty", price: 8 },
          { value: "folia_okenna", label: "Folia okienna (one-way)", price: 15 },
          { value: "blyszczaca", label: "Folia błyszcząca", price: 5 }
        ],
        default: "bialy"
      },
      laminowanie: {
        label: "Laminowanie",
        type: "select",
        values: [
          { value: "nie", label: "Bez laminowania", price: 0 },
          { value: "mat", label: "Laminowanie matowe", price: 10 },
          { value: "blyszcz", label: "Laminowanie błyszczące", price: 10 }
        ],
        default: "nie"
      },
      naklad: {
        label: "Nakład (sztuk)",
        type: "select",
        values: [
          { value: "10", label: "10", price: 0 },
          { value: "25", label: "25", price: 5 },
          { value: "50", label: "50", price: 10 },
          { value: "100", label: "100", price: 18 },
          { value: "250", label: "250", price: 35 },
          { value: "500", label: "500", price: 60 }
        ],
        default: "50"
      }
    }
  },

  tabliczki: {
    name: "Tabliczki grawerowane",
    slug: "tabliczki",
    image: "images/produkty/product-12.png",
    category: "uslugi",
    description: "Tabliczki informacyjne i grawerowane laserowo.",
    calculatorType: "options",
    options: {
      material: {
        label: "Materiał",
        type: "select",
        values: [
          { value: "aluminium", label: "Aluminium (srebrne)", price: 35 },
          { value: "drewno", label: "Drewno (brzoza/dąb)", price: 30 },
          { value: "akryl", label: "Akryl (przezroczysty/biały)", price: 40 },
          { value: "pleksa", label: "Pleksa kolorowa", price: 45 }
        ],
        default: "aluminium"
      },
      rozmiar: {
        label: "Rozmiar",
        type: "select",
        values: [
          { value: "10x5", label: "10 × 5 cm", price: 0 },
          { value: "15x7", label: "15 × 7 cm", price: 10 },
          { value: "20x10", label: "20 × 10 cm", price: 20 },
          { value: "30x10", label: "30 × 10 cm", price: 30 }
        ],
        default: "15x7"
      },
      grawerunek: {
        label: "Grawerunek",
        type: "select",
        values: [
          { value: "tekst", label: "Tylko tekst", price: 0 },
          { value: "tekst_logo", label: "Tekst + logo", price: 15 }
        ],
        default: "tekst"
      }
    }
  },

  rysunki_cad: {
    name: "Rysunki, plany, mapy",
    slug: "rysunki-cad",
    image: "images/produkty/product-13.png",
    category: "uslugi",
    description: "Wydruki CAD w formatach A0–A4.",
    calculatorType: "ksero",
    prices: {
      A4: { kolor: 2.00, czarnobialy: 0.50 },
      A3: { kolor: 4.00, czarnobialy: 1.00 },
      A2: { kolor: 8.00, czarnobialy: 2.50 },
      A1: { kolor: 15.00, czarnobialy: 5.00 },
      A0: { kolor: 25.00, czarnobialy: 8.00 }
    },
    options: {
      format: {
        label: "Format",
        type: "select",
        values: [
          { value: "A0", label: "A0 (841 × 1189 mm)" },
          { value: "A1", label: "A1 (594 × 841 mm)" },
          { value: "A2", label: "A2 (420 × 594 mm)" },
          { value: "A3", label: "A3 (297 × 420 mm)" },
          { value: "A4", label: "A4 (210 × 297 mm)" }
        ],
        default: "A3"
      },
      kolor: {
        label: "Rodzaj",
        type: "select",
        values: [
          { value: "kolor", label: "Kolorowy" },
          { value: "czarnobialy", label: "Czarno-biały" }
        ],
        default: "kolor"
      },
      naklad: {
        label: "Liczba kopii",
        type: "number",
        min: 1,
        max: 1000,
        default: 1
      }
    }
  },

  ksero: {
    name: "Ksero",
    slug: "ksero",
    image: "images/produkty/product-14.png",
    category: "uslugi",
    description: "Kserokopie w czerni-bieli i kolorze.",
    calculatorType: "ksero",
    prices: {
      A4: { kolor: 1.50, czarnobialy: 0.35 },
      A3: { kolor: 3.00, czarnobialy: 0.70 }
    },
    options: {
      format: {
        label: "Format",
        type: "select",
        values: [
          { value: "A4", label: "A4" },
          { value: "A3", label: "A3" }
        ],
        default: "A4"
      },
      kolor: {
        label: "Rodzaj",
        type: "select",
        values: [
          { value: "czarnobialy", label: "Czarno-biały" },
          { value: "kolor", label: "Kolor" }
        ],
        default: "czarnobialy"
      },
      naklad: {
        label: "Liczba kopii",
        type: "number",
        min: 1,
        max: 10000,
        default: 10
      }
    }
  },

  druk: {
    name: "Druk",
    slug: "druk",
    image: "images/produkty/product-15.png",
    category: "druk",
    description: "Druk cyfrowy i offsetowy na różnych nośnikach.",
    calculatorType: "options",
    options: {
      format: {
        label: "Format",
        type: "select",
        values: [
          { value: "A4", label: "A4", price: 1 },
          { value: "A3", label: "A3", price: 2 },
          { value: "A2", label: "A2", price: 4 },
          { value: "A1", label: "A1", price: 8 },
          { value: "A0", label: "A0", price: 15 }
        ],
        default: "A4"
      },
      kolor: {
        label: "Rodzaj",
        type: "select",
        values: [
          { value: "czarnobialy", label: "Czarno-biały", price: 0 },
          { value: "kolor", label: "Kolor", price: 1 }
        ],
        default: "czarnobialy"
      },
      papier: {
        label: "Papier",
        type: "select",
        values: [
          { value: "offset_80", label: "Offset 80g", price: 0 },
          { value: "kreda_130", label: "Kreda 130g", price: 0.5 },
          { value: "kreda_170", label: "Kreda 170g", price: 1 },
          { value: "kreda_250", label: "Kreda 250g", price: 2 }
        ],
        default: "offset_80"
      },
      naklad: {
        label: "Liczba stron",
        type: "number",
        min: 1,
        max: 10000,
        default: 10
      }
    }
  },

  skanowanie: {
    name: "Skanowanie",
    slug: "skanowanie",
    image: "images/produkty/product-16.png",
    category: "uslugi",
    description: "Skanowanie dokumentów i zdjęć w wysokiej rozdzielczości.",
    calculatorType: "ksero",
    prices: {
      A4: { kolor: 1.00, czarnobialy: 1.00 },
      A3: { kolor: 2.00, czarnobialy: 2.00 }
    },
    options: {
      format: {
        label: "Format",
        type: "select",
        values: [
          { value: "A4", label: "A4" },
          { value: "A3", label: "A3" }
        ],
        default: "A4"
      },
      kolor: {
        label: "Rodzaj",
        type: "select",
        values: [
          { value: "kolor", label: "Kolor" },
          { value: "czarnobialy", label: "Czarno-biały" }
        ],
        default: "kolor"
      },
      naklad: {
        label: "Liczba stron",
        type: "number",
        min: 1,
        max: 500,
        default: 1
      }
    }
  },

  zdjecia_dokumenty: {
    name: "Zdjęcia do dokumentów",
    slug: "zdjecia-dokumenty",
    image: "images/produkty/product-17.png",
    category: "uslugi",
    description: "Fotoset do paszportu, dowodu i wizy.",
    calculatorType: "fixed",
    items: [
      { value: "paszport", label: "Zdjęcie paszportowe (35×45mm, 4 szt.)", price: 25 },
      { value: "dowod", label: "Zdjęcie dowodowe (35×45mm, 4 szt.)", price: 25 },
      { value: "wiza_schengen", label: "Zdjęcie wizowe Schengen (35×45mm, 4 szt.)", price: 30 },
      { value: "wiza_usa", label: "Zdjęcie wizowe USA (50×50mm, 4 szt.)", price: 30 },
      { value: "legitymacja", label: "Zdjęcie legitymacyjne (30×40mm, 4 szt.)", price: 20 },
      { value: "emerytura", label: "Zdjęcie emerytalne (25×30mm, 4 szt.)", price: 20 }
    ]
  },

  pieczatki: {
    name: "Pieczątki",
    slug: "pieczatki",
    image: "images/produkty/product-18.png",
    category: "uslugi",
    description: "Pieczątki stemple i datowniki.",
    calculatorType: "fixed",
    items: [
      { value: "trodat_4911", label: "Trodat 4911 (kieszonkowa)", price: 45 },
      { value: "trodat_4912", label: "Trodat 4912 (mała)", price: 55 },
      { value: "trodat_4913", label: "Trodat 4913 (średnia)", price: 70 },
      { value: "trodat_4915", label: "Trodat 4915 (duża)", price: 85 },
      { value: "trodat_4921", label: "Trodat 4921 (datownik)", price: 75 },
      { value: "trodat_4923", label: "Trodat 4923 (datownik duży)", price: 95 },
      { value: "trodat_4941", label: "Trodat 4941 (profesjonalna)", price: 120 }
    ]
  },

  projektowanie: {
    name: "Projektowanie graficzne",
    slug: "projektowanie",
    image: "images/produkty/product-19.png",
    category: "reklama",
    description: "Projekty graficzne materiałów reklamowych.",
    calculatorType: "fixed",
    items: [
      { value: "wizytowka", label: "Projekt wizytówki", price: 80 },
      { value: "ulotka", label: "Projekt ulotki (A5/A4)", price: 150 },
      { value: "plakat", label: "Projekt plakatu (A3–A1)", price: 200 },
      { value: "baner", label: "Projekt banera", price: 250 },
      { value: "rollup", label: "Projekt rollupa", price: 200 },
      { value: "logo", label: "Projekt logo", price: 300 },
      { value: "katalog", label: "Projekt katalogu/broszury", price: 400 }
    ]
  },

  uslugi_dodatkowe: {
    name: "Usługi dodatkowe",
    slug: "uslugi-dodatkowe",
    image: "images/produkty/product-01.png",
    category: "uslugi",
    description: "Laminowanie, oprawianie, personalizacja.",
    calculatorType: "fixed",
    items: [
      { value: "laminowanie_A4", label: "Laminowanie A4 (do 30 stron)", price: 3.50 },
      { value: "laminowanie_A3", label: "Laminowanie A3 (do 30 stron)", price: 6.00 },
      { value: "binda_plastik", label: "Binda plastikowa", price: 2.00 },
      { value: "binda_ozdobna", label: "Binda ozdobna", price: 5.00 },
      { value: "spirala_czarna", label: "Spirala czarna", price: 5.00 },
      { value: "spirala_biala", label: "Spirala biała", price: 5.00 },
      { value: "grzbiet_kanalkowy", label: "Grzbiet kanałowy", price: 8.00 }
    ]
  },

  oprawa_prac: {
    name: "Oprawa prac i bindowanie",
    slug: "oprawa-prac",
    image: "images/produkty/product-02.png",
    category: "druk",
    description: "Oprawa twarda i miękka prac dyplomowych w 24h.",
    calculatorType: "fixed",
    items: [
      { value: "miekka_kanalowa", label: "Miękka oprawa kanałowa", price: 25 },
      { value: "twarda_standard", label: "Twarda oprawa kanałowa (standard)", price: 35 },
      { value: "twarda_premium", label: "Twarda oprawa kanałowa (premium)", price: 55 },
      { value: "termobindowanie_50", label: "Termobindowanie (do 50 stron)", price: 10 },
      { value: "termobindowanie_100", label: "Termobindowanie (51–100 stron)", price: 15 },
      { value: "termobindowanie_200", label: "Termobindowanie (101–200 stron)", price: 20 },
      { value: "bindowanie_50", label: "Bindowanie spiralą (do 50 stron)", price: 8 },
      { value: "bindowanie_100", label: "Bindowanie spiralą (51–100 stron)", price: 12 },
      { value: "bindowanie_200", label: "Bindowanie spiralą (101–200 stron)", price: 16 },
      { value: "bindowanie_500", label: "Bindowanie spiralą (201–500 stron)", price: 22 }
    ]
  }
};

/* --- Price Data Manager --- */
const PriceManager = {
  STORAGE_KEY: 'copycabana_prices',

  getAll() {
    const stored = localStorage.getItem(this.STORAGE_KEY);
    if (stored) {
      try {
        return JSON.parse(stored);
      } catch (e) {
        console.warn('Failed to parse stored prices, using defaults');
      }
    }
    return JSON.parse(JSON.stringify(DEFAULT_PRICES));
  },

  get(slug) {
    const all = this.getAll();
    return all[slug] || null;
  },

  save(prices) {
    localStorage.setItem(this.STORAGE_KEY, JSON.stringify(prices));
  },

  reset() {
    localStorage.removeItem(this.STORAGE_KEY);
  },

  resetProduct(slug) {
    const all = this.getAll();
    if (DEFAULT_PRICES[slug]) {
      all[slug] = JSON.parse(JSON.stringify(DEFAULT_PRICES[slug]));
      this.save(all);
    }
  },

  updateProduct(slug, data) {
    const all = this.getAll();
    all[slug] = data;
    this.save(all);
  },

  exportAll() {
    const all = this.getAll();
    const blob = new Blob([JSON.stringify(all, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'copycabana-cennik.json';
    a.click();
    URL.revokeObjectURL(url);
  },

  importAll(jsonString) {
    try {
      const data = JSON.parse(jsonString);
      this.save(data);
      return true;
    } catch (e) {
      console.error('Import failed:', e);
      return false;
    }
  }
};
