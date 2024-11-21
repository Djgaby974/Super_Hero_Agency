<?php

namespace App\Controller;

use App\Entity\SuperHero;
use App\Form\SuperHeroType;
use App\Repository\SuperHeroRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/super/hero')]
final class SuperHeroController extends AbstractController
{
    #[Route(name: 'app_super_hero_index', methods: ['GET'])]
    public function index(Request $request, SuperHeroRepository $superHeroRepository): Response
    {
        // Récupérer les filtres de la requête GET
        $availability = $request->query->get('isAvailable'); // Disponible ou non
        $energyLevel = $request->query->get('energyLevel');  // Niveau minimum d'énergie
    
        // Construire la requête avec les filtres
        $queryBuilder = $superHeroRepository->createQueryBuilder('s');
    
        if ($availability !== null && $availability !== '') {
            $queryBuilder->andWhere('s.isAvailable = :isAvailable')
                         ->setParameter('isAvailable', $availability);
        }
    
        if ($energyLevel !== null && $energyLevel !== '') {
            $queryBuilder->andWhere('s.energyLevel >= :energyLevel')
                         ->setParameter('energyLevel', $energyLevel);
        }
    
        $superHeroes = $queryBuilder->getQuery()->getResult();
    
        return $this->render('super_hero/index.html.twig', [
            'super_heroes' => $superHeroes,
        ]);
    }
    

    #[Route('/new', name: 'app_super_hero_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $superHero = new SuperHero();
        $form = $this->createForm(SuperHeroType::class, $superHero);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload d'image
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                // Déplacer l'image dans le répertoire public/uploads
                $imageFile->move(
                    $this->getParameter('uploads_directory'),
                    $newFilename
                );

                // Mettre à jour l'entité avec le nom du fichier
                $superHero->setImageName($newFilename);
            }

            $entityManager->persist($superHero);
            $entityManager->flush();

            return $this->redirectToRoute('app_super_hero_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_hero/new.html.twig', [
            'super_hero' => $superHero,
            'form' => $form,
        ]);
    }

    public function show(SuperHero $superHero): Response
    {
        // Exemple de statistique : pourcentage d'énergie
        $energyPercentage = ($superHero->getEnergyLevel() / 100) * 100;
    
        return $this->render('super_hero/show.html.twig', [
            'super_hero' => $superHero,
            'energyPercentage' => $energyPercentage,
        ]);
    }
    

    #[Route('/{id}/edit', name: 'app_super_hero_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SuperHero $superHero, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SuperHeroType::class, $superHero);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload d'image
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                // Déplacer l'image dans le répertoire public/uploads
                $imageFile->move(
                    $this->getParameter('uploads_directory'),
                    $newFilename
                );

                // Mettre à jour l'entité avec le nom du fichier
                $superHero->setImageName($newFilename);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_super_hero_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_hero/edit.html.twig', [
            'super_hero' => $superHero,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_super_hero_delete', methods: ['POST'])]
    public function delete(Request $request, SuperHero $superHero, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $superHero->getId(), $request->request->get('_token'))) {
            // Supprimer l'image associée si elle existe
            if ($superHero->getImageName()) {
                $imagePath = $this->getParameter('uploads_directory') . '/' . $superHero->getImageName();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $entityManager->remove($superHero);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_super_hero_index', [], Response::HTTP_SEE_OTHER);
    }
}
