<?php

namespace App\Controller;

use App\Entity\Author;
use App\Form\AuthorType;
use App\Repository\AuthorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/author', name: 'bks_author_')]
class AuthorController extends AbstractController
{
    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request, AuthorRepository $authorRepository): Response
    {
        $author = new Author();

        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        $template = $request->isXmlHttpRequest() ? 'forms/edit.html.twig' : 'author/create.html.twig';

        if ($form->isSubmitted() && $form->isValid()) {
            $authorRepository->save($author, true);

            if ($request->isXmlHttpRequest()) {
                return new Response(null, Response::HTTP_NO_CONTENT);
            }
        }

        return $this->render($template, [
            'form' => $form->createView()
        ], new Response(
            null,
            $form->isSubmitted() && !$form->isValid() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK
        ));
    }
}
