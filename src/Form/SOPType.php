<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\SOP;
use App\Entity\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SOPType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'attr' => [
                    'placeholder' => 'Enter SOP title',
                    'class' => 'w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all outline-none',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter a title']),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Describe the purpose and scope of this SOP...',
                    'rows' => 6,
                    'class' => 'w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all outline-none resize-none',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter a description']),
                ],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Category',
                'placeholder' => 'Select a category',
                'required' => true,
                'attr' => [
                    'class' => 'w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all outline-none',
                ],
            ])
            ->add('department', TextType::class, [
                'label' => 'Department',
                'required' => false,
                'attr' => [
                    'placeholder' => 'e.g., IT, HR, Operations',
                    'class' => 'w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all outline-none',
                ],
            ])
            ->add('difficulty', ChoiceType::class, [
                'label' => 'Difficulty Level',
                'choices' => [
                    '1 - Easy' => 1,
                    '2' => 2,
                    '3 - Medium' => 3,
                    '4' => 4,
                    '5 - Expert' => 5,
                ],
                'expanded' => true,
                'attr' => [
                    'class' => 'flex items-center gap-4',
                ],
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'name',
                'label' => 'Tags',
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'class' => 'w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all outline-none',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SOP::class,
        ]);
    }
}
