<?php

namespace App\Form;

use App\Entity\Bookshelf;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class BookshelfType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => $this->translator->trans('bookshelf.edit.name')
            ])
            ->add('description', null, [
                'label' => $this->translator->trans('bookshelf.edit.description'),
            ])
            ->add('public', null, [
                'label' => $this->translator->trans('bookshelf.edit.public'),
                'help' => $this->translator->trans('bookshelf.edit.public.help')

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Bookshelf::class,
        ]);
    }
}
