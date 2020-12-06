<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class OtpType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('otp', TextType::class, [
            'label' => false,
            'attr' => [
                'placeholder' => 'XXX-XXX',
                'style' => 'text-align: center',
                'maxlength' => 7,
                'data-mask' => "000-000",
            ],
            "constraints" => [
                new NotBlank(),
            ],
        ]);


        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            $otp = $form->get('otp')->getData();

            if ($otp) {

                $otpSent = $options["otpSent"];

                if ($otp !== $otpSent) {
                    // Form is invalid when otp sent is different of input otp
                    $form->get('otp')->addError(new FormError("Invalid OTP"));
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'otpSent' => null,
        ]);

        $resolver->setRequired('otpSent');
    }
}
