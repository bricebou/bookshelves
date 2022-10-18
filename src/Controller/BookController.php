<?php

namespace App\Controller;

use App\Entity\Book;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/book', name: 'bks_book_')]
class BookController extends AbstractController
{
    #[Route('/{ulid}', name: 'view')]
    public function view(Book $book): Response
    {
        $this->denyAccessUnlessGranted('view', $book->getBookshelf());

        return $this->render('book/view.html.twig', [
            'book' => $book,
        ]);
    }
}
