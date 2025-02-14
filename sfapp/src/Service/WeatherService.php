<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

const WEATHER_SERVER = "https://api.openweathermap.org/data/2.5/weather";
const WEATHER_APIKEY = "3a51fe718e7d084a5db6313b81ddbb56";
const WEATHER_CITY = "La Rochelle";
const WEATHER_LANGUAGE = "fr";
const WEATHER_EXCLUDE = "hourly,daily,minutely,alerts"; // Ignore la météo par heure, jour, minutes ainsi que les alertes
const WEATHER_UNITs = "metric"; // Puis demande une réponse en °C (metric)

class WeatherService
{

    private HttpClientInterface $httpClient;

    /**
     * @author Axel
     * @brief constructeur de la classe
     * @param HttpClientInterface $httpClient le client http pour faire des requêtes à l'api
     */
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @author Axel
     * @brief Récupère la météo et la return
     * @return ?array le tableau de réponse ou null
     */
    public function fetchWeather(): ?array
    {
        try
        {
            $response = $this->httpClient->request('GET', WEATHER_SERVER, [
                'query' => [
                    'q' => WEATHER_CITY,
                    'appid' => WEATHER_APIKEY,
                    'lang' => WEATHER_LANGUAGE,
                    'exclude' => WEATHER_EXCLUDE,
                    'units' => WEATHER_UNITs,
                ],
            ]);

            // Si la réponse est bonne return le tableau
            if ($response->getStatusCode() == 200)
            {
                return $response->toArray();
            }
        }
        catch (\Exception $e)
        {
            // Catch les exceptions pouvant être renvoyer par la request
        }
        return null;
    }
}