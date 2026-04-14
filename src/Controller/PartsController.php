<?php

namespace App\Controller;

use App\Entity\Part;
use App\Entity\User;
use App\Form\PartFormType;
use App\Repository\AiInsightRepository;
use App\Repository\PartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/parts')]
class PartsController extends AbstractController
{
    #[Route('', name: 'parts_index')]
    public function index(PartRepository $partRepo, AiInsightRepository $insightRepo): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $parts = $partRepo->findByUser($user);
        $latestSynthesis = $insightRepo->findLatestByUserAndType($user, 'parts_synthesis');

        return $this->render('parts/index.html.twig', [
            'parts' => $parts,
            'latest_synthesis' => $latestSynthesis,
        ]);
    }

    #[Route('/new', name: 'parts_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $part = new Part();
        $part->setUser($user);

        $form = $this->createForm(PartFormType::class, $part);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($part);
            $em->flush();
            $this->addFlash('success', 'Part added.');
            return $this->redirectToRoute('parts_index');
        }

        return $this->render('parts/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'parts_edit')]
    public function edit(Part $part, Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($part->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(PartFormType::class, $part);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Part updated.');
            return $this->redirectToRoute('parts_index');
        }

        return $this->render('parts/edit.html.twig', [
            'form' => $form,
            'part' => $part,
        ]);
    }

    #[Route('/{id}/delete', name: 'parts_delete', methods: ['POST'])]
    public function delete(Part $part, Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($part->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete-part-' . $part->getId(), $request->request->get('_token'))) {
            $em->remove($part);
            $em->flush();
            $this->addFlash('success', 'Part removed.');
        }

        return $this->redirectToRoute('parts_index');
    }
}
