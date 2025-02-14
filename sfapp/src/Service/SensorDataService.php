<?php

namespace App\Service;

use http\Exception;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/** Constantes */
const DATABASE_URL = "https://sae34.k8s.iut-larochelle.fr/api/captures/last";
const DATABASE_USERNAME = "k2eq2";
const DATABASE_USERPASS = "zobdaN-tigqy2-nucsyb";

class SensorDataService
{
    /**
     * @author Axel
     * @brief constructeur du service permettant des appels http
     * @param HttpClientInterface $httpClient l'interface permettant les appels http
     */
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @author  Axel
     * @brief récupére les dernières données d'un capteur en fonction du nom de la base, son nom
     *        et le nom de la querry (temp, hum, etc...)
     * @param string $dbName = le nom de la base (exemple : sae34bdk2eq2)
     * @param string $saName = le nom du SA (exemple: ESP-012)
     * @param string $querryName = le nom de la querry (exemple: temp)
     * @return array|null = la réponse de la requête, null si elle n'a pas fonctionné,
     *                      un tableau si elle a marché
     */
    public function fetchSensorData(string $dbName, string $saName, string $querryName): ?array
    {
        try
        {
            $response = $this->httpClient->request('GET', DATABASE_URL, [
                'headers' => [
                    'dbname' => $dbName,
                    'username' => DATABASE_USERNAME,
                    'userpass' => DATABASE_USERPASS,
                ],
                'query' => [
                    'nom' => $querryName,
                    'nomsa' => $saName,
                    'limit' => 1, // On veut seulement la dernière valeur
                    'page' => 1, // De la dernière page
                ]
            ]);

            // Si ce n'est pas un code 200 (succès)
            if($response->getStatusCode() !== 200)
            {
                return null;
            }

            return $response->toArray();
        }
        catch (\Exception $e)
        {
            throw $e;
        }
    }
}