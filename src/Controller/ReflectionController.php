<?php

namespace App\Controller;

use App\Entity\Reflection;
use App\Entity\User;
use App\Repository\ReflectionRepository;
use App\Service\PromptRotationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/reflections')]
class ReflectionController extends AbstractController
{
    #[Route('', name: 'reflections_index')]
    public function index(
        PromptRotationService $promptService,
        ReflectionRepository $reflectionRepo,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $prompt = $promptService->getTodaysPrompt($user);
        $todayReflection = $reflectionRepo->findTodayByUser($user);

        return $this->render('reflections/index.html.twig', [
            'prompt' => $prompt,
            'today_reflection' => $todayReflection,
        ]);
    }

    #[Route('/save', name: 'reflections_save', methods: ['POST'])]
    public function save(
        Request $request,
        PromptRotationService $promptService,
        ReflectionRepository $reflectionRepo,
        EntityManagerInterface $em,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$this->isCsrfTokenValid('save-reflection', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $responseText = trim($request->request->get('response_text', ''));
        if (empty($responseText)) {
            $this->addFlash('error', 'Please write something before saving.');
            return $this->redirectToRoute('reflections_index');
        }

        // Update existing today's reflection or create new one
        $reflection = $reflectionRepo->findTodayByUser($user);
        if (!$reflection) {
            $prompt = $promptService->getTodaysPrompt($user);
            $reflection = new Reflection();
            $reflection->setUser($user);
            $reflection->setPromptText($prompt ? $prompt->getPromptText() : '');
            $reflection->setDate(new \DateTime());
            $em->persist($reflection);
        }

        $reflection->setResponseText($responseText);
        $em->flush();

        $this->addFlash('success', 'Reflection saved.');
        return $this->redirectToRoute('reflections_history');
    }

    #[Route('/history', name: 'reflections_history')]
    public function history(ReflectionRepository $reflectionRepo): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $reflections = $reflectionRepo->findByUserOrderedByDate($user);

        return $this->render('reflections/history.html.twig', [
            'reflections' => $reflections,
        ]);
    }
}
