<?php

namespace OpenAgendaSdk;

/**
 * Class OpenAgendaSdk
 * @package OpenAgendaSdk
 *
 * @link https://developers.openagenda.com
 */
class OpenAgendaSdk
{
  /**
   * @var HttpClient
   */
  private $client;

  /**
   * @var string|null
   */
  private $publicKey;

  /**
   * @var array|null
   */
  private $clientOptions;

  /**
   * OpenAgendaSdk constructor.
   *
   * @param string|null $publicKey
   *   Public key.
   * @param array|null $clientOptions
   *   Array of client options.
   *
   * @see \OpenAgendaSdk\RequestOptions for a list of available client options.
   */
  public function __construct(?string $publicKey, ?array $clientOptions = [])
  {
    $this->publicKey = $publicKey;
    $this->clientOptions = $clientOptions;
  }

  /**
   * @return HttpClient
   *   The Http client.
   * @throws \Exception
   */
  public function getClient(): HttpClient
  {
    return $this->client ?? new HttpClient($this->publicKey, $this->clientOptions);
  }

  /**
   * @return string
   *
   * @link https://developers.openagenda.com/lister-ses-agendas/
   */
  public function getMyAgenda(int $agendaUid): string
  {
    try {
      $content = $this->getClient()->request(Endpoints::MY_AGENDA, ['agendaUid' => $agendaUid]);
    } catch (\Throwable $e) {
      return \json_encode(['error' => $e->getMessage()]);
    }

    return $content;
  }

  /**
   * @param int $agendaUid
   *  The agenda UID.
   *
   * @return string
   *
   * @link https://developers.openagenda.com/configuration-dun-agenda/
   */
  public function getMyAgendas(): string
  {
    try {
      $content = $this->getClient()->request(Endpoints::MY_AGENDAS);
    } catch (\Throwable $e) {
      return \json_encode(['error' => $e->getMessage()]);
    }

    return $content;
  }

  /**
   * @return array
   *   An array of agenda Uids.
   *
   * @link https://developers.openagenda.com/lister-ses-agendas/
   */
  public function getMyAgendasUids(): array
  {
    $agendas = \json_decode($this->getMyAgendas(), false);
    
    if ($agendas->error) {
      return [];
    }
    
    if (!\property_exists($agendas, 'items')) {
      return [];
    }

    $result = [];
    foreach ($agendas->items as $index => $data) {
      $result[] = $data->uid;
    }

    return $result;
  }

  /**
   * @param int $agendaUid
   *   The agenda Uid.
   *
   * @return bool
   *   TRUE if agenda exists or FALSE otherwise.
   */
  public function hasPermission(int $agendaUid): bool
  {
    $agenda = \json_decode($this->getMyAgenda($agendaUid));

    return isset($agenda->me->member);
  }

  /**
   * @param int $agendaUid
   *  The agenda UID.
   *
   * @return string
   *   Response body as json.
   *
   * @link https://developers.openagenda.com/configuration-dun-agenda/
   */
  public function getAgenda(int $agendaUid): string
  {
    try {
      $content = $this->getClient()->request(Endpoints::AGENDAS, ['agendaUid' => $agendaUid]);
    } catch (\Throwable $e) {
      return \json_encode(['error' => $e->getMessage()]);
    }

    return $content;
  }

  /**
   * @param int $agendaUid
   *  The agenda UID.
   *
   * @return array
   *
   * @link https://developers.openagenda.com/lister-ses-agendas/
   */
  public function getAgendaAdditionalFields(int $agendaUid): array
  {
    if (!$this->hasPermission($agendaUid)) {
      return [];
    }

    $agenda = \json_decode($this->getAgenda($agendaUid));

    if ($agenda->error) {
      return [];
    }
    $result = [];
    $fieldsSchema = $agenda->schema->fields;
    foreach ($fieldsSchema as $index => $fieldSchema) {
      if ($fieldSchema->fieldType != 'event') {
        $result[] = $fieldSchema->field;
      }
    }

    return $result;
  }

  /**
   * @param int $agendaUid
   *   The agenda UID.
   * @param array|null $params
   *   Urls query parameters such as search, sort, filters.
   *
   * @return string
   *   Response body as json.
   */
  public function getEvents(int $agendaUid, ?array $params = []): string
  {
    try {
      $content = $this->getClient()->request(Endpoints::EVENTS, ['agendaUid' => $agendaUid], $params + ['includeLabels' => 1, 'detailed' => 1]);
    } catch (\Throwable $e) {
      return \json_encode(['error' => $e->getMessage()]);
    }

    return $content;
  }

  /**
   * @param int $agendaUid
   *   The agenda UID.
   * @param int $eventUid
   *   The event UID.
   *
   * @return string
   *   Response body as json.
   *
   * @link https://developers.openagenda.com/10-lecture/#lire-un-v-nement
   */
  public function getEvent(int $agendaUid, int $eventUid): string
  {
    try {
      $content = $this->getClient()->request(Endpoints::EVENT, ['agendaUid' => $agendaUid, 'eventUid' => $eventUid, 'includeLabels' => 1], ['includeLabels' => 1, 'detailed' => 1]);
    } catch (\Throwable $e) {
      return \json_encode(['error' => $e->getMessage()]);
    }

    return $content;
  }

