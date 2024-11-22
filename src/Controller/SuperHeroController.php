<?php

namespace App\Controller;

use App\Entity\SuperHero;
use App\Form\SuperHeroType;
use App\Repository\SuperHeroRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/super/hero')]
class SuperHeroController extends AbstractController
{
    #[Route(name: 'app_super_hero_index', methods: ['GET'])]
    public function index(Request $request, SuperHeroRepository $superHeroRepository): Response
    {
        $availability = $request->query->get('isAvailable');
        $energyLevel = $request->query->get('energyLevel');

        $queryBuilder = $superHeroRepository->createQueryBuilder('s');

        if ($availability !== null && $availability !== '') {
            $queryBuilder->andWhere('s.isAvailable = :isAvailable')
                         ->setParameter('isAvailable', (bool) $availability);
        }

        if ($energyLevel !== null && $energyLevel !== '') {
            $queryBuilder->andWhere('s.energyLevel >= :energyLevel')
                         ->setParameter('energyLevel', (int) $energyLevel);
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
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('uploads_directory'),
                    $newFilename
                );
                $superHero->setImageName($newFilename);
            }

            $superHero->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($superHero);
            $entityManager->flush();

            return $this->redirectToRoute('app_super_hero_index');
        }

        return $this->render('super_hero/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_super_hero_show', methods: ['GET'])]
    public function show(SuperHero $superHero): Response
    {
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
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('uploads_directory'),
                    $newFilename
                );
                $superHero->setImageName($newFilename);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_super_hero_index');
        }

        return $this->render('super_hero/edit.html.twig', [
            'form' => $form->createView(),
            'super_hero' => $superHero,
        ]);
    }

    #[Route('/{id}', name: 'app_super_hero_delete', methods: ['POST'])]
    public function delete(Request $request, SuperHero $superHero, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $superHero->getId(), $request->request->get('_token'))) {
            if ($superHero->getImageName()) {
                $imagePath = $this->getParameter('uploads_directory') . '/' . $superHero->getImageName();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $entityManager->remove($superHero);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_super_hero_index');
    }
}
