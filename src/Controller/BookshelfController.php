<?php

namespace App\Controller;

use App\Entity\Bookshelf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/bookshelf', name: 'bks_bookshelf_')]
class BookshelfController extends AbstractController
{
    #[Route('/{ulid}', name: 'view')]
    public function view(Bookshelf $bookshelf): Response
    {
        return $this->render('bookshelf/view.html.twig', [
            'bookshelf' => $bookshelf,
        ]);
    }
}
