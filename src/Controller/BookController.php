<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use App\Repository\BookshelfRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/book', name: 'bks_book_')]
class BookController extends AbstractController
{
    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        BookRepository $bookRepository,
        BookshelfRepository $bookshelfRepository
    ): Response {
        $this->denyAccessUnlessGranted('edit', $this->getUser());

        $bookshelf = $bookshelfRepository->findOneBy(['ulid' => $request->query->get('bksid')]) ?? null;

        $book = new Book();
        $book->setBookshelf($bookshelf);

        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bookRepository->save($book, true);

            return $this->redirectToRoute('bks_book_view', [
                'ulid' => $book->getUlid(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('book/create.html.twig', [
            'form' => $form,
            'book' => $book
        ]);
    }

    #[Route('/{ulid}', name: 'view', methods: ['GET'])]
    public function view(Book $book): Response
    {
        $this->denyAccessUnlessGranted('view', $book->getBookshelf());

        return $this->render('book/view.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/{ulid}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Book $book, Request $request, BookRepository $bookRepository): Response
    {
        $this->denyAccessUnlessGranted('edit', $book->getBookshelf());

        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);
        $form->remove('bookshelf');

        if ($form->isSubmitted() && $form->isValid()) {
            $bookRepository->save($book, true);

            return $this->redirectToRoute('bks_book_view', [
                'ulid' => $book->getUlid(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('book/edit.html.twig', [
            'form' => $form,
            'book' => $book,
        ]);
    }

    #[Route('/{ulid}', name: 'delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Book $book,
        BookRepository $bookRepository
    ): Response {
        if ($this->isCsrfTokenValid('deletebook' . $book->getUlid(), $request->request->get('_token'))) {
            $bookRepository->remove($book, true);
        }

        return $this->redirectToRoute('bks_bookshelf_view', [
            'ulid' => $book->getBookshelf()->getUlid()
        ], Response::HTTP_SEE_OTHER);
    }
}
