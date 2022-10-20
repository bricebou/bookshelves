<?php

namespace App\Controller;

use App\Entity\Bookshelf;
use App\Form\BookshelfOwnerType;
use App\Form\BookshelfType;
use App\Repository\BookshelfRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/bookshelf', name: 'bks_bookshelf_')]
class BookshelfController extends AbstractController
{
    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request, BookshelfRepository $bookshelfRepository): Response
    {
        $this->denyAccessUnlessGranted('edit', $this->getUser());

        $bookshelf = new Bookshelf();
        $bookshelf->setOwner($this->getUser());

        $form = $this->createForm(BookshelfType::class, $bookshelf);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bookshelfRepository->save($bookshelf, true);

            return $this->redirectToRoute('bks_bookshelf_view', [
                'ulid' => $bookshelf->getUlid()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('bookshelf/create.html.twig', [
            'bookshelf' => $bookshelf,
            'form' => $form,
        ]);
    }

    #[Route('/{ulid}', name: 'view', methods: ['GET'])]
    public function view(Bookshelf $bookshelf): Response
    {
        $this->denyAccessUnlessGranted('view', $bookshelf);

        return $this->render('bookshelf/view.html.twig', [
            'bookshelf' => $bookshelf,
        ]);
    }

    #[Route('{ulid}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        Bookshelf $bookshelf,
        Request $request,
        BookshelfRepository $bookshelfRepository
    ): Response {
        $this->denyAccessUnlessGranted('edit', $bookshelf);

        $form = $this->createForm(BookshelfType::class, $bookshelf);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bookshelfRepository->save($bookshelf, true);

            return $this->redirectToRoute('bks_bookshelf_view', [
                'ulid' => $bookshelf->getUlid()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('bookshelf/edit.html.twig', [
            'bookshelf' => $bookshelf,
            'form' => $form,
        ]);
    }

    #[Route('/{ulid}/ownership', name: 'edit_ownership', methods: ['GET', 'POST'])]
    public function editOwnership(
        Bookshelf $bookshelf,
        Request $request,
        BookshelfRepository $bookshelfRepository
    ): Response {
        $this->denyAccessUnlessGranted('edit', $bookshelf);

        $form = $this->createForm(BookshelfOwnerType::class, $bookshelf);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bookshelfRepository->save($bookshelf, true);

            return $this->redirectToRoute('bks_bookshelf_view', [
                'ulid' => $bookshelf->getUlid()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('bookshelf/edit.html.twig', [
            'bookshelf' => $bookshelf,
            'form' => $form,
        ]);
    }

    #[Route('/{ulid}', name: 'delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Bookshelf $bookshelf,
        BookshelfRepository $bookshelfRepository
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $bookshelf->getUlid(), $request->request->get('_token'))) {
            $bookshelfRepository->remove($bookshelf, true);
        }

        return $this->redirectToRoute('bks_profile_view', ['username' => $this->getUser()->getUserIdentifier()], Response::HTTP_SEE_OTHER);
    }
}
