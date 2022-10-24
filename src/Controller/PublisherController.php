<?php

namespace App\Controller;

use App\Entity\Publisher;
use App\Form\PublisherType;
use App\Repository\PublisherRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/publisher', name: 'bks_publisher_')]
class PublisherController extends AbstractController
{
    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request, PublisherRepository $publisherRepository): Response
    {
        $publisher = new Publisher();

        $form = $this->createForm(PublisherType::class, $publisher);
        $form->handleRequest($request);

        $template = $request->isXmlHttpRequest() ? 'forms/edit.html.twig' : 'publisher/create.html.twig';

        if ($form->isSubmitted() && $form->isValid()) {
            $publisherRepository->save($publisher, true);

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
