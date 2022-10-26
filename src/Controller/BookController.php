<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Publisher;
use App\Form\BookType;
use App\Form\IsbnType;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use App\Repository\BookshelfRepository;
use App\Repository\PublisherRepository;
use App\Utils\GoogleBooksApiUtils;
use Nicebooks\Isbn\IsbnTools;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/book', name: 'bks_book_')]
class BookController extends AbstractController
{
    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        BookshelfRepository $bookshelfRepository,
        AuthorRepository $authorRepository,
        PublisherRepository $publisherRepository,
        BookRepository $bookRepository
    ): Response {
        $this->denyAccessUnlessGranted('edit', $this->getUser());

        $book = new Book();

        // Retrieving the Bookshelf passed along the request
        // and associating it to the Book
        if ($request->query->get('bksid')) {
            $bookshelf = $bookshelfRepository->findOneBy(['ulid' => $request->query->get('bksid')]);

            $book->setBookshelf($bookshelf);
        }


        // Dealing with the ISBN form
        $isbnForm = $this->createForm(IsbnType::class);
        $isbnForm->handleRequest($request);

        if ($isbnForm->isSubmitted() && $isbnForm->isValid()) {
            $isbnTools = new IsbnTools();
            $isbn = $isbnTools->format($isbnForm->getData()['isbn']);

            $book->setIsbn($isbn);

            // Getting book's details from the Google Books API using the ISBN
            $gbapi = new GoogleBooksApiUtils();
            $details = $gbapi->gettingVolumeInfoByIsbn($isbn);

            $book->setTitle($details->getTitle());
            $book->setSubtitle($details->getSubtitle());
            $book->setDescription($details->getDescription());
            $book->setPages($details->getPageCount());
            $book->setPublicationDate(substr($details->getPublishedDate(), 0, 4));

            if ($details->getPublisher()) {
                $publisher = $publisherRepository->findOneBy(['name' => $details->getPublisher()]);

                if (!$publisher) {
                    $publisher = new Publisher();
                    $publisher->setName($details->getPublisher());

                    $publisherRepository->save($publisher, true);
                }

                $book->setPublisher($publisher);
            }

            foreach ($details->getAuthors() ?? [] as $dga) {
                $author = $authorRepository->findOneBy(['name' => $dga]);

                if (!$author) {
                    $author = new Author();
                    $author->setName($dga);

                    $authorRepository->save($author, true);
                }

                $book->addAuthor($author);
            }
        }

        // Dealing with the main form, dealing with the Book entity
        $bookForm = $this->createForm(BookType::class, $book);
        $bookForm->handleRequest($request);

        if ($bookForm->isSubmitted() && $bookForm->isValid()) {
            $bookRepository->save($book, true);

            return $this->redirectToRoute('bks_book_view', [
                'ulid' => $book->getUlid(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('book/create.html.twig', [
            'isbn_form' => $isbnForm,
            'form' => $bookForm,
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
