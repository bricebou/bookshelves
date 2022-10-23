<?php

namespace App\Form;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\ParentEntityAutocompleteType;

#[AsEntityAutocompleteField]
class AuthorAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => Author::class,
            'placeholder' => 'Choose a Author',
            'choice_label' => 'name',
            'multiple' => true,
            'query_builder' => function(AuthorRepository $authorRepository) {
                return $authorRepository->createQueryBuilder('author');
            },
            //'security' => 'ROLE_SOMETHING',
        ]);
    }

    public function getParent(): string
    {
        return ParentEntityAutocompleteType::class;
    }
}
