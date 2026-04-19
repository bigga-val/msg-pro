<?php

namespace App\Form;

use App\Entity\Contact;
use App\Entity\Groupe;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];

        $builder
            ->add('telephone')
            ->add('nom')
            ->add('postnom')
            ->add('adresse')
            ->add('fonction')
            ->add('groupe', EntityType::class, [
                'class'        => Groupe::class,
                'choice_label' => 'designation',
                'mapped'       => false,
                'required'     => false,
                'placeholder'  => '— Aucun groupe —',
                'query_builder' => function (EntityRepository $er) use ($user) {
                    $qb = $er->createQueryBuilder('g');
                    if ($user !== null) {
                        $qb->where('g.user = :user')->setParameter('user', $user);
                    }
                    return $qb->orderBy('g.designation', 'ASC');
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
            'user'       => null,
        ]);
    }
}
