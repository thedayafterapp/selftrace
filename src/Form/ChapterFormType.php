<?php

namespace App\Form;

use App\Entity\Chapter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class ChapterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 255]),
                ],
                'attr' => ['placeholder' => 'e.g. "The Year I Disappeared", "When Everything Burned"'],
            ])
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [new NotBlank()],
            ])
            ->add('isOngoing', CheckboxType::class, [
                'required' => false,
                'label' => 'This chapter is still happening',
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('colorHex', TextType::class, [
                'attr' => ['type' => 'color', 'class' => 'color-picker'],
                'label' => 'Choose a color for this chapter',
            ])
            ->add('partName', TextType::class, [
                'required' => false,
                'label' => 'If this version of you was a character, what would you call them?',
                'attr' => ['placeholder' => 'e.g. "The Seeker", "The Ghost", "The Fighter"'],
            ]);

        // Prompt response fields
        $prompts = [
            'mattered' => 'What mattered to you then?',
            'afraid' => 'What were you afraid of?',
            'needed' => 'What did you need?',
            'self_view' => 'How did you see yourself?',
            'running' => 'What were you running toward — or away from?',
        ];

        foreach ($prompts as $key => $label) {
            $builder->add('prompt_' . $key, TextareaType::class, [
                'mapped' => false,
                'required' => false,
                'label' => $label,
                'attr' => ['rows' => 3, 'placeholder' => 'Take your time...'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Chapter::class,
        ]);
    }
}
