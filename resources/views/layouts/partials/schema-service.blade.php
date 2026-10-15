{{-- schema.org Service + BreadcrumbList for a service page. Expects $name, $description and $path. --}}
@php
  $schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
      [
        '@type' => 'Service',
        'name' => $name,
        'description' => $description,
        'url' => url($path),
        'serviceType' => $name,
        'areaServed' => ['@type' => 'Country', 'name' => 'Polska'],
        'provider' => ['@type' => 'LocalBusiness', '@id' => url('/').'#business', 'name' => config('business.company.name')],
      ],
      [
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
          ['@type' => 'ListItem', 'position' => 1, 'name' => 'Start', 'item' => url('/')],
          ['@type' => 'ListItem', 'position' => 2, 'name' => $name, 'item' => url($path)],
        ],
      ],
    ],
  ];
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
