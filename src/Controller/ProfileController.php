<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/profile', name: 'bks_profile_')]
class ProfileController extends AbstractController
{
    #[Route('/{username}', name: 'view')]
    public function view(User $user): Response
    {
        $this->denyAccessUnlessGranted('view', $user);

        return $this->render('profile/view.html.twig', [
            'user' => $user,
        ]);
    }
}
