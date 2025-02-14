<?php

/** Pour éxécuter les tests:
 *
 * php bin/phpunit --debug --colors=always --testdox
 *
 * Ajouter '--filter nomTest' pour cibler un test
 */


namespace App\Tests;

use App\Entity\Batiment;
use App\Entity\Capteur;
use App\Entity\Conseil;
use App\Entity\DetailsSalle;
use App\Entity\Etage;
use App\Entity\Etat;
use App\Entity\Plan;
use App\Entity\Salle;
use App\Entity\SystemAcquisition;
use App\Entity\Type;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;



class CapteurListenerTest extends KernelTestCase
{
    private EntityManagerInterface $manager;
    private array $entityArray;

    /**
     * @author Axel
     * @brief Lance le kernel et récupère l'entity manager pour test les insertions
     * @return void
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $this->manager = self::getContainer()->get('doctrine')->getManager();
    }

    /**
     * @author Axel
     * @brief permet de supprimer les entités créé lors de l'exécution des test
     */
    private function deleteTestEntities() : void
    {
        $conseilRepository = $this->manager->getRepository(Conseil::class);

        foreach ($this->entityArray as $entity)
        {
            dump($entity);
            // Si c'est une salle : supprime le conseil
            if ($entity instanceof Salle)
            {
                $salleID = $entity->getId();
                $conseil = $conseilRepository->findOneBy(['salle' => $salleID]);

                //$this->manager->remove($conseil);
            }
            $this->manager->remove($entity);
        }

        $this->manager->flush();
    }

    /**
     * @author Axel
     * @brief Test que le trigger (listener postPersist) fonctionne correctement
     */
    public function testPostPersistListener(): void
    {
        //Variables
        $testTemp = 31;

        $this->entitySetup($testTemp);

        // Capture le dump
        ob_start();
        $this->manager->flush();
        $output = ob_get_clean();

        // Supprime toutes les entités une fois que nous avons la variable a test
        $this->deleteTestEntities();
        $this->assertSame($testTemp, $output);
    }

    /**
     * @author Axel
     * @brief "construit" les éléments nécessaire à la création d'un capteur
     *         pouvant être testé par le trigger
     * @param int $testTemp la température que l'on veut insérer
     */
    private function entitySetup(int $testTemp)
    {
        //Créer un batiment
        $batiment = new Batiment();
        $batiment->setNom("Test batiment");
        $this->manager->persist($batiment);

        //Créer un étage
        $etage = new Etage();
        $etage->setBatiment($batiment);
        $etage->setNomComplet("Test etage");
        $this->manager->persist($etage);

        //Créer une salle
        $salle = new Salle();
        $salle->setNom("Salle Test");
        $salle->setEtage($etage);
        $this->manager->persist($salle);

            // Créer un SA
        $SA = new SystemAcquisition();
        $SA->setNom("SA Test");
        $SA->setEtat(Etat::Fonctionnel);
        $SA->setDescription("SA de test pour les trigger");
        $SA->setDateCreation(new \DateTime());
        $this->manager->persist($SA);

        //Créer le plan liant le SA à la salle
        $plan = new Plan();
        $plan->setSalle($salle);
        $plan->setSA($SA);
        $plan->setDateAssociation(new \DateTime());
        $this->manager->persist($plan);

        // Créer un capteur
        $capteur = new Capteur();
        $capteur->setSA($SA);
        $capteur->setType(Type::temperature);
        $capteur->setValeur($testTemp);
        $this->manager->persist($capteur);
        $this->entityArray[] = $capteur;

        // Insére dans l'odre de supression les Entités:
        $this->entityArray[] = $capteur;
        $this->entityArray[] = $plan;
        $this->entityArray[] = $SA;
        $this->entityArray[] = $salle;
        $this->entityArray[] = $etage;
        $this->entityArray[] = $batiment;
    }
}

