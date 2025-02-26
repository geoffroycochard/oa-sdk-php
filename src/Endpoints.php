<?php

namespace OpenAgendaSdk;

/**
 * Class Endpoints
 *
 * This class contains a list of built-in OpenAgenda API endpoints.
 * @package OpenAgendaSdk
 */
final class Endpoints
{
    /**
     * My agenda: Get my agenda linked to public key.
     */
    public const MY_AGENDA = 'my_agenda';
    /**
     * My agendas: Get my agendas linked to public key.
     */
    public const MY_AGENDAS = 'my_agendas';
    /**
     * My agendas: Get my agendas linked to public key.
     */
    public const AGENDAS = 'agendas';

    /**
     * events: Get the list of an agenda events.
     */
    public const EVENTS = 'events';

    /**
     * event: Get a particular event of an agenda.
     */
    public const EVENT = 'event';

    /**
     * locations: Get the list of an agenda locations.
     */
    public const LOCATIONS = 'locations';

    /**
     * requestAccessToken: Get the access token for the agenda.
     */
    public const REQUEST_ACCESS_TOKEN = 'requestAccessToken';

    /**
     * createEvent: Create a new event in the agenda.
     */
    public const CREATE_EVENT = 'createEvent';

    /**
     * updateEvent: Update an existing event in the agenda.
     */
    public const UPDATE_EVENT = 'updateEvent';

    /**
     * deleteEvent: Delete an existing event in the agenda.
     */
    public const DELETE_EVENT = 'deleteEvent';
}
