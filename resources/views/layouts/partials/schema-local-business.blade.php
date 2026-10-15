{{-- schema.org LocalBusiness + WebSite, from config/business.php. Included on the home and contact pages. --}}
@php
  $company = config('business.company');
  $schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
      [
        '@type' => 'LocalBusiness',
        '@id' => url('/').'#business',
        'name' => $company['name'],
        'legalName' => $company['legal_name'],
        'description' => 'Drukarnia w Katowicach: druk i oprawa prac dyplomowych, materiały dla firm, ksero, skanowanie i druk wielkoformatowy.',
        'url' => url('/'),
        'image' => asset('images/og-default.png'),
        'logo' => asset('images/logo.png'),
        'email' => $company['email'],
        'telephone' => $company['phones'][0],
        'priceRange' => '$$',
        'address' => [
          '@type' => 'PostalAddress',
          'streetAddress' => $company['street'],
          'postalCode' => $company['postal_code'],
          'addressLocality' => $company['city'],
          'addressRegion' => $company['region'],
          'addressCountry' => $company['country'],
        ],
        'areaServed' => ['@type' => 'AdministrativeArea', 'name' => 'Śląsk'],
        'openingHoursSpecification' => array_map(fn (array $hours): array => [
          '@type' => 'OpeningHoursSpecification',
          'dayOfWeek' => $hours['days'],
          'opens' => $hours['opens'],
          'closes' => $hours['closes'],
        ], $company['opening_hours']),
        'contactPoint' => array_map(fn (string $phone): array => [
          '@type' => 'ContactPoint',
          'telephone' => $phone,
          'contactType' => 'customer service',
          'areaServed' => 'PL',
          'availableLanguage' => 'pl',
        ], $company['phones']),
      ],
      [
        '@type' => 'WebSite',
        '@id' => url('/').'#website',
        'url' => url('/'),
        'name' => $company['name'],
        'inLanguage' => 'pl-PL',
        'publisher' => ['@id' => url('/').'#business'],
      ],
    ],
  ];
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
