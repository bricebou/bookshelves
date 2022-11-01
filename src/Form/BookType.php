<?php

namespace App\Form;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Bookshelf;
use App\Entity\Publisher;
use App\Repository\BookshelfRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class BookType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('bookshelf', EntityType::class, [
                'class' => Bookshelf::class,
                'choice_label' => 'name',
                'query_builder' => function (BookshelfRepository $bookshelfRepository) {
                    return $bookshelfRepository->createQueryBuilder('bookshelf')
                        ->where('bookshelf.owner = :owner')
                        ->setParameter('owner', $this->security->getUser())
                        ->orderBy('bookshelf.name', 'ASC');
                },
            ])
            ->add('title')
            ->add('subtitle')
            ->add('authors', AuthorAutocompleteField::class)
            ->add('publisher', PublisherAutocompleteField::class)
            ->add('publication_date')
            ->add('description')
            ->add('isbn')
            ->add('pages')
            ->add('imageFile', VichImageType::class, [
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }
}
