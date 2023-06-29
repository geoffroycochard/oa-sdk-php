<?php

namespace OpenAgendaSdk;

/**
 * Class Config
 * @package OpenAgendaSdk
 */
abstract class Config
{
    private const ENV_KEY = 'OPENAGENDA_SDK_ENV';
    public const DEFAULT_ENV = 'default';
    public const TEST_ENV = 'test';

    /**
     * @return object
     *   Return an environment object.
     * @throws \Exception
     */
    public static function getConfig(): object
    {
        $configuration = json_decode(file_get_contents(__DIR__ . '/../resources/config.json'));

        return $configuration;
    }

    /**
     * @return string
     *   Return an environment string key.
     */
    public static function getEnv(): string
    {
        return getenv(self::ENV_KEY, TRUE) ?? self::DEFAULT_ENV;
    }
}