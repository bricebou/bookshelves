<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Publisher;
use App\Form\BookType;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use App\Repository\BookshelfRepository;
use App\Repository\PublisherRepository;
use Google\Service\Books;
use Google_Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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

    #[Route('/isbn', name: 'isbn', methods: ['GET'])]
    public function isbn(): Response
    {
        $form = $this->createFormBuilder()
            ->add('isbn', TextType::class)
            ->getForm();

        return $this->renderForm('book/isbn.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/isbn', name: 'isbn_lookup', methods: ['POST'])]
    public function isbnLookup(
        Request $request,
        PublisherRepository $publisherRepository,
        AuthorRepository $authorRepository,
        BookRepository $bookRepository
    ): Response {
        $isbn = $request->query->get('isbn');

        $client = new Google_Client();
        $client->setApplicationName("Bookshelves Symfony Learning");
        $service = new Books($client);

        $result = $service->volumes->listVolumes("isbn: $isbn")->offsetGet(0);

        $book = new Book();

        foreach ($result->volumeInfo->authors as $name) {
            if (!$authorRepository->findOneBy(['name' => $name])) {
                $author = new Author();
                $author->setName($name);

                $authorRepository->save($author, true);
            }

            $book->addAuthor($authorRepository->findOneBy(['name' => $name]));
        }

        $publisher = $publisherRepository->findOneBy(['name' => $result->volumeInfo->publisher]) ?? null;

        if (!$publisher) {
            $publisher = new Publisher();
            $publisher->setName($result->volumeInfo->publisher);

            $publisherRepository->save($publisher, true);
        }
        $book->setPublisher($publisher);

        $book->setDescription($result->volumeInfo->description)
            ->setIsbn($isbn)
            ->setPages($result->volumeInfo->pageCount)
            ->setPublicationDate(substr($result->volumeInfo->publishedDate, 0, 4))
            ->setSubtitle($result->volumeInfo->subtitle)
            ->setTitle($result->volumeInfo->title);

        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bookRepository->save($book, true);

            return $this->redirectToRoute('bks_book_view', [
                'ulid' => $book->getUlid()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('book/_edit.html.twig', [
            'book' => $book,
            'result' => $result,
            'form' => $form,
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
        $form->remove('bookshelf');
        $form->handleRequest($request);

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
