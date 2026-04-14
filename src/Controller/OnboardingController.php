<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/onboarding')]
class OnboardingController extends AbstractController
{
    #[Route('/step-1', name: 'onboarding_step1')]
    public function step1(): Response
    {
        return $this->render('onboarding/step1.html.twig');
    }

    #[Route('/step-2', name: 'onboarding_step2')]
    public function step2(): Response
    {
        return $this->render('onboarding/step2.html.twig');
    }

    #[Route('/step-3', name: 'onboarding_step3')]
    public function step3(): Response
    {
        return $this->render('onboarding/step3.html.twig');
    }

    #[Route('/complete', name: 'onboarding_complete', methods: ['POST'])]
    public function complete(EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $user->setOnboardingComplete(true);
        $em->flush();

        return $this->redirectToRoute('dashboard');
    }

    #[Route('/restart', name: 'onboarding_restart')]
    public function restart(EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $user->setOnboardingComplete(false);
        $em->flush();

        return $this->redirectToRoute('onboarding_step1');
    }
}
