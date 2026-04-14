<?php

namespace App\Controller;

use App\Entity\Chapter;
use App\Entity\User;
use App\Form\ChapterFormType;
use App\Repository\AiInsightRepository;
use App\Repository\ChapterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/timeline')]
class TimelineController extends AbstractController
{
    #[Route('', name: 'timeline_index')]
    public function index(ChapterRepository $chapterRepo, AiInsightRepository $insightRepo): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $chapters = $chapterRepo->findByUserOrderedByDate($user);
        $latestThroughline = $insightRepo->findLatestByUserAndType($user, 'throughline');

        return $this->render('timeline/index.html.twig', [
            'chapters' => $chapters,
            'latest_throughline' => $latestThroughline,
        ]);
    }

    #[Route('/new', name: 'timeline_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $chapter = new Chapter();
        $chapter->setUser($user);
        $chapter->setStartDate(new \DateTime());

        $form = $this->createForm(ChapterFormType::class, $chapter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Collect prompt responses from unmapped fields
            $promptKeys = ['mattered', 'afraid', 'needed', 'self_view', 'running'];
            $responses = [];
            foreach ($promptKeys as $key) {
                $value = $form->get('prompt_' . $key)->getData();
                if ($value) {
                    $responses[$key] = $value;
                }
            }
            if (!empty($responses)) {
                $chapter->setPromptResponses($responses);
            }

            if ($chapter->isOngoing()) {
                $chapter->setEndDate(null);
            }

            $em->persist($chapter);
            $em->flush();

            $this->addFlash('success', 'Chapter added to your timeline.');
            return $this->redirectToRoute('timeline_index');
        }

        return $this->render('timeline/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'timeline_edit')]
    public function edit(Chapter $chapter, Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($chapter->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        $promptKeys = ['mattered', 'afraid', 'needed', 'self_view', 'running'];

        $form = $this->createForm(ChapterFormType::class, $chapter);

        // Pre-fill unmapped prompt fields from saved data
        $saved = $chapter->getPromptResponses() ?? [];
        foreach ($promptKeys as $key) {
            if (isset($saved[$key])) {
                $form->get('prompt_' . $key)->setData($saved[$key]);
            }
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $responses = [];
            foreach ($promptKeys as $key) {
                $value = $form->get('prompt_' . $key)->getData();
                if ($value) {
                    $responses[$key] = $value;
                }
            }
            $chapter->setPromptResponses(!empty($responses) ? $responses : null);

            if ($chapter->isOngoing()) {
                $chapter->setEndDate(null);
            }

            $em->flush();

            $this->addFlash('success', 'Chapter updated.');
            return $this->redirectToRoute('timeline_index');
        }

        return $this->render('timeline/edit.html.twig', [
            'form' => $form,
            'chapter' => $chapter,
        ]);
    }

    #[Route('/{id}/delete', name: 'timeline_delete', methods: ['POST'])]
    public function delete(Chapter $chapter, Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($chapter->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete-chapter-' . $chapter->getId(), $request->request->get('_token'))) {
            $em->remove($chapter);
            $em->flush();
            $this->addFlash('success', 'Chapter removed.');
        }

        return $this->redirectToRoute('timeline_index');
    }
}