  /**
   * @param array $event
   *   The event data
   * @param string $url
   *   The event URL
   * @param string $locale
   *   The locale code for localized fields
   * 
   * @return array $data
   *   Array of data to encode and print as Rich snippet
   */
  public function getEventRichSnippet(array $event, string $url = '', string $locale = 'en'): array {
    $schema = [];

    $firstTiming = ! empty( $event['timings'] ) ? $event['timings'][0] : $event['firstTiming'];
    $lastTiming  = ! empty( $event['timings'] ) ? $event['timings'][count($event['timings'])-1] : $event['lastTiming'];

    $begin  = $firstTiming['begin'] ?? '';
    $end    = $lastTiming['end'] ?? '';

    $attendanceModeLabels = [
      1 => 'OfflineEventAttendanceMode',
      2 => 'OnlineEventAttendanceMode',
      3 => 'MixedEventAttendanceMode',
    ];
    $attendanceMode = ! empty($event['attendanceMode']['id']) ? $attendanceModeLabels[$event['attendanceMode']['id']] : $attendanceModeLabels[1];
    
    $eventStatusLabels = [
      1 => 'EventScheduled',
      2 => 'EventRescheduled',
      3 => 'EventMovedOnline',
      4 => 'EventPostponed',
      5 => 'EventScheduled', // but full.
      6 => 'EventCancelled',
    ];
    $eventStatus = ! empty($event['status']['id']) ? $eventStatusLabels[$event['status']['id']] : $eventStatusLabels[1];

    $schema      = [
      '@context'    => 'https://schema.org',
      '@type'       => 'Event',
      'name'        => $this->getEventFieldLocaleValue($event['title'], $locale),
      'description' => $this->getEventFieldLocaleValue($event['description'], $locale),
      'startDate'   => $begin,
      'endDate'     => $end,
      'eventAttendanceMode' => sprintf('https://schema.org/%s',$attendanceMode),
      'eventStatus' => sprintf('https://schema.org/%s',$eventStatus),
    ];

    $registrationLinks = ! empty($event['registration']) ? array_filter($event['registration'], function($r){return $r['type'] == 'link';}) : [];
    if(!empty($registrationLinks)){
      $schema['offers'] = array_map(function($link) use($event){
        return [
          '@type' => 'Offer',
          'url'   => $link['value'],
          'availability' => sprintf('https://schema.org/%s', $event['status']['id'] === 5 ? 'SoldOut' : 'InStock' )
        ];
      },$registrationLinks);
    }

    if($url) {
      $schema['@id'] = $url;
      $schema['url'] = $url;
    }

    if(!empty($event['image'])) {
      $schema['image'] = sprintf('%s%s', $event['image']['base'], $event['image']['filename']);
    }

    $place = [];
    $virtualLocation = [];
    if (!empty($event['location'])) {
      $place = [
        '@type'   => 'Place',
        'name'    => $event['location']['name'],
        'address' => array_filter([
          '@type'          => 'PostalAddress',
          'streetAddress'  => $event['location']['address'] ?? '',
          'addressLocality'=> $event['location']['city']  ?? '',
          'addressRegion'  => $event['location']['region']  ?? '',
          'postalCode'     => $event['location']['postalCode'] ?? '',
          'addressCountry' => $event['location']['countryCode'] ?? '',
        ]),
        'geo'     => [
          '@type'     => 'GeoCoordinates',
          'latitude'  => $event['location']['latitude'],
          'longitude' => $event['location']['longitude'],
        ],
      ];
    }
    if(!empty($event['onlineAccessLink'])){
      $virtualLocation = [
        '@type' => 'VirtualLocation',
        'url'   => $event['onlineAccessLink'],
      ];
    }

    switch ($attendanceMode) {
      case 'OfflineEventAttendanceMode':
        $location = $place;
        break;
      case 'OnlineEventAttendanceMode':
        $location = $virtualLocation;
        break;
      case 'MixedEventAttendanceMode':
        $location = [$place, $virtualLocation];
        break;
    }

    if(!empty($location)){
      $schema['location'] = $location;
    }

    if(!empty($event['age'])){
      $schema['typicalAgeRange'] = sprintf( '%d-%d', (int) $event['age']['min'], (int) $event['age']['max'] );
    }

    return $schema;
  }

  /**
   * @param string|array $field
   *   The event field
   * @param string $locale
   *   The locale code for localized fields
   * 
   * @return string $value
   *   Localized field value. Defaults to 'en' value or first found.
   */
  public function getEventFieldLocaleValue($field, string $locale = 'en'): string {
    $value  = '';
    if( is_string( $field ) ) $value = $field;
    if( is_array( $field ) && ! empty( $field ) ){
        if( array_key_exists( $locale, $field ) ){
            $value = ! empty( $field[$locale] ) ? $field[$locale] : '';
        } else {
            $value = ! empty( $field['en'] ) ? $field['en'] : array_values( $field )[0];
        }
    }
    return $value;
  }
}