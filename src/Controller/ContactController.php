<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Message\SendContactEmailMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact.create', methods: ['POST', 'GET'])]
    public function create(Request $request, MessageBusInterface $bus): Response
    {
        $contactForm = $this->createForm(ContactType::class);
        $contactForm->handleRequest($request);

        if ($contactForm->isSubmitted() && $contactForm->isValid()) {
            $data = $contactForm->getData();
            try {
                $bus->dispatch(new SendContactEmailMessage(
                    $data['firstname'],
                    $data['lastname'],
                    $data['email'],
                    $data['message'],
                ));
            } catch (ExceptionInterface $e) {
                dd($e);
            }
        }

        return $this->render('contact/create.html.twig', compact('contactForm'));
    }
}
