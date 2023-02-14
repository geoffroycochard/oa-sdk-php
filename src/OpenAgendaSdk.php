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
      $agendaUids = $this->getMyAgendasUids($agendaUid);

      return \in_array($agendaUid, $agendaUids);
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

}
