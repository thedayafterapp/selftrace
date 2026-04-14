<?php

namespace App\Form;

use App\Entity\Part;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class PartFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'What do you call this part?',
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 255]),
                ],
                'attr' => ['placeholder' => 'e.g. "the people pleaser", "the lone wolf", "the dreamer"'],
            ])
            ->add('triggerText', TextareaType::class, [
                'required' => false,
                'label' => 'What tends to bring this part out?',
                'attr' => ['rows' => 3, 'placeholder' => 'Situations, feelings, people...'],
            ])
            ->add('needsText', TextareaType::class, [
                'required' => false,
                'label' => 'What does this part need?',
                'attr' => ['rows' => 3, 'placeholder' => 'What does this part of you crave or require?'],
            ])
            ->add('fearsText', TextareaType::class, [
                'required' => false,
                'label' => 'What is this part afraid of?',
                'attr' => ['rows' => 3, 'placeholder' => 'What is this part trying to avoid?'],
            ])
            ->add('protectsText', TextareaType::class, [
                'required' => false,
                'label' => 'What is this part trying to protect you from?',
                'attr' => ['rows' => 3, 'placeholder' => 'What does this part believe it\'s keeping you safe from?'],
            ])
            ->add('colorHex', TextType::class, [
                'label' => 'Choose a color for this part',
                'attr' => ['type' => 'color', 'class' => 'color-picker'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Part::class,
        ]);
    }
}
