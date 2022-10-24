<?php

namespace App\Form;

use App\Entity\Publisher;
use App\Repository\PublisherRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\ParentEntityAutocompleteType;

#[AsEntityAutocompleteField]
class PublisherAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => Publisher::class,
            'placeholder' => 'Choose a Publisher',
            'choice_label' => 'name',

            'query_builder' => function (PublisherRepository $publisherRepository) {
                return $publisherRepository->createQueryBuilder('publisher');
            },
            //'security' => 'ROLE_SOMETHING',
        ]);
    }

    public function getParent(): string
    {
        return ParentEntityAutocompleteType::class;
    }
}
