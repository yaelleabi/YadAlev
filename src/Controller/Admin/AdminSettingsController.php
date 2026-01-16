<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\SettingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\PasswordStrength;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/settings')]
final class AdminSettingsController extends AbstractController
{
    #[Route(name: 'app_admin_settings_index')]
    public function index(): Response
    {
        return $this->render('admin/settings/index.html.twig');
    }

    #[Route('/color', name: 'app_admin_settings_color', methods: ['GET', 'POST'])]
    public function color(Request $request, SettingRepository $settingRepository): Response
    {
        if ($request->isMethod('POST')) {
            // Check for reset action
            if ($request->request->has('reset')) {
                $settingRepository->setValue('site_title_color', '#0d6efd'); // Default Bootstrap Primary Color
                $this->addFlash('success', 'Couleur réinitialisée par défaut.');
                return $this->redirectToRoute('app_admin_settings_index');
            }

            $color = $request->request->get('title_color');
            if ($color) {
                $settingRepository->setValue('site_title_color', $color);
                $this->addFlash('success', 'Couleur mise à jour avec succès.');
                return $this->redirectToRoute('app_admin_settings_index');
            }
        }

        return $this->render('admin/settings/color.html.twig', [
            'current_color' => $settingRepository->getValue('site_title_color', '#0d6efd'),
        ]);
    }

    #[Route('/password', name: 'app_admin_settings_password', methods: ['GET', 'POST'])]
    public function password(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $errors = [];
        if ($request->isMethod('POST')) {
            $user = $this->getUser();
            $newPassword = $request->request->get('password');
            
            // Validation manuelle
            $constraints = [
                new NotBlank(['message' => 'Veuillez entrer un mot de passe.']),
                new Length([
                    'min' => 8,
                    'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères.',
                    'max' => 4096,
                ]),
                new PasswordStrength(),
                new NotCompromisedPassword(),
            ];

            $violationList = $validator->validate($newPassword, $constraints);

            if (count($violationList) > 0) {
                foreach ($violationList as $violation) {
                    $this->addFlash('error', $violation->getMessage());
                    $errors[] = $violation->getMessage();
                }
            } else {
                $user->setPassword($hasher->hashPassword($user, $newPassword));
                $entityManager->flush();
                $this->addFlash('success', 'Mot de passe modifié avec succès.');
                return $this->redirectToRoute('app_admin_settings_index');
            }
        }

        return $this->render('admin/settings/password.html.twig', [
            'errors' => $errors
        ]);
    }

    #[Route('/new-admin', name: 'app_admin_settings_new_admin', methods: ['GET', 'POST'])]
    public function newAdmin(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createFormBuilder()
            ->add('firstName', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
                'label' => 'Prénom',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Prénom']
            ])
            ->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nom']
            ])
            ->add('email', \Symfony\Component\Form\Extension\Core\Type\EmailType::class, [
                'label' => 'Adresse email',
                'attr' => ['class' => 'form-control', 'placeholder' => 'email@admin.com']
            ])
            ->add('plainPassword', \Symfony\Component\Form\Extension\Core\Type\PasswordType::class, [
                'label' => 'Mot de passe',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Mot de passe'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un mot de passe.']),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères.',
                        'max' => 4096,
                    ]),
                    new PasswordStrength(),
                    new NotCompromisedPassword(),
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            $user = new User();
            $user->setEmail($data['email']);
            $user->setName($data['name']);
            $user->setFirstName($data['firstName']);
            $user->setPhoneNumber('0000000000'); // Dummy valid phone
            
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $data['plainPassword']
                )
            );
            $user->setRoles(['ROLE_ADMIN']); // Force ADMIN role
            $user->setIsVerified(true); // Auto-verify admin

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Nouvel administrateur créé avec succès.');

            return $this->redirectToRoute('app_admin_settings_index');
        }

        return $this->render('admin/settings/register_admin.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/profile', name: 'app_admin_settings_profile', methods: ['GET', 'POST'])]
    public function profile(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createFormBuilder($user)
            ->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'form-control']
            ])
            ->add('firstName', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
                'label' => 'Prénom',
                'attr' => ['class' => 'form-control']
            ])
            ->add('email', \Symfony\Component\Form\Extension\Core\Type\EmailType::class, [
                'label' => 'Email',
                'attr' => ['class' => 'form-control']
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès.');
            return $this->redirectToRoute('app_admin_settings_index');
        }

        return $this->render('admin/settings/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }


}
